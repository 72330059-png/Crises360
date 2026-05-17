package com.example.crises;

import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.AdapterView;
import android.widget.Spinner;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;

public class Hospitals extends AppCompatActivity {

    RecyclerView recyclerView;
    HospitalAdapter adapter;

    ArrayList<Hospital> hospitalList = new ArrayList<>();
    ArrayList<Hospital> filteredList = new ArrayList<>();

    Spinner regionSpinner;

    String[] regions = {"All", "Achrafieh", "Tripoli", "Byblos", "bntjbeil", "hhhh", "mmmm"};

    RequestQueue queue;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_hospitals);

        recyclerView = findViewById(R.id.hospitalRecycler);
        regionSpinner = findViewById(R.id.regionSpinner);

        recyclerView.setLayoutManager(new LinearLayoutManager(this));

        queue = Volley.newRequestQueue(this);

        setupSpinner();
        loadHospitals();
    }

    // ---------------- LOAD FROM PHP ----------------
    private void loadHospitals() {

        String url = "http://10.0.2.2/crises_api/get_hospitals.php";

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

                            String region = location; // or real region column if you have

                            int total = Integer.parseInt(obj.getString("total_beds"));
                            int available = Integer.parseInt(obj.getString("available_beds"));
                            int occupied = Integer.parseInt(obj.getString("occupied_beds"));
                            String status = obj.getString("hospital_status");

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

                        adapter = new HospitalAdapter(hospitalList);
                        recyclerView.setAdapter(adapter);

                    } catch (Exception e) {
                        Toast.makeText(this, "Parse Error: " + e.getMessage(), Toast.LENGTH_LONG).show();
                    }

                },
                error -> Toast.makeText(this, "Network Error: " + error.getMessage(), Toast.LENGTH_LONG).show()
        );

        queue.add(request);
    }

    // ---------------- FILTER ----------------
    private void setupSpinner() {

        ArrayAdapter<String> spinnerAdapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_spinner_dropdown_item,
                regions
        );

        regionSpinner.setAdapter(spinnerAdapter);

        regionSpinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {

            @Override
            public void onItemSelected(AdapterView<?> parent, android.view.View view, int position, long id) {

                String selected = regions[position];

                filteredList.clear();

                if (selected.equals("All")) {
                    filteredList.addAll(hospitalList);
                } else {
                    for (Hospital h : hospitalList) {
                        if (h.getLocation().equalsIgnoreCase(selected)) {
                            filteredList.add(h);
                        }
                    }
                }

                adapter = new HospitalAdapter(filteredList);
                recyclerView.setAdapter(adapter);
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
        });
    }
}