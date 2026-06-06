package com.example.crises;

import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.media.AudioAttributes;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Build;
import android.os.VibrationEffect;
import android.os.Vibrator;
import android.util.Log;

import androidx.annotation.NonNull;
import androidx.core.app.NotificationCompat;
import androidx.core.content.ContextCompat;
import androidx.work.Worker;
import androidx.work.WorkerParameters;

import com.google.android.gms.location.CurrentLocationRequest;
import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.location.Priority;
import com.google.android.gms.tasks.Tasks;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;
import java.util.HashSet;
import java.util.List;
import java.util.Set;
import java.util.concurrent.TimeUnit;

public class NotificationWorker extends Worker {

    private static final String TAG              = "NotificationWorker";
    private static final String CHANNEL_ID       = "crises_alerts";
    private static final String CHANNEL_ID_DANGER = "crises_danger";
    private static final String PREF_LAST_ZONE   = "last_notified_zone";

    private static final String API_ALERTS    = "http://192.168.0.106/crises_api/get_alerts.php";
    private static final String API_MAP       = "http://192.168.0.106/crises_api/get_map_data.php";
    private static final String API_NEWS      = "http://192.168.0.106/crises_api/get_news.php";
    private static final String API_HOSPITALS = "http://192.168.0.106/crises_api/get_hospitals.php";
    private static final String API_NEEDS     = "http://192.168.0.106/crises_api/get_needs.php";
    private static final String API_SHELTERS  = "http://192.168.0.106/crises_api/get_shelters.php";

    private NotificationRepository repo;

    public NotificationWorker(@NonNull Context context, @NonNull WorkerParameters params) {
        super(context, params);
        repo = new NotificationRepository(context);
    }

    @NonNull
    @Override
    public Result doWork() {
        Log.d(TAG, "Worker started. Seeded=" + repo.isSeeded()
                + "  loginDate=" + repo.getLoginDate());

        // ── STEP 1: Check location zone (runs even when app is closed) ────
        checkLocationZone();

        // ── STEP 2: First run — seed pre-login items silently ─────────────
        if (!repo.isSeeded()) {
            Log.d(TAG, "First run — seeding pre-login IDs silently");
            seedPreLoginItems();
            repo.markSeeded();
            Log.d(TAG, "Seeding complete.");
            return Result.success();
        }

        // ── STEP 3: Check for new items and notify ─────────────────────────
        List<String> newItems = new ArrayList<>();

        try { newItems.addAll(checkAlerts());    } catch (Exception e) { Log.e(TAG, "Alerts: "    + e.getMessage()); }
        try { newItems.addAll(checkMapAlerts()); } catch (Exception e) { Log.e(TAG, "MapAlerts: " + e.getMessage()); }
        try { newItems.addAll(checkNews());      } catch (Exception e) { Log.e(TAG, "News: "      + e.getMessage()); }
        try { newItems.addAll(checkHospitals()); } catch (Exception e) { Log.e(TAG, "Hospitals: " + e.getMessage()); }
        try { newItems.addAll(checkNeeds());     } catch (Exception e) { Log.e(TAG, "Needs: "     + e.getMessage()); }
        try { newItems.addAll(checkShelters());  } catch (Exception e) { Log.e(TAG, "Shelters: "  + e.getMessage()); }

        Log.d(TAG, "New items found: " + newItems.size());

        if (!newItems.isEmpty()) {
            repo.addUnread(newItems.size());
            sendNotification(newItems);
            vibrateDevice();
        } else {
            Log.d(TAG, "No new items — nothing to notify");
        }

        return Result.success();
    }

    // ── Location zone check (background — works when app is closed) ───────

    private void checkLocationZone() {
        Context ctx = getApplicationContext();

        // Check location permission
        if (ContextCompat.checkSelfPermission(ctx,
                android.Manifest.permission.ACCESS_FINE_LOCATION)
                != android.content.pm.PackageManager.PERMISSION_GRANTED) {
            Log.d(TAG, "Location permission not granted — skipping zone check");
            return;
        }

        try {
            FusedLocationProviderClient fusedClient =
                    LocationServices.getFusedLocationProviderClient(ctx);

            Location location = null;

            // Try last known location first (fast)
            try {
                location = Tasks.await(fusedClient.getLastLocation(), 5, TimeUnit.SECONDS);
                Log.d(TAG, "Last location: " + (location != null ? location.getLatitude() + "," + location.getLongitude() : "null"));
            } catch (Exception e) {
                Log.e(TAG, "getLastLocation failed: " + e.getMessage());
            }

            // If null, request a fresh location (slower but reliable)
            if (location == null) {
                Log.d(TAG, "Last location null — requesting fresh location");
                try {
                    CurrentLocationRequest req = new CurrentLocationRequest.Builder()
                            .setPriority(Priority.PRIORITY_BALANCED_POWER_ACCURACY)
                            .setMaxUpdateAgeMillis(60000)
                            .setDurationMillis(10000)
                            .build();
                    location = Tasks.await(
                            fusedClient.getCurrentLocation(req, null),
                            15, TimeUnit.SECONDS);
                    Log.d(TAG, "Fresh location: " + (location != null ? location.getLatitude() + "," + location.getLongitude() : "null"));
                } catch (Exception e) {
                    Log.e(TAG, "getCurrentLocation failed: " + e.getMessage());
                }
            }

            if (location == null) {
                Log.d(TAG, "Could not get any location — skipping zone check");
                return;
            }

            double userLat = location.getLatitude();
            double userLng = location.getLongitude();

            // Fetch zones from API
            JSONObject mapData = fetchJson(API_MAP);
            JSONArray  zones   = mapData.getJSONArray("zones");
            JSONArray  alerts  = mapData.getJSONArray("alerts");

            // Build combined zone list
            List<JSONObject> allZones = new ArrayList<>();
            for (int i = 0; i < zones.length(); i++)
                allZones.add(zones.getJSONObject(i));
            for (int i = 0; i < alerts.length(); i++) {
                JSONObject alert = alerts.getJSONObject(i);
                JSONObject z     = new JSONObject();
                z.put("center_lat",    alert.getDouble("lat"));
                z.put("center_lng",    alert.getDouble("lng"));
                z.put("radius_meters", 1000);
                z.put("type",          "danger");
                z.put("name",          alert.optString("title", "Alert Zone"));
                allZones.add(z);
            }

            // Find highest threat zone
            String highestThreat = "none";
            String detectedName  = null;

            for (JSONObject zone : allZones) {
                double centerLat    = zone.getDouble("center_lat");
                double centerLng    = zone.getDouble("center_lng");
                double radiusMeters = zone.getDouble("radius_meters");
                String type         = zone.optString("type", "").toLowerCase();

                float[] result = new float[1];
                Location.distanceBetween(userLat, userLng, centerLat, centerLng, result);

                if (result[0] <= radiusMeters) {
                    if (type.equals("danger")) {
                        highestThreat = "danger";
                        detectedName  = zone.optString("name");
                        break;
                    } else if (type.equals("warning") && !highestThreat.equals("danger")) {
                        highestThreat = "warning";
                        detectedName  = zone.optString("name");
                    }
                }
            }

            Log.d(TAG, "Zone result: " + highestThreat + " / " + detectedName);

            // Only notify if zone changed (avoid spamming every 15 min)
            String lastNotifiedZone = ctx
                    .getSharedPreferences("notif_zone_prefs", Context.MODE_PRIVATE)
                    .getString(PREF_LAST_ZONE, "none");

            String newZoneKey = highestThreat + "_" +
                    (detectedName != null ? detectedName : "");

            if (!newZoneKey.equals(lastNotifiedZone)) {
                ctx.getSharedPreferences("notif_zone_prefs", Context.MODE_PRIVATE)
                        .edit().putString(PREF_LAST_ZONE, newZoneKey).apply();

                if (highestThreat.equals("danger")) {
                    sendDangerNotification(
                            "⚠️ You are in a DANGER zone!",
                            detectedName != null ? detectedName : "Move to safety immediately.");
                    vibrateDevice();
                } else if (highestThreat.equals("warning")) {
                    sendDangerNotification(
                            "⚠️ Warning: You are near a risk area",
                            detectedName != null ? detectedName : "Stay alert.");
                    vibrateDevice();
                } else if (!lastNotifiedZone.equals("none") && highestThreat.equals("none")) {
                    // User left a danger/warning zone
                    sendDangerNotification(
                            "✅ You have left the risk area",
                            "You are now in a safe zone.");
                }
            }

        } catch (Exception e) {
            Log.e(TAG, "Zone check failed: " + e.getMessage());
        }
    }

    // ── Send danger/zone notification (high priority, alarm sound) ────────

    private void sendDangerNotification(String title, String body) {
        Context             ctx = getApplicationContext();
        NotificationManager nm  = (NotificationManager)
                ctx.getSystemService(Context.NOTIFICATION_SERVICE);

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            Uri            soundUri  = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_ALARM);
            AudioAttributes audioAttr = new AudioAttributes.Builder()
                    .setUsage(AudioAttributes.USAGE_ALARM).build();
            NotificationChannel channel = new NotificationChannel(
                    CHANNEL_ID_DANGER, "Danger Zone Alerts",
                    NotificationManager.IMPORTANCE_HIGH);
            channel.setSound(soundUri, audioAttr);
            channel.enableVibration(true);
            channel.setVibrationPattern(new long[]{0, 500, 200, 500, 200, 500});
            nm.createNotificationChannel(channel);
        }

        Intent        intent = new Intent(ctx, HomeActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        PendingIntent pi     = PendingIntent.getActivity(
                ctx, 0, intent, PendingIntent.FLAG_IMMUTABLE);

        NotificationCompat.Builder builder =
                new NotificationCompat.Builder(ctx, CHANNEL_ID_DANGER)
                        .setSmallIcon(android.R.drawable.ic_dialog_alert)
                        .setContentTitle(title)
                        .setContentText(body)
                        .setPriority(NotificationCompat.PRIORITY_MAX)
                        .setCategory(NotificationCompat.CATEGORY_ALARM)
                        .setAutoCancel(true)
                        .setContentIntent(pi)
                        .setSound(RingtoneManager.getDefaultUri(RingtoneManager.TYPE_ALARM))
                        .setVibrate(new long[]{0, 500, 200, 500, 200, 500});

        nm.notify(1002, builder.build());
    }

    // ── Seed pre-login items ───────────────────────────────────────────────

    private void seedPreLoginItems() {
        String loginDate = repo.getLoginDate();
        Log.d(TAG, "Seeding items before: " + loginDate);

        try {
            JSONArray alerts = new JSONArray(fetchRaw(API_ALERTS));
            Set<String> ids  = new HashSet<>();
            for (int i = 0; i < alerts.length(); i++) {
                JSONObject a    = alerts.getJSONObject(i);
                String itemDate = extractDate(a.optString("created_at", ""));
                if (isBeforeLoginDate(itemDate, loginDate))
                    ids.add("a_" + a.getString("id"));
            }
            if (!ids.isEmpty()) repo.seedNotified("alerts", ids);
        } catch (Exception e) { Log.e(TAG, "Seed alerts: " + e.getMessage()); }

        try {
            JSONObject root   = fetchJson(API_MAP);
            JSONArray  alerts = root.getJSONArray("alerts");
            Set<String> ids   = new HashSet<>();
            for (int i = 0; i < alerts.length(); i++) {
                JSONObject a    = alerts.getJSONObject(i);
                String itemDate = extractDate(a.optString("created_at", ""));
                if (isBeforeLoginDate(itemDate, loginDate))
                    ids.add("m_" + a.getString("id"));
            }
            if (!ids.isEmpty()) repo.seedNotified("map_alerts", ids);
        } catch (Exception e) { Log.e(TAG, "Seed map: " + e.getMessage()); }

        try {
            JSONArray news  = new JSONArray(fetchRaw(API_NEWS));
            Set<String> ids = new HashSet<>();
            for (int i = 0; i < news.length(); i++) {
                JSONObject n    = news.getJSONObject(i);
                String itemDate = extractDate(n.optString("publish_date", ""));
                if (isBeforeLoginDate(itemDate, loginDate))
                    ids.add(n.getString("id"));
            }
            if (!ids.isEmpty()) repo.seedNotified("news", ids);
        } catch (Exception e) { Log.e(TAG, "Seed news: " + e.getMessage()); }

        try {
            JSONObject root  = fetchJson(API_HOSPITALS);
            JSONArray  data  = root.getJSONArray("data");
            Set<String> ids  = new HashSet<>();
            for (int i = 0; i < data.length(); i++) {
                JSONObject h    = data.getJSONObject(i);
                String itemDate = extractDate(h.optString("updated_at", ""));
                if (isBeforeLoginDate(itemDate, loginDate))
                    ids.add(h.getString("id"));
            }
            if (!ids.isEmpty()) repo.seedNotified("hospitals", ids);
        } catch (Exception e) { Log.e(TAG, "Seed hospitals: " + e.getMessage()); }

        try {
            JSONObject root  = fetchJson(API_NEEDS);
            JSONArray  data  = root.getJSONArray("data");
            Set<String> ids  = new HashSet<>();
            for (int i = 0; i < data.length(); i++) {
                JSONObject n    = data.getJSONObject(i);
                String itemDate = extractDate(n.optString("created_at", ""));
                if (isBeforeLoginDate(itemDate, loginDate))
                    ids.add(n.getString("resource_id"));
            }
            if (!ids.isEmpty()) repo.seedNotified("needs", ids);
        } catch (Exception e) { Log.e(TAG, "Seed needs: " + e.getMessage()); }

        try {
            JSONArray shelters = new JSONArray(fetchRaw(API_SHELTERS));
            Set<String> ids    = new HashSet<>();
            for (int i = 0; i < shelters.length(); i++) {
                JSONObject s    = shelters.getJSONObject(i);
                String itemDate = extractDate(s.optString("created_at", ""));
                if (isBeforeLoginDate(itemDate, loginDate))
                    ids.add(s.getString("shelter_name"));
            }
            if (!ids.isEmpty()) repo.seedNotified("shelters", ids);
        } catch (Exception e) { Log.e(TAG, "Seed shelters: " + e.getMessage()); }
    }

    // ── Checkers ───────────────────────────────────────────────────────────

    private List<String> checkAlerts() throws Exception {
        List<String> found  = new ArrayList<>();
        JSONArray    alerts = new JSONArray(fetchRaw(API_ALERTS));
        Set<String>  newIds = new HashSet<>();
        for (int i = 0; i < alerts.length(); i++) {
            JSONObject a  = alerts.getJSONObject(i);
            String     id = "a_" + a.getString("id");
            if (!repo.isNotified("alerts", id)) {
                found.add("🚨 " + a.optString("message", "New Alert"));
                newIds.add(id);
            }
        }
        if (!newIds.isEmpty()) repo.markNotified("alerts", newIds);
        return found;
    }

    private List<String> checkMapAlerts() throws Exception {
        List<String> found  = new ArrayList<>();
        JSONObject   root   = fetchJson(API_MAP);
        JSONArray    alerts = root.getJSONArray("alerts");
        Set<String>  newIds = new HashSet<>();
        for (int i = 0; i < alerts.length(); i++) {
            JSONObject a  = alerts.getJSONObject(i);
            String     id = "m_" + a.getString("id");
            if (!repo.isNotified("map_alerts", id)) {
                found.add("🗺️ Map Alert: " + a.optString("title", ""));
                newIds.add(id);
            }
        }
        if (!newIds.isEmpty()) repo.markNotified("map_alerts", newIds);
        return found;
    }

    private List<String> checkNews() throws Exception {
        List<String> found  = new ArrayList<>();
        JSONArray    news   = new JSONArray(fetchRaw(API_NEWS));
        Set<String>  newIds = new HashSet<>();
        for (int i = 0; i < news.length(); i++) {
            JSONObject n  = news.getJSONObject(i);
            String     id = n.getString("id");
            if (!repo.isNotified("news", id)) {
                found.add("📰 News: " + n.optString("title", ""));
                newIds.add(id);
            }
        }
        if (!newIds.isEmpty()) repo.markNotified("news", newIds);
        return found;
    }

    private List<String> checkHospitals() throws Exception {
        List<String> found  = new ArrayList<>();
        JSONObject   root   = fetchJson(API_HOSPITALS);
        JSONArray    data   = root.getJSONArray("data");
        Set<String>  newIds = new HashSet<>();
        for (int i = 0; i < data.length(); i++) {
            JSONObject h  = data.getJSONObject(i);
            String     id = h.getString("id");
            if (!repo.isNotified("hospitals", id)) {
                found.add("🏥 Hospital: " + h.optString("name", ""));
                newIds.add(id);
            }
        }
        if (!newIds.isEmpty()) repo.markNotified("hospitals", newIds);
        return found;
    }

    private List<String> checkNeeds() throws Exception {
        List<String> found  = new ArrayList<>();
        JSONObject   root   = fetchJson(API_NEEDS);
        JSONArray    data   = root.getJSONArray("data");
        Set<String>  newIds = new HashSet<>();
        for (int i = 0; i < data.length(); i++) {
            JSONObject n  = data.getJSONObject(i);
            String     id = n.getString("resource_id");
            if (!repo.isNotified("needs", id)) {
                found.add("📦 Resource: " + n.optString("resource_name", ""));
                newIds.add(id);
            }
        }
        if (!newIds.isEmpty()) repo.markNotified("needs", newIds);
        return found;
    }

    private List<String> checkShelters() throws Exception {
        List<String> found    = new ArrayList<>();
        JSONArray    shelters = new JSONArray(fetchRaw(API_SHELTERS));
        Set<String>  newIds   = new HashSet<>();
        for (int i = 0; i < shelters.length(); i++) {
            JSONObject s  = shelters.getJSONObject(i);
            String     id = s.getString("shelter_name");
            if (!repo.isNotified("shelters", id)) {
                found.add("🏠 Shelter: " + id);
                newIds.add(id);
            }
        }
        if (!newIds.isEmpty()) repo.markNotified("shelters", newIds);
        return found;
    }

    // ── Send general notification (new alerts/news/etc.) ──────────────────

    private void sendNotification(List<String> items) {
        Context             ctx = getApplicationContext();
        NotificationManager nm  = (NotificationManager)
                ctx.getSystemService(Context.NOTIFICATION_SERVICE);

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            Uri            soundUri  = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
            AudioAttributes audioAttr = new AudioAttributes.Builder()
                    .setUsage(AudioAttributes.USAGE_NOTIFICATION).build();
            NotificationChannel channel = new NotificationChannel(
                    CHANNEL_ID, "Crisis Alerts", NotificationManager.IMPORTANCE_HIGH);
            channel.setSound(soundUri, audioAttr);
            channel.enableVibration(true);
            channel.setVibrationPattern(new long[]{0, 400, 200, 400});
            nm.createNotificationChannel(channel);
        }

        String title = items.size() == 1
                ? items.get(0)
                : items.size() + " new crisis updates";

        StringBuilder body = new StringBuilder();
        for (int i = 0; i < Math.min(items.size(), 4); i++)
            body.append(items.get(i)).append("\n");
        if (items.size() > 4)
            body.append("+ ").append(items.size() - 4).append(" more...");

        Intent        intent = new Intent(ctx, Notifications.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        PendingIntent pi     = PendingIntent.getActivity(
                ctx, 0, intent, PendingIntent.FLAG_IMMUTABLE);

        NotificationCompat.Builder builder =
                new NotificationCompat.Builder(ctx, CHANNEL_ID)
                        .setSmallIcon(android.R.drawable.ic_dialog_info)
                        .setContentTitle(title)
                        .setStyle(new NotificationCompat.BigTextStyle().bigText(body.toString()))
                        .setPriority(NotificationCompat.PRIORITY_HIGH)
                        .setAutoCancel(true)
                        .setContentIntent(pi)
                        .setSound(RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION))
                        .setVibrate(new long[]{0, 400, 200, 400});

        nm.notify(1001, builder.build());
    }

    // ── Vibrate ────────────────────────────────────────────────────────────

    private void vibrateDevice() {
        Vibrator v = (Vibrator) getApplicationContext()
                .getSystemService(Context.VIBRATOR_SERVICE);
        if (v == null || !v.hasVibrator()) return;
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O)
            v.vibrate(VibrationEffect.createWaveform(
                    new long[]{0, 400, 200, 400}, -1));
        else
            v.vibrate(new long[]{0, 400, 200, 400}, -1);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private boolean isBeforeLoginDate(String itemDate, String loginDate) {
        if (itemDate == null || itemDate.isEmpty()) return false;
        if (loginDate == null || loginDate.isEmpty()) return false;
        return itemDate.compareTo(loginDate) < 0;
    }

    private String extractDate(String datetime) {
        if (datetime == null || datetime.isEmpty()) return "";
        if (datetime.contains(" ")) return datetime.split(" ")[0];
        if (datetime.contains("T")) return datetime.split("T")[0];
        return datetime;
    }

    private String fetchRaw(String apiUrl) throws Exception {
        URL               url  = new URL(apiUrl);
        HttpURLConnection conn = (HttpURLConnection) url.openConnection();
        conn.setConnectTimeout(15000);
        conn.setReadTimeout(15000);
        conn.setRequestMethod("GET");
        conn.setRequestProperty("Connection", "close");
        BufferedReader br = new BufferedReader(
                new InputStreamReader(conn.getInputStream()));
        StringBuilder sb = new StringBuilder();
        String line;
        while ((line = br.readLine()) != null) sb.append(line);
        br.close();
        conn.disconnect();
        return sb.toString();
    }

    private JSONObject fetchJson(String apiUrl) throws Exception {
        return new JSONObject(fetchRaw(apiUrl));
    }
}