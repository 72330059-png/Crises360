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

public class Needs extends AppCompatActivity {

    private static final String TAG = "Needs";

    // ── Views ─────────────────────────────────────────────────────────────────
    private RecyclerView recyclerView;
    private NeedsAdapter adapter;
    private TabLayout    tabLayout;
    private ProgressBar  loadingBar;
    private TextView     locationLabel;

    // ── Data ──────────────────────────────────────────────────────────────────
    private final ArrayList<Need> needList        = new ArrayList<>();
    private final ArrayList<Need> recommendedList = new ArrayList<>();

    // ── Tabs ──────────────────────────────────────────────────────────────────
    private static final String[][] TABS = {
            {"⭐ Nearby",  "recommended"},
            {"All",        "all"},
            {"Food",       "food"},
            {"Water",      "water"},
            {"Medical",    "medical"},
            {"Fuel",       "fuel"},
    };

    // ── Network ───────────────────────────────────────────────────────────────
    private RequestQueue queue;
    private static final String BASE_URL =
            "https://crises360-mobile-api.onrender.com/get_needs.php";

    // ── Location ──────────────────────────────────────────────────────────────
    private FusedLocationProviderClient fusedLocationClient;
    private double userLat = Double.MIN_VALUE;
    private double userLng = Double.MIN_VALUE;
    private static final int LOCATION_PERMISSION_REQUEST = 1003;

    // ─────────────────────────────────────────────────────────────────────────
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_needs);

        recyclerView  = findViewById(R.id.recyclerNeeds);
        tabLayout     = findViewById(R.id.tabLayout);
        loadingBar    = findViewById(R.id.loadingBar);
        locationLabel = findViewById(R.id.locationLabel);

        recyclerView.setLayoutManager(new LinearLayoutManager(this));
        adapter = new NeedsAdapter(new ArrayList<>(), new ArrayList<>());
        recyclerView.setAdapter(adapter);

        queue = Volley.newRequestQueue(this);
        findViewById(R.id.btnBack).setOnClickListener(v -> finish());

        setupTabs();
        fusedLocationClient = LocationServices.getFusedLocationProviderClient(this);
        requestLocationAndLoad();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LOCATION
    // ─────────────────────────────────────────────────────────────────────────

    private void requestLocationAndLoad() {
        if (ContextCompat.checkSelfPermission(this,
                Manifest.permission.ACCESS_FINE_LOCATION)
                == PackageManager.PERMISSION_GRANTED) {
            getCurrentLocation();
        } else {
            ActivityCompat.requestPermissions(this,
                    new String[]{Manifest.permission.ACCESS_FINE_LOCATION},
                    LOCATION_PERMISSION_REQUEST);
        }
    }

    private void getCurrentLocation() {
        if (ActivityCompat.checkSelfPermission(this,
                Manifest.permission.ACCESS_FINE_LOCATION)
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
                        showLabel("📍 Location found — finding nearby resources...");
                        loadNeeds();
                    } else {
                        tryLastLocationFallback();
                    }
                })
                .addOnFailureListener(e -> tryLastLocationFallback());
    }

    private void tryLastLocationFallback() {
        if (ActivityCompat.checkSelfPermission(this,
                Manifest.permission.ACCESS_FINE_LOCATION)
                != PackageManager.PERMISSION_GRANTED) return;

        fusedLocationClient.getLastLocation()
                .addOnSuccessListener(location -> {
                    if (location != null) {
                        userLat = location.getLatitude();
                        userLng = location.getLongitude();
                        showLabel("📍 Location found — finding nearby resources...");
                    } else {
                        showLabel("📡 GPS unavailable — showing all resources");
                    }
                    loadNeeds();
                })
                .addOnFailureListener(e -> {
                    showLabel("📡 Location error — showing all resources");
                    loadNeeds();
                });
    }

    @Override
    public void onRequestPermissionsResult(int requestCode,
                                           @NonNull String[] permissions,
                                           @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == LOCATION_PERMISSION_REQUEST) {
            if (grantResults.length > 0
                    && grantResults[0] == PackageManager.PERMISSION_GRANTED) {
                getCurrentLocation();
            } else {
                showLabel("📡 Permission denied — showing all resources");
                loadNeeds();
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // NETWORK
    // ─────────────────────────────────────────────────────────────────────────

    private void loadNeeds() {
        setLoading(true);

        int    tabPos = tabLayout.getSelectedTabPosition();
        String tabKey = TABS[tabPos][1];

        StringBuilder url = new StringBuilder(BASE_URL).append("?v=1");

        if (userLat != Double.MIN_VALUE && userLng != Double.MIN_VALUE) {
            url.append("&lat=").append(userLat)
                    .append("&lng=").append(userLng);
        }

        // Send category filter for Food/Water/Medical/Fuel tabs
        // Recommended and All tabs send no category — PHP returns everything
        if (!tabKey.equals("recommended") && !tabKey.equals("all")) {
            url.append("&category=").append(tabKey);
        }

        Log.d(TAG, "URL: " + url);

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.GET, url.toString(), null,
                response -> handleResponse(response, tabKey),
                error -> {
                    setLoading(false);
                    Log.e(TAG, "Network: " + error.toString());
                    showLabel("❌ Network error — check connection");
                }
        );

        queue.add(request);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RESPONSE HANDLER
    // ─────────────────────────────────────────────────────────────────────────

    private void handleResponse(JSONObject response, String tabKey) {
        try {
            needList.clear();
            recommendedList.clear();

            if (!"success".equals(response.optString("status"))) {
                showLabel("❌ " + response.optString("message", "Error"));
                setLoading(false);
                return;
            }

            // ── Recommended array → ALL within 15km sorted by distance ────────
            JSONArray recArr = response.optJSONArray("recommended");
            if (recArr != null) {
                for (int i = 0; i < recArr.length(); i++) {
                    JSONObject obj = recArr.getJSONObject(i);
                    Need need = parseNeed(obj);
                    need.setRecommended(true);
                    need.setRecommendationRank(i + 1);
                    recommendedList.add(need);
                }
            }

            // ── Data array → filtered display list ────────────────────────────
            JSONArray arr = response.optJSONArray("data");
            if (arr == null) arr = new JSONArray();
            for (int i = 0; i < arr.length(); i++) {
                JSONObject obj = arr.getJSONObject(i);
                Need need = parseNeed(obj);
                need.setRecommended(obj.optBoolean("is_recommended", false));
                need.setRecommendationRank(obj.optInt("recommendation_rank", 0));
                needList.add(need);
            }

            // ── Update location label ─────────────────────────────────────────
            if (userLat != Double.MIN_VALUE) {
                showLabel("📦 " + recommendedList.size()
                        + " resources within 15 km");
            } else {
                showLabel("📦 " + needList.size() + " resources found");
            }

            // ── Nearby tab → show full recommended list (all within 15km) ─────
            // Other tabs → show filtered list with ⭐ badges on nearby ones
            List<Need> displayList = tabKey.equals("recommended")
                    ? new ArrayList<>(recommendedList)
                    : new ArrayList<>(needList);

            adapter.updateList(displayList, new ArrayList<>(recommendedList));

        } catch (Exception e) {
            Log.e(TAG, "Parse error: " + e.getMessage());
            showLabel("❌ Parsing error");
        } finally {
            setLoading(false);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARSE NEED
    // ─────────────────────────────────────────────────────────────────────────

    private Need parseNeed(JSONObject obj) {
        return new Need(
                obj.optString("resource_name",     ""),
                obj.optString("category",          ""),
                obj.optString("status",            ""),
                obj.optString("location",          ""),
                obj.optString("address",           ""),
                obj.optString("contact_number",    ""),
                obj.optString("opening_hours",     ""),
                obj.optString("notes",             ""),
                obj.optString("organization_name", ""),
                (obj.has("distance_km") && !obj.isNull("distance_km"))
                        ? obj.optDouble("distance_km", -1) : -1
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TABS
    // ─────────────────────────────────────────────────────────────────────────

    private void setupTabs() {
        for (String[] tab : TABS)
            tabLayout.addTab(tabLayout.newTab().setText(tab[0]));

        tabLayout.addOnTabSelectedListener(new TabLayout.OnTabSelectedListener() {
            @Override public void onTabSelected(TabLayout.Tab tab)   { loadNeeds(); }
            @Override public void onTabUnselected(TabLayout.Tab tab) {}
            @Override public void onTabReselected(TabLayout.Tab tab) {}
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UI HELPERS
    // ─────────────────────────────────────────────────────────────────────────

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