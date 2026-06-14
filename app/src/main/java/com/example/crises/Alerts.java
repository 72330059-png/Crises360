package com.example.crises;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.os.Bundle;
import android.view.View;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.activity.EdgeToEdge;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.Volley;
import com.google.android.material.bottomnavigation.BottomNavigationView;
import com.google.android.material.tabs.TabLayout;

import org.json.JSONObject;

import java.util.ArrayList;

public class Alerts extends BaseActivity {

    RecyclerView          recyclerView;
    ArrayList<AlertModel> fullList     = new ArrayList<>();
    ArrayList<AlertModel> filteredList = new ArrayList<>();
    AlertsAdapter         adapter;
    BottomNavigationView  bottomNav;
    TabLayout             tabLayout;
    ProgressBar           progressBar;
    TextView              tvEmpty;

    // Login date stored as "yyyy-MM-dd" — alerts from before this date are hidden
    private String loginDate;

    @Override
    protected void attachBaseContext(Context newBase) {
        SharedPreferences prefs =
                newBase.getSharedPreferences("settings", MODE_PRIVATE);
        String lang = prefs.getString("lang", "en");
        super.attachBaseContext(LocaleHelper.setLocale(newBase, lang));
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        if (!checkProfileCompletion()) return;

        setContentView(R.layout.activity_alerts);

        // Read login_date saved at login time (format: "yyyy-MM-dd")
        loginDate = getSharedPreferences("notification_prefs", MODE_PRIVATE)
                .getString("login_date", null);

        android.util.Log.d("ALERT_FILTER", "loginDate = " + loginDate);

        recyclerView = findViewById(R.id.recyclerAlerts);
        progressBar  = findViewById(R.id.progressBar);
        tvEmpty      = findViewById(R.id.tvEmpty);

        recyclerView.setLayoutManager(new LinearLayoutManager(this));
        adapter = new AlertsAdapter(filteredList);
        recyclerView.setAdapter(adapter);

        initTabFilters();
        initBottomNavigation();
        loadAlertsFromServer();
    }

    private void loadAlertsFromServer() {
        if (progressBar != null) progressBar.setVisibility(View.VISIBLE);
        if (tvEmpty != null)     tvEmpty.setVisibility(View.GONE);

        String url = "https://crises360-mobile-api.onrender.com/get_alerts.php";

        JsonArrayRequest request = new JsonArrayRequest(
                Request.Method.GET, url, null,
                response -> {
                    try {
                        fullList.clear();

                        for (int i = 0; i < response.length(); i++) {
                            JSONObject obj = response.getJSONObject(i);

                            // Full timestamp from API e.g. "2026-06-13 21:44:38"
                            String rawTime = obj.optString("time", "").trim();

                            // Extract only the date part "yyyy-MM-dd" for comparison
                            // This matches the format loginDate is saved in
                            String alertDate = rawTime.length() >= 10
                                    ? rawTime.substring(0, 10)
                                    : rawTime;

                            android.util.Log.d("ALERT_FILTER",
                                    "alertDate=" + alertDate + "  loginDate=" + loginDate);

                            // Show alerts from the login date onward (inclusive)
                            // Only skip alerts from days strictly before the login date
                            if (loginDate != null && !alertDate.isEmpty()
                                    && alertDate.compareTo(loginDate) < 0) {
                                continue;
                            }

                            int    id       = obj.getInt("id");
                            String message  = obj.optString("message", "");
                            String region   = obj.optString("region", "");
                            String severity = obj.optString("severity", "");

                            fullList.add(new AlertModel(
                                    id, severity, message, region, rawTime));
                        }

                        if (progressBar != null)
                            progressBar.setVisibility(View.GONE);

                        int position = (tabLayout != null)
                                ? tabLayout.getSelectedTabPosition() : 0;
                        filterList(position);

                    } catch (Exception e) {
                        e.printStackTrace();
                        if (progressBar != null)
                            progressBar.setVisibility(View.GONE);
                        if (tvEmpty != null)
                            tvEmpty.setVisibility(View.VISIBLE);
                    }
                },
                error -> {
                    error.printStackTrace();
                    if (progressBar != null)
                        progressBar.setVisibility(View.GONE);
                    if (tvEmpty != null) {
                        tvEmpty.setVisibility(View.VISIBLE);
                        tvEmpty.setText("Failed to load alerts.\nCheck connection.");
                    }
                }
        );

        RequestQueue queue = Volley.newRequestQueue(this);
        queue.add(request);
    }

    private void initTabFilters() {
        tabLayout = findViewById(R.id.tabFilters);
        if (tabLayout == null) return;

        TabLayout.Tab defaultTab = tabLayout.getTabAt(0);
        if (defaultTab != null) defaultTab.select();

        tabLayout.addOnTabSelectedListener(
                new TabLayout.OnTabSelectedListener() {
                    @Override
                    public void onTabSelected(TabLayout.Tab tab) {
                        filterList(tab.getPosition());
                    }
                    @Override public void onTabUnselected(TabLayout.Tab tab) {}
                    @Override public void onTabReselected(TabLayout.Tab tab) {}
                });
    }

    private void filterList(int position) {
        filteredList.clear();

        for (AlertModel alert : fullList) {
            String severity = alert.getSeverity() != null
                    ? alert.getSeverity() : "";

            if (position == 0) {
                filteredList.add(alert);                                        // All
            } else if (position == 1 && severity.equalsIgnoreCase("Warning")) {
                filteredList.add(alert);                                        // Warning
            } else if (position == 2 && severity.equalsIgnoreCase("Critical")) {
                filteredList.add(alert);                                        // Critical
            } else if (position == 3 && severity.equalsIgnoreCase("Info")) {
                filteredList.add(alert);                                        // Info
            }
        }

        if (tvEmpty != null)
            tvEmpty.setVisibility(
                    filteredList.isEmpty() ? View.VISIBLE : View.GONE);

        adapter.notifyDataSetChanged();
    }

    private void initBottomNavigation() {
        BottomNavigationView bottomNav = findViewById(R.id.bottomNavigation);
        bottomNav.setItemActiveIndicatorColor(
                ColorStateList.valueOf(Color.parseColor("#EEF2FF")));
        bottomNav.setSelectedItemId(R.id.nav_alerts);
        bottomNav.setOnItemSelectedListener(item -> {
            int id = item.getItemId();
            if (id == R.id.nav_home) {
                startActivity(new Intent(this, HomeActivity.class));
            } else if (id == R.id.nav_profile) {
                startActivity(new Intent(this, Account.class));
            } else if (id == R.id.nav_map) {
                startActivity(new Intent(this, MapActivity.class));
            }
            return true;
        });
    }
}