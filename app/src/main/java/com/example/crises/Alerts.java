package com.example.crises;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;

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

    RecyclerView recyclerView;
    ArrayList<AlertModel> fullList = new ArrayList<>();
    ArrayList<AlertModel> filteredList = new ArrayList<>();
    AlertsAdapter adapter;

    BottomNavigationView bottomNav;
    TabLayout tabLayout;

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
        if (!checkProfileCompletion()) return;

        setContentView(R.layout.activity_alerts);

        recyclerView = findViewById(R.id.recyclerAlerts);
        recyclerView.setLayoutManager(new LinearLayoutManager(this));

        adapter = new AlertsAdapter(filteredList);
        recyclerView.setAdapter(adapter);

        initTabFilters();          // ✅ MUST come before loading data
        initBottomNavigation();

        loadAlertsFromServer();    // ✅ safe now
    }

    private void loadAlertsFromServer() {
        String url = "http://10.0.2.2/crises_api/get_alerts.php?user_id=" + getUserId();

        JsonArrayRequest request = new JsonArrayRequest(Request.Method.GET, url, null,
                response -> {
                    try {
                        fullList.clear();

                        for (int i = 0; i < response.length(); i++) {
                            JSONObject obj = response.getJSONObject(i);

                            int id = obj.getInt("id");
                            String message = obj.getString("message");
                            String location = obj.getString("region");
                            String time = obj.getString("time");
                            String severity = obj.getString("severity");

                            fullList.add(new AlertModel(id, severity, message, location, time));
                        }

                        // ✅ SAFE fallback if tabLayout not ready
                        int position = (tabLayout != null)
                                ? tabLayout.getSelectedTabPosition()
                                : 0;

                        filterList(position);

                    } catch (Exception e) {
                        e.printStackTrace();
                    }
                },
                error -> error.printStackTrace()
        );

        RequestQueue queue = Volley.newRequestQueue(this);
        queue.add(request);
    }

    private void initTabFilters() {
        tabLayout = findViewById(R.id.tabFilters);

        // Default tab = "All Alerts"
        TabLayout.Tab defaultTab = tabLayout.getTabAt(0);
        if (defaultTab != null) defaultTab.select();

        tabLayout.addOnTabSelectedListener(new TabLayout.OnTabSelectedListener() {
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

        if (position == 0) {
            filteredList.addAll(fullList);
        }
        else if (position == 1) {
            for (AlertModel alert : fullList) {
                if ("Warning".equalsIgnoreCase(alert.getSeverity())) {
                    filteredList.add(alert);
                }
            }
        }
        else if (position == 2) {
            for (AlertModel alert : fullList) {
                if ("Update".equalsIgnoreCase(alert.getSeverity())) {
                    filteredList.add(alert);
                }
            }
        }

        adapter.notifyDataSetChanged();
    }

    private String getUserId() {
        SharedPreferences prefs = getSharedPreferences("user", MODE_PRIVATE);
        return String.valueOf(prefs.getInt("user_id", -1));
    }

    private void initBottomNavigation() {
        bottomNav = findViewById(R.id.bottomNavigation);
        bottomNav.setSelectedItemId(R.id.nav_alerts);

        bottomNav.setOnItemSelectedListener(item -> {
            int id = item.getItemId();

            if (id == R.id.nav_alerts) return true;

            if (id == R.id.nav_home) {
                startActivity(new Intent(this, HomeActivity.class));
            } else if (id == R.id.nav_map) {
                startActivity(new Intent(this, MapActivity.class));
            } else if (id == R.id.nav_profile) {
                startActivity(new Intent(this, Account.class));
            }

            overridePendingTransition(0, 0);
            finish();
            return true;
        });
    }
}