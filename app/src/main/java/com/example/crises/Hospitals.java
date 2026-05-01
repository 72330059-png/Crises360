package com.example.crises;

import android.os.Bundle;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Spinner;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;

public class Hospitals extends AppCompatActivity {

    RecyclerView recyclerView;
    HospitalAdapter adapter;
    ArrayList<Hospital> hospitalList;

    Spinner regionSpinner;

    String[] regions = {"All", "Beirut", "Dahieh", "North", "South"};

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_hospitals);

        recyclerView = findViewById(R.id.hospitalRecycler);
        regionSpinner = findViewById(R.id.regionSpinner);

        recyclerView.setLayoutManager(new LinearLayoutManager(this));

        hospitalList = new ArrayList<>();

        // SAMPLE DATA
        hospitalList.add(new Hospital("AUBMC", "Hamra", "Beirut", 200, 50, 150, "Available"));
        hospitalList.add(new Hospital("Rafik Hariri", "Airport Road", "Beirut", 300, 10, 290, "Critical"));
        hospitalList.add(new Hospital("Sahel Hospital", "Haret Hreik", "Dahieh", 180, 0, 180, "Full"));

        adapter = new HospitalAdapter(hospitalList);
        recyclerView.setAdapter(adapter);

        setupSpinner();
    }

    private void setupSpinner() {

        ArrayAdapter<String> spinnerAdapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_spinner_dropdown_item,
                regions
        );

        regionSpinner.setAdapter(spinnerAdapter);

        regionSpinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {

                String selectedRegion = regions[position];

                if (selectedRegion.equals("All")) {
                    adapter = new HospitalAdapter(hospitalList);
                    recyclerView.setAdapter(adapter);
                    return;
                }

                ArrayList<Hospital> filteredList = new ArrayList<>();

                for (Hospital h : hospitalList) {
                    if (h.getRegion().equalsIgnoreCase(selectedRegion)) {
                        filteredList.add(h);
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