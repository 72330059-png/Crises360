package com.example.crises;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
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

    // Login date anchor — alerts created before this date are hidden
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

        // Read the login date saved when the user authenticated
        loginDate = getSharedPreferences("notification_prefs", MODE_PRIVATE)
                .getString("login_date", null);

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

        String url = "http://10.0.2.2/crises_api/get_alerts.php";

        JsonArrayRequest request = new JsonArrayRequest(
                Request.Method.GET, url, null,
                response -> {
                    try {
                        fullList.clear();

                        for (int i = 0; i < response.length(); i++) {
                            JSONObject obj = response.getJSONObject(i);

                            // Extract the alert's creation date (format: "yyyy-MM-dd HH:mm:ss")
                            String rawTime  = obj.optString("time", "");
                            String alertDate = rawTime.contains(" ")
                                    ? rawTime.split(" ")[0]   // "yyyy-MM-dd"
                                    : rawTime;

                            // Skip alerts created before the user's login date
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
                filteredList.add(alert);
            } else if (position == 1) {
                if (severity.equalsIgnoreCase("Warning"))
                    filteredList.add(alert);
            } else if (position == 2) {
                if (severity.equalsIgnoreCase("Critical"))
                    filteredList.add(alert);
            } else if (position == 3) {
                if (severity.equalsIgnoreCase("Info"))
                    filteredList.add(alert);
            }
        }

        if (tvEmpty != null)
            tvEmpty.setVisibility(
                    filteredList.isEmpty() ? View.VISIBLE : View.GONE);

        adapter.notifyDataSetChanged();
    }

    private void initBottomNavigation() {
        bottomNav = findViewById(R.id.bottomNavigation);
        if (bottomNav == null) return;
        bottomNav.setSelectedItemId(R.id.nav_alerts);

        bottomNav.setOnItemSelectedListener(item -> {
            int id = item.getItemId();
            if (id == R.id.nav_alerts) return true;
            Intent intent = null;
            if (id == R.id.nav_home)
                intent = new Intent(this, HomeActivity.class);
            else if (id == R.id.nav_map)
                intent = new Intent(this, MapActivity.class);
            else if (id == R.id.nav_profile)
                intent = new Intent(this, Account.class);
            if (intent != null) {
                startActivity(intent);
                overridePendingTransition(0, 0);
                finish();
                return true;
            }
            return false;
        });
    }
}