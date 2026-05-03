package com.example.crises;

import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.Spinner;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;

public class Needs extends AppCompatActivity {

    RecyclerView recyclerView;
    NeedsAdapter adapter;
    ArrayList<Need> list;

    Spinner locationFilter;

    ArrayList<Need> fullList = new ArrayList<>();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_needs);

        recyclerView = findViewById(R.id.recyclerNeeds);
        locationFilter = findViewById(R.id.filterLocation);

        recyclerView.setLayoutManager(new LinearLayoutManager(this));

        list = new ArrayList<>();


        fullList.add(new Need("Carrefour", "Hamra", "Supermarket", "OPEN", "Available"));
        fullList.add(new Need("City Pharmacy", "Downtown", "Pharmacy", "OPEN", "Available"));
        fullList.add(new Need("Total Fuel", "Airport Road", "Fuel", "OPEN", "Limited"));
        fullList.add(new Need("Spinneys", "Hamra", "Supermarket", "OPEN", "Available"));

        list.addAll(fullList);

        adapter = new NeedsAdapter(list);
        recyclerView.setAdapter(adapter);

        String[] locations = {
                "All Locations",
                "Hamra",
                "Downtown",
                "Airport Road"
        };

        locationFilter.setAdapter(new ArrayAdapter<>(
                this,
                android.R.layout.simple_spinner_dropdown_item,
                locations
        ));

        locationFilter.setOnItemSelectedListener(new android.widget.AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(android.widget.AdapterView<?> parent, android.view.View view, int position, long id) {

                String selected = locations[position];

                filterByLocation(selected);
            }

            @Override
            public void onNothingSelected(android.widget.AdapterView<?> parent) {}
        });
    }

    private void filterByLocation(String location) {

        list.clear();

        if (location.equals("All Locations")) {
            list.addAll(fullList);
        } else {
            for (Need n : fullList) {
                if (n.getLocation().equalsIgnoreCase(location)) {
                    list.add(n);
                }
            }
        }

        adapter.notifyDataSetChanged();
    }
}