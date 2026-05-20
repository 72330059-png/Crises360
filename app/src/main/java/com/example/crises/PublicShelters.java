package com.example.crises;

import android.os.Bundle;
import android.util.Log;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.google.android.material.tabs.TabLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;

public class PublicShelters extends AppCompatActivity {

    RecyclerView recyclerView;
    PublicShelterAdapter adapter;
    List<PublicShelter> fullList = new ArrayList<>();
    List<PublicShelter> filteredList = new ArrayList<>();

    TabLayout tabLayout;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_public_shelters);

        recyclerView = findViewById(R.id.recyclerView);
        tabLayout = findViewById(R.id.tabLayout);

        recyclerView.setLayoutManager(new LinearLayoutManager(this));

        adapter = new PublicShelterAdapter(filteredList);
        recyclerView.setAdapter(adapter);

        setupTabs();
        loadShelters();
    }

    // =========================
    // LOAD DATA FROM SERVER
    // =========================
    private void loadShelters() {

        new Thread(() -> {

            try {
                URL url = new URL("http://10.0.2.2/crises_api/get_shelters.php");
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("GET");
                conn.setConnectTimeout(10000);
                conn.setReadTimeout(10000);

                BufferedReader br = new BufferedReader(
                        new InputStreamReader(conn.getInputStream())
                );

                StringBuilder sb = new StringBuilder();
                String line;

                while ((line = br.readLine()) != null) {
                    sb.append(line);
                }

                JSONArray array = new JSONArray(sb.toString());

                fullList.clear();

                for (int i = 0; i < array.length(); i++) {

                    JSONObject obj = array.getJSONObject(i);

                    fullList.add(new PublicShelter(
                            obj.optString("shelter_name"),
                            obj.optString("location"),
                            obj.optString("status"),
                            obj.optInt("available")
                    ));
                }

                runOnUiThread(() -> {
                    filteredList.clear();
                    filteredList.addAll(fullList);
                    adapter.notifyDataSetChanged();
                });

            } catch (Exception e) {
                Log.e("SHELTER_ERROR", e.toString());
            }
        }).start();
    }

    // =========================
    // TAB FILTER SYSTEM
    // =========================
    private void setupTabs() {

        tabLayout.addTab(tabLayout.newTab().setText("ALL"));
        tabLayout.addTab(tabLayout.newTab().setText("OPEN"));
        tabLayout.addTab(tabLayout.newTab().setText("NEAR-FULL"));
        tabLayout.addTab(tabLayout.newTab().setText("FULL"));

        tabLayout.addOnTabSelectedListener(new TabLayout.OnTabSelectedListener() {

            @Override
            public void onTabSelected(TabLayout.Tab tab) {

                String selected = tab.getText().toString().toLowerCase();

                filteredList.clear();

                for (PublicShelter s : fullList) {

                    String status = s.getStatus().toLowerCase().trim();

                    if (selected.equals("all")) {
                        filteredList.add(s);
                    }

                    else if (selected.equals("open") && status.equals("open")) {
                        filteredList.add(s);
                    }

                    else if (selected.equals("near-full")
                            && (status.contains("near") || status.contains("limited"))) {
                        filteredList.add(s);
                    }

                    else if (selected.equals("full") && status.equals("full")) {
                        filteredList.add(s);
                    }
                }

                adapter.notifyDataSetChanged();
            }

            @Override
            public void onTabUnselected(TabLayout.Tab tab) {}

            @Override
            public void onTabReselected(TabLayout.Tab tab) {}
        });
    }
}