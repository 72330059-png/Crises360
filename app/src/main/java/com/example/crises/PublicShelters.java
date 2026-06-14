package com.example.crises;

import android.Manifest;
import android.content.pm.PackageManager;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.location.Priority;
import com.google.android.gms.tasks.CancellationTokenSource;
import com.google.android.material.tabs.TabLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

public class PublicShelters extends AppCompatActivity {

    private static final String TAG = "PublicShelters";

    // ── Views ─────────────────────────────────────────────────────────────────
    private RecyclerView         recyclerView;
    private PublicShelterAdapter adapter;
    private TabLayout            tabLayout;
    private ProgressBar          loadingBar;
    private TextView             locationLabel;

    // ── Data ──────────────────────────────────────────────────────────────────
    private final ArrayList<PublicShelter> shelterList     = new ArrayList<>();
    private final ArrayList<PublicShelter> recommendedList = new ArrayList<>(); // ← separate

    // ── Tabs ──────────────────────────────────────────────────────────────────
    private final String[] statuses = {"All", "Open", "Near-Full", "Full"};

    // ── Network ───────────────────────────────────────────────────────────────
    private RequestQueue queue;
    private static final String BASE_URL = "https://crises360-mobile-api.onrender.com/get_shelters.php";

    // ── Location ──────────────────────────────────────────────────────────────
    private FusedLocationProviderClient fusedLocationClient;
    private double userLat = Double.MIN_VALUE;
    private double userLng = Double.MIN_VALUE;
    private static final int LOCATION_PERMISSION_REQUEST = 1002;

    // ─────────────────────────────────────────────────────────────────────────
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_public_shelters);

        recyclerView  = findViewById(R.id.recyclerView);
        tabLayout     = findViewById(R.id.tabLayout);
        loadingBar    = findViewById(R.id.loadingBar);
        locationLabel = findViewById(R.id.locationLabel);

        recyclerView.setLayoutManager(new LinearLayoutManager(this));
        adapter = new PublicShelterAdapter(new ArrayList<>(), new ArrayList<>());
        recyclerView.setAdapter(adapter);

        queue = Volley.newRequestQueue(this);
        findViewById(R.id.btnBack).setOnClickListener(v -> finish());

        setupTabs();
        fusedLocationClient = LocationServices.getFusedLocationProviderClient(this);
        requestLocationAndLoad();
    }

    // ── LOCATION ──────────────────────────────────────────────────────────────

    private void requestLocationAndLoad() {
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION)
                == PackageManager.PERMISSION_GRANTED) {
            getCurrentLocation();
        } else {
            ActivityCompat.requestPermissions(this,
                    new String[]{Manifest.permission.ACCESS_FINE_LOCATION},
                    LOCATION_PERMISSION_REQUEST);
        }
    }

    private void getCurrentLocation() {
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION)
                != PackageManager.PERMISSION_GRANTED) return;

        setLoading(true);
        showLabel("📍 Getting your location...");

        CancellationTokenSource cts = new CancellationTokenSource();
        fusedLocationClient
                .getCurrentLocation(Priority.PRIORITY_HIGH_ACCURACY, cts.getToken())
                .addOnSuccessListener(location -> {
                    if (location != null) {
                        userLat = location.getLatitude();
                        userLng = location.getLongitude();
                        Log.d(TAG, "GPS: " + userLat + ", " + userLng);
                        showLabel("📍 Location found — finding nearby shelters...");
                        loadShelters();
                    } else {
                        tryLastLocationFallback();
                    }
                })
                .addOnFailureListener(e -> tryLastLocationFallback());
    }

    private void tryLastLocationFallback() {
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION)
                != PackageManager.PERMISSION_GRANTED) return;

        fusedLocationClient.getLastLocation()
                .addOnSuccessListener(location -> {
                    if (location != null) {
                        userLat = location.getLatitude();
                        userLng = location.getLongitude();
                        showLabel("📍 Location found — finding nearby shelters...");
                    } else {
                        showLabel("📡 GPS unavailable — showing all shelters");
                    }
                    loadShelters();
                })
                .addOnFailureListener(e -> {
                    showLabel("📡 Location error — showing all shelters");
                    loadShelters();
                });
    }

    @Override
    public void onRequestPermissionsResult(int requestCode,
                                           @NonNull String[] permissions,
                                           @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == LOCATION_PERMISSION_REQUEST) {
            if (grantResults.length > 0 && grantResults[0] == PackageManager.PERMISSION_GRANTED) {
                getCurrentLocation();
            } else {
                showLabel("📡 Location denied — showing all shelters");
                loadShelters();
            }
        }
    }

    // ── NETWORK ───────────────────────────────────────────────────────────────

    private void loadShelters() {
        setLoading(true);

        StringBuilder url = new StringBuilder(BASE_URL).append("?v=1");

        if (userLat != Double.MIN_VALUE && userLng != Double.MIN_VALUE) {
            url.append("&lat=").append(userLat)
                    .append("&lng=").append(userLng);
        }

        int tabPos = tabLayout.getSelectedTabPosition();
        String sel = statuses[tabPos];
        if (!sel.equalsIgnoreCase("All")) {
            url.append("&status=").append(sel);
        }

        Log.d(TAG, "URL: " + url);

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.GET, url.toString(), null,
                response -> {
                    try {
                        shelterList.clear();
                        recommendedList.clear();

                        // ── "recommended" array → header chips ────────────────
                        // Always nearest ≤15 km regardless of tab filter
                        if (response.has("recommended")) {
                            JSONArray recArr = response.getJSONArray("recommended");
                            for (int i = 0; i < recArr.length(); i++) {
                                JSONObject obj = recArr.getJSONObject(i);
                                PublicShelter s = parseShelter(obj);
                                s.setRecommended(true);
                                s.setRecommendationRank(i + 1);
                                recommendedList.add(s);
                            }
                        }

                        // ── "data" array → full list (respects tab filter) ────
                        JSONArray arr = response.getJSONArray("data");
                        for (int i = 0; i < arr.length(); i++) {
                            JSONObject obj = arr.getJSONObject(i);
                            PublicShelter s = parseShelter(obj);
                            s.setRecommended(obj.optBoolean("is_recommended", false));
                            s.setRecommendationRank(obj.optInt("recommendation_rank", 0));
                            shelterList.add(s);
                        }

                        // ── FIX: "near you" count = recommendedList (≤15 km) ──
                        //         total count  = shelterList (full list)
                        if (locationLabel != null) {
                            if (userLat != Double.MIN_VALUE) {
                                locationLabel.setText(
                                        "📍 " + recommendedList.size()
                                                + " shelters found near you (within 15 km)");
                            } else {
                                locationLabel.setText(
                                        "🏠 " + shelterList.size() + " shelters found");
                            }
                        }

                        adapter.updateList(
                                new ArrayList<>(shelterList),
                                new ArrayList<>(recommendedList)
                        );

                    } catch (Exception e) {
                        Log.e(TAG, "Parse error: " + e.getMessage());
                        showLabel("❌ Error loading shelters");
                    } finally {
                        setLoading(false);
                    }
                },
                error -> {
                    setLoading(false);
                    Log.e(TAG, "Network: " + error.getMessage());
                    showLabel("❌ Network error — check connection");
                }
        );

        queue.add(request);
    }

    // ── PARSE SHELTER ─────────────────────────────────────────────────────────

    private PublicShelter parseShelter(JSONObject obj) throws Exception {
        return new PublicShelter(
                obj.optString("shelter_name", ""),
                obj.optString("location",     ""),
                obj.optString("status",       ""),
                obj.optString("org_name",     ""),
                obj.optInt("capacity",  0),
                obj.optInt("occupied",  0),
                obj.optInt("available", 0),
                obj.optDouble("occupancy_pct", 0),
                obj.optDouble("lat", 0),
                obj.optDouble("lng", 0),
                (obj.has("distance_km") && !obj.isNull("distance_km"))
                        ? obj.getDouble("distance_km") : -1,
                obj.optString("created_at", "")
        );
    }

    // ── TABS ──────────────────────────────────────────────────────────────────

    private void setupTabs() {
        for (String s : statuses)
            tabLayout.addTab(tabLayout.newTab().setText(s));

        tabLayout.addOnTabSelectedListener(new TabLayout.OnTabSelectedListener() {
            @Override public void onTabSelected(TabLayout.Tab tab)   { loadShelters(); }
            @Override public void onTabUnselected(TabLayout.Tab tab) {}
            @Override public void onTabReselected(TabLayout.Tab tab) {}
        });
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    private void setLoading(boolean on) {
        if (loadingBar   != null) loadingBar.setVisibility(on ? View.VISIBLE : View.GONE);
        if (recyclerView != null) recyclerView.setAlpha(on ? 0.4f : 1f);
    }

    private void showLabel(String text) {
        if (locationLabel != null) {
            locationLabel.setText(text);
            locationLabel.setVisibility(View.VISIBLE);
        }
    }
}