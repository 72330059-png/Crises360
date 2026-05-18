package com.example.crises;

import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.Spinner;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.android.volley.Request;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;

public class Needs extends AppCompatActivity {

    RecyclerView recyclerView;
    NeedsAdapter adapter;
    ArrayList<Need> list;
    Spinner filterLocation;

    String url = "http://10.0.2.2/crises_api/get_needs.php";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_needs);

        recyclerView = findViewById(R.id.recyclerNeeds);
        filterLocation = findViewById(R.id.filterLocation);

        list = new ArrayList<>();
        adapter = new NeedsAdapter(list);

        recyclerView.setLayoutManager(new LinearLayoutManager(this));
        recyclerView.setAdapter(adapter);

        setupSpinner();
        loadNeeds();
    }

    private void setupSpinner() {
        String[] locations = {"All", "Beirut", "Tripoli", "Saida"};

        ArrayAdapter<String> spinnerAdapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_spinner_dropdown_item,
                locations
        );

        filterLocation.setAdapter(spinnerAdapter);
    }

    private void loadNeeds() {

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.GET,
                url,
                null,

                response -> {
                    try {
                        list.clear();

                        if (response.getString("status").equals("success")) {

                            JSONArray data = response.getJSONArray("data");

                            for (int i = 0; i < data.length(); i++) {

                                JSONObject obj = data.getJSONObject(i);

                                String name = obj.getString("need_name"); // ✅ FIXED
                                String location = obj.getString("location");
                                String category = obj.getString("category");
                                String status = obj.getString("status");
                                String quantity = obj.getString("quantity");
                                String priority = obj.getString("priority");

                                list.add(new Need(
                                        name,
                                        location,
                                        category,
                                        status,
                                        quantity,
                                        priority
                                ));
                            }

                            adapter.notifyDataSetChanged();

                        } else {
                            Toast.makeText(this, "Server error", Toast.LENGTH_SHORT).show();
                        }

                    } catch (Exception e) {
                        Toast.makeText(this, "Parsing error: " + e.getMessage(),
                                Toast.LENGTH_LONG).show();
                    }
                },

                error -> Toast.makeText(this,
                        "Network error: " + error.toString(),
                        Toast.LENGTH_LONG).show()
        );

        Volley.newRequestQueue(this).add(request);
    }
}