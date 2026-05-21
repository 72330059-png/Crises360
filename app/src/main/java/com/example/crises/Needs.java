package com.example.crises;

import android.os.Bundle;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.android.volley.Request;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import com.google.android.material.tabs.TabLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;

public class Needs extends AppCompatActivity {

    RecyclerView recyclerView;
    NeedsAdapter adapter;

    ArrayList<Need> list;
    ArrayList<Need> fullList;

    TabLayout tabLayout;

    String url = "http://10.0.2.2/crises_api/get_needs.php";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_needs);

        recyclerView = findViewById(R.id.recyclerNeeds);
        tabLayout = findViewById(R.id.tabLayout);

        list = new ArrayList<>();
        fullList = new ArrayList<>();

        adapter = new NeedsAdapter(list);

        recyclerView.setLayoutManager(new LinearLayoutManager(this));
        recyclerView.setAdapter(adapter);

        setupTabs();
        setupTabListener();
        loadNeeds();
    }

    private void setupTabs() {

        String[] categories = {
                "All", "Food", "Water", "Medical",
                "Fuel", "Transport", "Clothes", "Other"
        };

        for (String c : categories) {
            tabLayout.addTab(tabLayout.newTab().setText(c));
        }
    }

    private void setupTabListener() {

        tabLayout.addOnTabSelectedListener(new TabLayout.OnTabSelectedListener() {
            @Override
            public void onTabSelected(TabLayout.Tab tab) {
                filterByCategory(tab.getText().toString());
            }

            @Override public void onTabUnselected(TabLayout.Tab tab) {}
            @Override public void onTabReselected(TabLayout.Tab tab) {}
        });
    }

    private void filterByCategory(String category) {

        list.clear();

        if (category.equalsIgnoreCase("all")) {
            list.addAll(fullList);
        } else {
            for (Need need : fullList) {
                if (need.getCategory() != null &&
                        need.getCategory().trim().equalsIgnoreCase(category.trim())) {
                    list.add(need);
                }
            }
        }

        adapter.notifyDataSetChanged();
    }

    private void loadNeeds() {

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.GET,
                url,
                null,

                response -> {
                    try {

                        list.clear();
                        fullList.clear();

                        if (response.getString("status").equals("success")) {

                            JSONArray data = response.getJSONArray("data");

                            for (int i = 0; i < data.length(); i++) {

                                JSONObject obj = data.getJSONObject(i);

                                Need need = new Need(
                                        obj.optString("resource_name"),
                                        obj.optString("category"),
                                        obj.optString("status"),
                                        obj.optString("location"),
                                        obj.optString("address"),
                                        obj.optString("contact_number"),
                                        obj.optString("opening_hours"),
                                        obj.optString("notes")
                                );

                                list.add(need);
                                fullList.add(need);
                            }

                            adapter.notifyDataSetChanged();
                        }

                    } catch (Exception e) {
                        e.printStackTrace();
                    }
                },

                error -> error.printStackTrace()
        );

        Volley.newRequestQueue(this).add(request);
    }
}