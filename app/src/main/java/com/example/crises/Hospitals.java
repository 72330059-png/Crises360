package com.example.crises;

import android.os.Bundle;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import com.google.android.material.tabs.TabLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;

public class Hospitals extends AppCompatActivity {

    RecyclerView recyclerView;
    HospitalAdapter adapter;

    ArrayList<Hospital> hospitalList = new ArrayList<>();
    ArrayList<Hospital> filteredList = new ArrayList<>();

    TabLayout statusTabLayout;

    // Updated to match your exact status categories
    String[] statuses = {"All", "Safe", "Warning", "Dangerous"};

    RequestQueue queue;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_hospitals);

        recyclerView = findViewById(R.id.hospitalRecycler);
        statusTabLayout = findViewById(R.id.statusTabLayout);

        recyclerView.setLayoutManager(new LinearLayoutManager(this));

        // Connect the adapter directly to filteredList on startup
        adapter = new HospitalAdapter(filteredList);
        recyclerView.setAdapter(adapter);

        queue = Volley.newRequestQueue(this);
        findViewById(R.id.btnBack).setOnClickListener(v -> finish());
        setupTabs();
        loadHospitals();
    }

    // ---------------- LOAD FROM PHP ----------------
    private void loadHospitals() {
        String url = "http://192.168.0.106/crises_api/get_hospitals.php";

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.GET,
                url,
                null,
                response -> {
                    try {
                        hospitalList.clear();
                        JSONArray arr = response.getJSONArray("data");

                        for (int i = 0; i < arr.length(); i++) {
                            JSONObject obj = arr.getJSONObject(i);

                            String name = obj.getString("name");
                            String location = obj.getString("location");
                            String region = location;

                            int total = Integer.parseInt(obj.getString("total_beds"));
                            int available = Integer.parseInt(obj.getString("available_beds"));
                            int occupied = Integer.parseInt(obj.getString("occupied_beds"));
                            String status = obj.getString("hospital_status"); // pulling status value

                            hospitalList.add(new Hospital(
                                    name,
                                    location,
                                    region,
                                    total,
                                    available,
                                    occupied,
                                    status
                            ));
                        }

                        // Run default list filter configuration (displays "All" by default)
                        filterByStatus(statusTabLayout.getSelectedTabPosition());

                    } catch (Exception e) {
                        Toast.makeText(this, "Parse Error: " + e.getMessage(), Toast.LENGTH_LONG).show();
                    }
                },
                error -> Toast.makeText(this, "Network Error: " + error.getMessage(), Toast.LENGTH_LONG).show()
        );

        queue.add(request);
    }

    // ---------------- TAB LOGIC & FILTERING ----------------
    private void setupTabs() {
        // Build tab elements dynamically out of the string array
        for (String status : statuses) {
            statusTabLayout.addTab(statusTabLayout.newTab().setText(status));
        }

        statusTabLayout.addOnTabSelectedListener(new TabLayout.OnTabSelectedListener() {
            @Override
            public void onTabSelected(TabLayout.Tab tab) {
                filterByStatus(tab.getPosition());
            }

            @Override
            public void onTabUnselected(TabLayout.Tab tab) {}

            @Override
            public void onTabReselected(TabLayout.Tab tab) {}
        });
    }

    private void filterByStatus(int position) {
        String selectedStatus = statuses[position];
        filteredList.clear();

        if (selectedStatus.equalsIgnoreCase("All")) {
            filteredList.addAll(hospitalList);
        } else {
            for (Hospital h : hospitalList) {
                // Ensure h.getStatus() matches the string values stored inside your DB
                if (h.getStatus() != null && h.getStatus().equalsIgnoreCase(selectedStatus)) {
                    filteredList.add(h);
                }
            }
        }

        // Notify adapter to draw the filtered results safely
        adapter.notifyDataSetChanged();
    }
}