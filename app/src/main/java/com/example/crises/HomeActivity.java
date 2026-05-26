package com.example.crises;

import android.Manifest;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.graphics.Color;
import android.net.Uri;
import android.os.Bundle;
import android.os.Looper;
import android.view.View;
import android.widget.Button;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AlertDialog;
import androidx.cardview.widget.CardView;
import androidx.core.app.ActivityCompat;

import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationCallback;
import com.google.android.gms.location.LocationRequest;
import com.google.android.gms.location.LocationResult;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.location.Priority;
import com.google.android.material.bottomnavigation.BottomNavigationView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.Locale;

public class HomeActivity extends BaseActivity {

    SharedPreferences prefs;
    Button quickCall;
    private QuoteTickerManager quoteTicker;

    // ── Safety card views ──────────────────────────────────────────────────
    private TextView tvSafetyStatus;
    private TextView tvSafetyLastUpdated;
    private CardView safetyCard;

    // ── Location ───────────────────────────────────────────────────────────
    private FusedLocationProviderClient fusedLocationClient;
    private LocationCallback locationCallback;
    private static final int LOCATION_PERMISSION_REQUEST = 99;
    private static final String API_URL = "http://10.0.2.2/crises_api/get_map_data.php";
    private final List<JSONObject> zoneList = new ArrayList<>();
    private boolean zonesLoaded = false;

    @Override
    protected void attachBaseContext(Context newBase) {
        SharedPreferences prefs = newBase.getSharedPreferences("settings", MODE_PRIVATE);
        String lang = prefs.getString("lang", "en");
        super.attachBaseContext(LocaleHelper.setLocale(newBase, lang));
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        prefs = getSharedPreferences("user_session", MODE_PRIVATE);
        if (!prefs.getBoolean("isLoggedIn", false)) {
            startActivity(new Intent(this, Login.class)
                    .setFlags(Intent.FLAG_ACTIVITY_NEW_TASK
                            | Intent.FLAG_ACTIVITY_CLEAR_TASK));
            finish();
            return;
        }

        setContentView(R.layout.activity_home);

        initTopAppBar();
        initProfileBanner();
        initQuickActions();
        initQuickCall();
        initNewsSection();
        initQuoteTicker();
        initBottomNavigation();
        initSafetyCard();
    }

    // ── Safety card init ───────────────────────────────────────────────────
    private void initSafetyCard() {
        safetyCard        = findViewById(R.id.safetyCard);
        tvSafetyStatus    = findViewById(R.id.tvSafetyStatus);
        tvSafetyLastUpdated = findViewById(R.id.tvSafetyLastUpdated);

        // Set default state while loading
        updateSafetyCard("loading", null);

        // Load zones first, then start GPS
        loadZonesFromApi();
    }

    // ── Load zones from API ────────────────────────────────────────────────
    private void loadZonesFromApi() {
        new Thread(() -> {
            try {
                URL url = new URL(API_URL);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("GET");
                conn.setConnectTimeout(8000);
                conn.setReadTimeout(8000);

                BufferedReader br = new BufferedReader(
                        new InputStreamReader(conn.getInputStream()));
                StringBuilder sb = new StringBuilder();
                String line;
                while ((line = br.readLine()) != null) sb.append(line);
                br.close();

                JSONObject data   = new JSONObject(sb.toString());
                JSONArray  zones  = data.getJSONArray("zones");
                JSONArray  alerts = data.getJSONArray("alerts");

                zoneList.clear();
                for (int i = 0; i < zones.length(); i++) {
                    zoneList.add(zones.getJSONObject(i));
                }

                // Also add alerts as danger zones (within 1km = danger)
                for (int i = 0; i < alerts.length(); i++) {
                    JSONObject alert = alerts.getJSONObject(i);
                    // Wrap alert as a pseudo-zone for distance checking
                    JSONObject alertZone = new JSONObject();
                    alertZone.put("center_lat",    alert.getDouble("lat"));
                    alertZone.put("center_lng",    alert.getDouble("lng"));
                    alertZone.put("radius_meters", 1000); // 1km danger radius
                    alertZone.put("type",          "danger");
                    alertZone.put("name",          alert.optString("title"));
                    zoneList.add(alertZone);
                }

                zonesLoaded = true;
                runOnUiThread(this::requestLocationPermission);

            } catch (Exception e) {
                e.printStackTrace();
                runOnUiThread(() -> updateSafetyCard("unknown", null));
            }
        }).start();
    }

    // ── Request location permission ────────────────────────────────────────
    private void requestLocationPermission() {
        if (ActivityCompat.checkSelfPermission(this,
                Manifest.permission.ACCESS_FINE_LOCATION)
                == PackageManager.PERMISSION_GRANTED) {
            startLocationUpdates();
        } else {
            ActivityCompat.requestPermissions(this,
                    new String[]{Manifest.permission.ACCESS_FINE_LOCATION},
                    LOCATION_PERMISSION_REQUEST);
        }
    }

    // ── Start GPS updates ──────────────────────────────────────────────────
    private void startLocationUpdates() {
        fusedLocationClient = LocationServices.getFusedLocationProviderClient(this);

        LocationRequest locationRequest = new LocationRequest.Builder(
                Priority.PRIORITY_HIGH_ACCURACY, 10000) // every 10 seconds
                .setMinUpdateIntervalMillis(5000)
                .build();

        locationCallback = new LocationCallback() {
            @Override
            public void onLocationResult(LocationResult result) {
                if (result == null || result.getLastLocation() == null) return;
                double lat = result.getLastLocation().getLatitude();
                double lng = result.getLastLocation().getLongitude();
                checkUserZone(lat, lng);
            }
        };

        if (ActivityCompat.checkSelfPermission(this,
                Manifest.permission.ACCESS_FINE_LOCATION)
                == PackageManager.PERMISSION_GRANTED) {
            fusedLocationClient.requestLocationUpdates(
                    locationRequest, locationCallback, Looper.getMainLooper());

            // Also get last known location immediately
            fusedLocationClient.getLastLocation().addOnSuccessListener(location -> {
                if (location != null) {
                    checkUserZone(location.getLatitude(), location.getLongitude());
                }
            });
        }
    }

    // ── Check which zone user is in ────────────────────────────────────────
    private void checkUserZone(double userLat, double userLng) {
        if (!zonesLoaded) return;

        String detectedType = "safe"; // default = safe if no zone found
        String detectedName = null;

        // Priority: danger > warning > safe
        String highestThreat = "none";

        for (JSONObject zone : zoneList) {
            try {
                double centerLat    = zone.getDouble("center_lat");
                double centerLng    = zone.getDouble("center_lng");
                double radiusMeters = zone.getDouble("radius_meters");
                String type         = zone.optString("type", "").toLowerCase();

                float[] results = new float[1];
                android.location.Location.distanceBetween(
                        userLat, userLng, centerLat, centerLng, results);

                if (results[0] <= radiusMeters) {
                    // User is inside this zone — pick highest threat
                    if (type.equals("danger") && !highestThreat.equals("danger")) {
                        highestThreat = "danger";
                        detectedName  = zone.optString("name");
                    } else if (type.equals("warning")
                            && !highestThreat.equals("danger")) {
                        highestThreat = "warning";
                        detectedName  = zone.optString("name");
                    } else if (type.equals("safe")
                            && highestThreat.equals("none")) {
                        highestThreat = "safe";
                        detectedName  = zone.optString("name");
                    }
                }
            } catch (Exception e) {
                e.printStackTrace();
            }
        }

        // If no zone found at all, still show safe
        detectedType = highestThreat.equals("none") ? "safe" : highestThreat;
        updateSafetyCard(detectedType, detectedName);
    }

    // ── Update the safety card UI ──────────────────────────────────────────
    private void updateSafetyCard(String type, String zoneName) {
        if (safetyCard == null || tvSafetyStatus == null) return;

        String statusText;
        int    cardColor;
        int    statusColor;

        switch (type.toLowerCase()) {
            case "danger":
                statusText  = "⚠️ You are in Danger";
                cardColor   = Color.parseColor("#FFF0F0");
                statusColor = Color.parseColor("#D32F2F");
                break;
            case "warning":
                statusText  = "⚠️ Warning Area Nearby";
                cardColor   = Color.parseColor("#FFFBEB");
                statusColor = Color.parseColor("#F59E0B");
                break;
            case "safe":
                statusText  = "✅ You are Safe";
                cardColor   = Color.parseColor("#F4FAF7");
                statusColor = Color.parseColor("#10B981");
                break;
            case "loading":
                statusText  = "📍 Detecting your location...";
                cardColor   = Color.parseColor("#F8FAFC");
                statusColor = Color.parseColor("#98A2B3");
                break;
            default:
                statusText  = "📍 Location unavailable";
                cardColor   = Color.parseColor("#F8FAFC");
                statusColor = Color.parseColor("#98A2B3");
                break;
        }

        // Update card background
        safetyCard.setCardBackgroundColor(cardColor);

        // Update status text
        tvSafetyStatus.setText(statusText);
        tvSafetyStatus.setTextColor(statusColor);

        // Update last updated time
        if (tvSafetyLastUpdated != null) {
            String time = new SimpleDateFormat("hh:mm a", Locale.getDefault())
                    .format(new Date());
            String zoneInfo = (zoneName != null && !zoneName.isEmpty())
                    ? " · " + zoneName : "";
            tvSafetyLastUpdated.setText("Last updated: " + time + zoneInfo);
        }
    }

    // ── Handle permission result ───────────────────────────────────────────
    @Override
    public void onRequestPermissionsResult(int requestCode,
                                           String[] permissions, int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == LOCATION_PERMISSION_REQUEST
                && grantResults.length > 0
                && grantResults[0] == PackageManager.PERMISSION_GRANTED) {
            startLocationUpdates();
        } else {
            updateSafetyCard("unknown", null);
        }
    }

    // ── Stop location when activity stops ─────────────────────────────────
    @Override
    protected void onStop() {
        super.onStop();
        if (quoteTicker != null) quoteTicker.stop();
        if (fusedLocationClient != null && locationCallback != null)
            fusedLocationClient.removeLocationUpdates(locationCallback);
    }

    @Override
    protected void onStart() {
        super.onStart();
        if (quoteTicker != null) quoteTicker.start();
    }

    // ── All your existing methods below (unchanged) ────────────────────────

    private void initQuoteTicker() {
        View quoteCard = findViewById(R.id.quoteCardInclude);
        if (quoteCard == null) return;
        quoteTicker = new QuoteTickerManager(
                getResources(),
                quoteCard.findViewById(R.id.tvQuoteText),
                quoteCard.findViewById(R.id.tvQuoteAuthor),
                quoteCard.findViewById(R.id.quoteAccentStrip),
                quoteCard.findViewById(R.id.quoteProgress)
        );
        quoteTicker.start();
    }

    private void initTopAppBar() {
        TextView tvHello = findViewById(R.id.appBrandName);
        if (tvHello != null) {
            String name = prefs.getString("full_name", "");
            tvHello.setText(name.isEmpty() ? "Hello!" : "Hello, " + name.split(" ")[0]);
        }

        ImageButton notificationBtn = findViewById(R.id.notificationBtn);
        ImageButton settingsBtn     = findViewById(R.id.settingsBtn);

        if (notificationBtn != null)
            notificationBtn.setOnClickListener(v ->
                    startActivity(new Intent(this, Tips.class)));

        if (settingsBtn != null)
            settingsBtn.setOnClickListener(v -> {
                try {
                    startActivity(new Intent(this, Settings.class));
                } catch (Exception e) {
                    android.widget.Toast.makeText(this,
                            "Settings not found!",
                            android.widget.Toast.LENGTH_SHORT).show();
                }
            });
    }

    private void initProfileBanner() {
        View banner = findViewById(R.id.profileBanner);
        if (banner == null) return;
        boolean profileComplete = prefs.getBoolean("isProfileComplete", false);
        if (!profileComplete) {
            banner.setVisibility(View.VISIBLE);
            Button btnComplete = findViewById(R.id.btnCompleteProfile);
            if (btnComplete != null)
                btnComplete.setOnClickListener(v ->
                        startActivity(new Intent(this, Account.class)));
        } else {
            banner.setVisibility(View.GONE);
        }
    }

    private void initNewsSection() {
        CardView newsCard = findViewById(R.id.newsCard);
        if (newsCard != null)
            newsCard.setOnClickListener(v ->
                    startActivity(new Intent(this, News.class)));
    }

    private void initQuickCall() {
        quickCall = findViewById(R.id.sosButton);
        quickCall.setOnClickListener(v -> {
            String[] options = {
                    "🚑 Ambulance (140)",
                    "🚓 Police (112)",
                    "🚒 Fire Brigade (175)"
            };
            String[] numbers = {"140", "112", "175"};
            new AlertDialog.Builder(this)
                    .setTitle("Choose Emergency Service")
                    .setItems(options, (dialog, which) -> {
                        Intent intent = new Intent(Intent.ACTION_DIAL);
                        intent.setData(Uri.parse("tel:" + numbers[which]));
                        startActivity(intent);
                    })
                    .show();
        });
    }

    private void initQuickActions() {
        LinearLayout findShelter = findViewById(R.id.btnFindShelter);
        LinearLayout medicalHelp = findViewById(R.id.btnMedicalHelp);
        LinearLayout needs       = findViewById(R.id.btnNeeds);
        LinearLayout emotional = findViewById(R.id.btnEmotionalSupport);

        if (findShelter != null)
            findShelter.setOnClickListener(v ->
                    startActivity(new Intent(this, PublicShelters.class)));
        if (medicalHelp != null)
            medicalHelp.setOnClickListener(v ->
                    startActivity(new Intent(this, Hospitals.class)));
        if (needs != null)
            needs.setOnClickListener(v ->
                    startActivity(new Intent(this, Needs.class)));
        if (emotional != null)
            emotional.setOnClickListener(v ->
                    startActivity(new Intent(this, PsychologicalSupport.class)));
    }

    private void initBottomNavigation() {
        BottomNavigationView bottomNav = findViewById(R.id.bottomNavigation);
        if (bottomNav == null) return;
        bottomNav.setSelectedItemId(R.id.nav_home);
        bottomNav.setOnItemSelectedListener(item -> {
            int id = item.getItemId();
            if (id == R.id.nav_home) return true;
            Intent intent = null;
            if (id == R.id.nav_alerts)       intent = new Intent(this, Alerts.class);
            else if (id == R.id.nav_map)     intent = new Intent(this, MapActivity.class);
            else if (id == R.id.nav_profile) intent = new Intent(this, Account.class);
            if (intent != null) {
                startActivity(intent);
                overridePendingTransition(0, 0);
                return true;
            }
            return false;
        });
    }
}