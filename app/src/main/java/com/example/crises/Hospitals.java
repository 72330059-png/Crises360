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

public class Hospitals extends AppCompatActivity {

    private static final String TAG = "Hospitals";

    private RecyclerView    recyclerView;
    private HospitalAdapter adapter;
    private TabLayout       statusTabLayout;
    private ProgressBar     loadingBar;
    private TextView        locationLabel;

    private final ArrayList<Hospital> hospitalList    = new ArrayList<>();
    private final ArrayList<Hospital> recommendedList = new ArrayList<>(); // ← separate

    private final String[] statuses = {"All", "Safe", "Warning", "Dangerous"};

    private RequestQueue queue;
    private static final String BASE_URL  = "https://crises360-mobile-api.onrender.com/get_hospitals.php";
    private static final int    RADIUS_KM = 50;

    private FusedLocationProviderClient fusedLocationClient;
    private double userLat = Double.MIN_VALUE;
    private double userLng = Double.MIN_VALUE;
    private static final int LOCATION_PERMISSION_REQUEST = 1001;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_hospitals);

        recyclerView    = findViewById(R.id.hospitalRecycler);
        statusTabLayout = findViewById(R.id.statusTabLayout);
        loadingBar      = findViewById(R.id.loadingBar);
        locationLabel   = findViewById(R.id.locationLabel);

        recyclerView.setLayoutManager(new LinearLayoutManager(this));
        adapter = new HospitalAdapter(new ArrayList<>(), new ArrayList<>());
        recyclerView.setAdapter(adapter);

        queue = Volley.newRequestQueue(this);

        if (findViewById(R.id.btnBack) != null)
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
                        showLabel("📍 Location found — finding nearby hospitals...");
                        loadHospitals();
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
                        showLabel("📍 Location found — finding nearby hospitals...");
                    } else {
                        showLabel("📡 GPS unavailable — showing all hospitals");
                    }
                    loadHospitals();
                })
                .addOnFailureListener(e -> {
                    showLabel("📡 Location error — showing all hospitals");
                    loadHospitals();
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
                showLabel("📡 Location denied — showing all hospitals");
                loadHospitals();
            }
        }
    }

    // ── NETWORK ───────────────────────────────────────────────────────────────

    private void loadHospitals() {
        setLoading(true);

        StringBuilder url = new StringBuilder(BASE_URL)
                .append("?radius=").append(RADIUS_KM);

        if (userLat != Double.MIN_VALUE && userLng != Double.MIN_VALUE) {
            url.append("&lat=").append(userLat)
                    .append("&lng=").append(userLng);
        }

        int tabPos = statusTabLayout.getSelectedTabPosition();
        String sel = statuses[tabPos];
        if (!sel.equalsIgnoreCase("All")) {
            url.append("&status=").append(sel);
        }

        Log.d(TAG, "URL: " + url);

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.GET, url.toString(), null,
                response -> {
                    try {
                        hospitalList.clear();
                        recommendedList.clear();

                        // ── Parse "recommended" array → header chips ──────────
                        // This is ALWAYS the nearest ≤15 km, non-Dangerous hospitals
                        // It never changes when user switches tabs — that's the point
                        if (response.has("recommended")) {
                            JSONArray recArr = response.getJSONArray("recommended");
                            for (int i = 0; i < recArr.length(); i++) {
                                Hospital h = parseHospital(recArr.getJSONObject(i));
                                h.setRecommended(true);
                                recommendedList.add(h);
                            }
                        }

                        // ── Parse "data" array → full list ────────────────────
                        // This respects the tab filter (Safe / Warning / Dangerous)
                        JSONArray dataArr = response.getJSONArray("data");
                        for (int i = 0; i < dataArr.length(); i++) {
                            Hospital h = parseHospital(dataArr.getJSONObject(i));
                            h.setRecommended(dataArr.getJSONObject(i)
                                    .optBoolean("is_recommended", false));
                            hospitalList.add(h);
                        }

                        showLabel(userLat != Double.MIN_VALUE
                                ? "📍 " + recommendedList.size() + " hospitals found near you (within 15 km)"
                                : "🏥 " + hospitalList.size() + " hospitals found");

                        // Pass BOTH lists to adapter
                        adapter.updateLists(
                                new ArrayList<>(recommendedList),  // → header chips
                                new ArrayList<>(hospitalList)      // → card list
                        );

                    } catch (Exception e) {
                        Log.e(TAG, "Parse error: " + e.getMessage());
                        showLabel("❌ Error loading data");
                    } finally {
                        setLoading(false);
                    }
                },
                error -> {
                    Log.e(TAG, "Network: " + error.getMessage());
                    setLoading(false);
                    showLabel("❌ Network error — check connection");
                }
        );

        queue.add(request);
    }

    // ── PARSE HOSPITAL FROM JSON ───────────────────────────────────────────────

    private Hospital parseHospital(JSONObject obj) throws Exception {
        return new Hospital(
                obj.optString("name",     "—"),
                obj.optString("location", "—"),
                obj.optString("location", "—"),
                obj.optString("phone",    ""),
                obj.optInt("total_beds",     0),
                obj.optInt("available_beds", 0),
                obj.optInt("occupied_beds",  0),
                obj.optDouble("occupancy_pct", 0),
                obj.optDouble("lat", 0),
                obj.optDouble("lng", 0),
                (obj.has("distance_km") && !obj.isNull("distance_km"))
                        ? obj.getDouble("distance_km") : -1,
                obj.optString("hospital_status", ""),
                obj.optString("updated_at", "")
        );
    }


    private void setupTabs() {
        for (String s : statuses)
            statusTabLayout.addTab(statusTabLayout.newTab().setText(s));

        statusTabLayout.addOnTabSelectedListener(new TabLayout.OnTabSelectedListener() {
            @Override public void onTabSelected(TabLayout.Tab tab)   { loadHospitals(); }
            @Override public void onTabUnselected(TabLayout.Tab tab) {}
            @Override public void onTabReselected(TabLayout.Tab tab) {}
        });
    }



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