package com.example.crises;

import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Date;
import java.util.HashSet;
import java.util.List;
import java.util.Locale;
import java.util.Set;

public class Notifications extends AppCompatActivity {

    private LinearLayout container;
    private TextView     tvEmpty;
    private ProgressBar  progressBar;
    private NotificationRepository repo;

    private String loginDate;

    private static final String API_ALERTS    = "https://crises360-mobile-api.onrender.com/get_alerts.php";
    private static final String API_MAP       = "https://crises360-mobile-api.onrender.com/get_map_data.php";
    private static final String API_NEWS      = "https://crises360-mobile-api.onrender.com/get_news.php";
    private static final String API_HOSPITALS = "https://crises360-mobile-api.onrender.com/get_hospitals.php";
    private static final String API_NEEDS     = "https://crises360-mobile-api.onrender.com/get_needs.php";
    private static final String API_SHELTERS  = "https://crises360-mobile-api.onrender.com/get_shelters.php";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_notifications);

        repo      = new NotificationRepository(this);
        loginDate = repo.getLoginDate();

        container   = findViewById(R.id.notifContainer);
        tvEmpty     = findViewById(R.id.tvNoNotifications);
        progressBar = findViewById(R.id.progressBar);

        ImageButton btnBack = findViewById(R.id.btnBack);
        if (btnBack != null)
            btnBack.setOnClickListener(v -> finish());

        loadAll();
    }

    private void loadAll() {
        progressBar.setVisibility(View.VISIBLE);
        tvEmpty.setVisibility(View.GONE);
        container.removeAllViews();

        new Thread(() -> {
            List<NotifItem> items = new ArrayList<>();

            try { items.addAll(fetchAlerts());    } catch (Exception e) { Log.e("Notif", "fetchAlerts: "    + e.getMessage()); }
            try { items.addAll(fetchMapAlerts()); } catch (Exception e) { Log.e("Notif", "fetchMapAlerts: " + e.getMessage()); }
            try { items.addAll(fetchNews());      } catch (Exception e) { Log.e("Notif", "fetchNews: "      + e.getMessage()); }
            try { items.addAll(fetchHospitals()); } catch (Exception e) { Log.e("Notif", "fetchHospitals: " + e.getMessage()); }
            try { items.addAll(fetchNeeds());     } catch (Exception e) { Log.e("Notif", "fetchNeeds: "     + e.getMessage()); }
            try { items.addAll(fetchShelters());  } catch (Exception e) { Log.e("Notif", "fetchShelters: "  + e.getMessage()); }

            // Sort: newest date first, unread items first within same date
            Collections.sort(items, (a, b) -> {
                int dateCmp = b.date.compareTo(a.date);
                if (dateCmp != 0) return dateCmp;
                return Boolean.compare(b.isNew, a.isNew);
            });

            List<Object> grouped = groupByDate(items);

            runOnUiThread(() -> {
                progressBar.setVisibility(View.GONE);

                if (items.isEmpty()) {
                    tvEmpty.setVisibility(View.VISIBLE);
                } else {
                    tvEmpty.setVisibility(View.GONE);
                    for (Object obj : grouped) {
                        if (obj instanceof String) addDateHeader((String) obj);
                        else addCard((NotifItem) obj);
                    }
                }

                // Clear badge AFTER content is shown to the user
                repo.clearUnread();
            });
        }).start();
    }

    // ── Fetchers ───────────────────────────────────────────────────────────

    private List<NotifItem> fetchAlerts() throws Exception {
        List<NotifItem> list   = new ArrayList<>();
        JSONArray       alerts = new JSONArray(fetchRaw(API_ALERTS));
        Set<String>     newIds = new HashSet<>();

        for (int i = 0; i < alerts.length(); i++) {
            JSONObject a        = alerts.getJSONObject(i);
            String     itemDate = extractDate(a.optString("created_at", getTodayDate()));

            // Skip items created before the user's login date
            if (isBeforeLoginDate(itemDate)) continue;

            String  id       = "a_" + a.getString("id");
            String  severity = a.optString("severity", "");
            String  emoji    = severity.equalsIgnoreCase("Critical") ? "🔴"
                    : severity.equalsIgnoreCase("Warning") ? "🟡" : "🔵";
            boolean isNew    = repo.isNew("alerts", id);
            if (isNew) newIds.add(id);

            list.add(new NotifItem(
                    "ALERT",
                    emoji + " " + a.optString("message", ""),
                    a.optString("region", "") + " · " + a.optString("status", ""),
                    itemDate,
                    isNew));
        }
        if (!newIds.isEmpty()) repo.markSeen("alerts", newIds);
        return list;
    }

    private List<NotifItem> fetchMapAlerts() throws Exception {
        List<NotifItem> list   = new ArrayList<>();
        JSONObject      root   = new JSONObject(fetchRaw(API_MAP));
        JSONArray       alerts = root.getJSONArray("alerts");
        Set<String>     newIds = new HashSet<>();

        for (int i = 0; i < alerts.length(); i++) {
            JSONObject a        = alerts.getJSONObject(i);
            String     itemDate = extractDate(a.optString("created_at", getTodayDate()));

            if (isBeforeLoginDate(itemDate)) continue;

            String  id       = "m_" + a.getString("id");
            String  severity = a.optString("severity", "low");
            String  emoji    = severity.equals("high")   ? "🔴"
                    : severity.equals("medium") ? "🟡" : "🟢";
            boolean isNew    = repo.isNew("map_alerts", id);
            if (isNew) newIds.add(id);

            list.add(new NotifItem(
                    "MAP ALERT",
                    emoji + " " + a.optString("title", ""),
                    a.optString("description", "") + " · " + a.optString("region", ""),
                    itemDate,
                    isNew));
        }
        if (!newIds.isEmpty()) repo.markSeen("map_alerts", newIds);
        return list;
    }

    private List<NotifItem> fetchNews() throws Exception {
        List<NotifItem> list   = new ArrayList<>();
        JSONArray       news   = new JSONArray(fetchRaw(API_NEWS));
        Set<String>     newIds = new HashSet<>();

        for (int i = 0; i < news.length(); i++) {
            JSONObject n        = news.getJSONObject(i);
            String     itemDate = extractDate(n.optString("publish_date", getTodayDate()));

            if (isBeforeLoginDate(itemDate)) continue;

            String  id   = n.getString("id");
            boolean isNew = repo.isNew("news", id);
            if (isNew) newIds.add(id);

            list.add(new NotifItem(
                    "NEWS",
                    "📰 " + n.optString("title", ""),
                    n.optString("category", ""),
                    itemDate,
                    isNew));
        }
        if (!newIds.isEmpty()) repo.markSeen("news", newIds);
        return list;
    }

    private List<NotifItem> fetchHospitals() throws Exception {
        List<NotifItem> list   = new ArrayList<>();
        JSONObject      root   = new JSONObject(fetchRaw(API_HOSPITALS));
        JSONArray       data   = root.getJSONArray("data");
        Set<String>     newIds = new HashSet<>();

        for (int i = 0; i < data.length(); i++) {
            JSONObject h        = data.getJSONObject(i);
            String     itemDate = extractDate(h.optString("updated_at", getTodayDate()));

            if (isBeforeLoginDate(itemDate)) continue;

            String  id     = h.getString("id");
            String  status = h.optString("hospital_status", "");
            String  emoji  = status.equals("Dangerous") ? "🔴"
                    : status.equals("Warning") ? "🟡" : "🟢";
            boolean isNew  = repo.isNew("hospitals", id);
            if (isNew) newIds.add(id);

            list.add(new NotifItem(
                    "HOSPITAL",
                    emoji + " " + h.optString("name", ""),
                    h.optString("location", "") + " · " + status,
                    itemDate,
                    isNew));
        }
        if (!newIds.isEmpty()) repo.markSeen("hospitals", newIds);
        return list;
    }

    private List<NotifItem> fetchNeeds() throws Exception {
        List<NotifItem> list   = new ArrayList<>();
        JSONObject      root   = new JSONObject(fetchRaw(API_NEEDS));
        JSONArray       data   = root.getJSONArray("data");
        Set<String>     newIds = new HashSet<>();

        for (int i = 0; i < data.length(); i++) {
            JSONObject n        = data.getJSONObject(i);
            String     itemDate = extractDate(n.optString("created_at", getTodayDate()));

            if (isBeforeLoginDate(itemDate)) continue;

            String  id     = n.getString("resource_id");
            String  status = n.optString("status", "");
            String  emoji  = status.equals("open") ? "✅" : "🔴";
            boolean isNew  = repo.isNew("needs", id);
            if (isNew) newIds.add(id);

            list.add(new NotifItem(
                    "NEED",
                    emoji + " " + n.optString("resource_name", ""),
                    n.optString("category", "") + " · " + n.optString("address", ""),
                    itemDate,
                    isNew));
        }
        if (!newIds.isEmpty()) repo.markSeen("needs", newIds);
        return list;
    }

    private List<NotifItem> fetchShelters() throws Exception {
        List<NotifItem> list     = new ArrayList<>();
        JSONArray       shelters = new JSONArray(fetchRaw(API_SHELTERS));
        Set<String>     newIds   = new HashSet<>();

        for (int i = 0; i < shelters.length(); i++) {
            JSONObject s        = shelters.getJSONObject(i);
            String     itemDate = extractDate(s.optString("created_at", getTodayDate()));

            if (isBeforeLoginDate(itemDate)) continue;

            String  name   = s.getString("shelter_name");
            String  status = s.optString("status", "");
            String  emoji  = status.equals("open")      ? "✅"
                    : status.equals("near_full") ? "🟡" : "🔴";
            boolean isNew  = repo.isNew("shelters", name);
            if (isNew) newIds.add(name);

            list.add(new NotifItem(
                    "SHELTER",
                    emoji + " " + name,
                    s.optString("location", "") + " · Available: " + s.optInt("available"),
                    itemDate,
                    isNew));
        }
        if (!newIds.isEmpty()) repo.markSeen("shelters", newIds);
        return list;
    }

    // ── Group by date ──────────────────────────────────────────────────────

    private List<Object> groupByDate(List<NotifItem> items) {
        List<Object> result   = new ArrayList<>();
        String       lastDate = "";
        for (NotifItem item : items) {
            if (!item.date.equals(lastDate)) {
                result.add(item.date);
                lastDate = item.date;
            }
            result.add(item);
        }
        return result;
    }

    // ── Date header ────────────────────────────────────────────────────────

    private void addDateHeader(String date) {
        LinearLayout row = new LinearLayout(this);
        row.setOrientation(LinearLayout.HORIZONTAL);
        row.setGravity(android.view.Gravity.CENTER_VERTICAL);
        LinearLayout.LayoutParams lp = new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.WRAP_CONTENT);
        lp.setMargins(0, 20, 0, 8);
        row.setLayoutParams(lp);

        TextView tv = new TextView(this);
        tv.setText(formatDate(date));
        tv.setTextColor(0xFF667085);
        tv.setTextSize(13f);
        tv.setTypeface(null, android.graphics.Typeface.BOLD);
        LinearLayout.LayoutParams tvLp = new LinearLayout.LayoutParams(
                0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f);
        tv.setLayoutParams(tvLp);
        row.addView(tv);

        android.widget.ImageView icon = new android.widget.ImageView(this);
        icon.setImageResource(android.R.drawable.ic_menu_my_calendar);
        icon.setColorFilter(0xFF2F66F6);
        row.addView(icon);
        container.addView(row);
    }

    // ── Format date ────────────────────────────────────────────────────────

    private String formatDate(String date) {
        try {
            SimpleDateFormat sdf      = new SimpleDateFormat("yyyy-MM-dd", Locale.getDefault());
            Date             d        = sdf.parse(date);
            Date             today    = sdf.parse(getTodayDate());
            long             diffDays = (today.getTime() - d.getTime()) / (1000 * 60 * 60 * 24);
            if (diffDays == 0) return "Today";
            if (diffDays == 1) return "Yesterday";
        } catch (Exception e) { e.printStackTrace(); }
        return date;
    }

    // ── Add card ───────────────────────────────────────────────────────────

    private void addCard(NotifItem item) {
        View     card      = getLayoutInflater().inflate(R.layout.item_notifications, container, false);
        TextView tvTitle   = card.findViewById(R.id.tvNotifTitle);
        TextView tvSub     = card.findViewById(R.id.tvNotifSub);
        TextView tvTime    = card.findViewById(R.id.tvNotifTime);
        View     dot       = card.findViewById(R.id.dotUnread);
        TextView tvSection = card.findViewById(R.id.tvNotifSection);

        if (tvSection != null) {
            tvSection.setText(item.section);
            switch (item.section) {
                case "ALERT":
                case "MAP ALERT":
                    tvSection.setTextColor(0xFFD32F2F);
                    tvSection.setBackgroundResource(R.drawable.badge_red_bg);
                    break;
                case "NEWS":
                    tvSection.setTextColor(0xFF2F66F6);
                    tvSection.setBackgroundResource(R.drawable.badge_blue_bg);
                    break;
                case "HOSPITAL":
                    tvSection.setTextColor(0xFF9D5CDB);
                    break;
                case "NEED":
                    tvSection.setTextColor(0xFF14B8A6);
                    break;
                case "SHELTER":
                    tvSection.setTextColor(0xFF10B981);
                    break;
            }
        }

        tvTitle.setText(item.title);
        tvSub.setText(item.subtitle);
        tvTime.setText(item.date);
        dot.setVisibility(item.isNew ? View.VISIBLE : View.GONE);
        if (item.isNew)
            ((CardView) card).setCardBackgroundColor(0xFFF0F4FF);

        container.addView(card);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Returns true if itemDate is strictly before the user's login date.
     * Items on the login date itself ARE shown (not filtered out).
     */
    private boolean isBeforeLoginDate(String itemDate) {
        if (loginDate == null || loginDate.isEmpty()) return false;
        if (itemDate  == null || itemDate.isEmpty())  return false;
        return itemDate.compareTo(loginDate) < 0;
    }

    private String extractDate(String datetime) {
        if (datetime == null || datetime.isEmpty()) return getTodayDate();
        if (datetime.contains(" ")) return datetime.split(" ")[0];
        if (datetime.contains("T")) return datetime.split("T")[0];
        return datetime;
    }

    private String getTodayDate() {
        return new SimpleDateFormat("yyyy-MM-dd", Locale.getDefault()).format(new Date());
    }

    private String fetchRaw(String apiUrl) throws Exception {
        int       maxRetries = 3;
        int       attempt    = 0;
        Exception lastError  = null;

        while (attempt < maxRetries) {
            attempt++;
            try {
                URL               url  = new URL(apiUrl);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setConnectTimeout(15000);
                conn.setReadTimeout(15000);
                conn.setRequestMethod("GET");
                conn.setRequestProperty("Connection", "close");

                int code = conn.getResponseCode();
                if (code != HttpURLConnection.HTTP_OK)
                    throw new Exception("HTTP error: " + code);

                BufferedReader br = new BufferedReader(
                        new InputStreamReader(conn.getInputStream()));
                StringBuilder sb = new StringBuilder();
                String line;
                while ((line = br.readLine()) != null) sb.append(line);
                br.close();
                conn.disconnect();
                return sb.toString();

            } catch (Exception e) {
                lastError = e;
                Log.w("Notif", "Attempt " + attempt + " failed for " + apiUrl + ": " + e.getMessage());
                try { Thread.sleep(2000); } catch (InterruptedException ignored) {}
            }
        }
        throw lastError;
    }

    // ── Model ──────────────────────────────────────────────────────────────

    static class NotifItem {
        String  section, title, subtitle, date;
        boolean isNew;

        NotifItem(String section, String title, String subtitle, String date, boolean isNew) {
            this.section  = section;
            this.title    = title;
            this.subtitle = subtitle;
            this.date     = date;
            this.isNew    = isNew;
        }
    }
}