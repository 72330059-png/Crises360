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

    // Tab label → DB category value(s) it matches
    // Tab label is what user sees, dbValue is what we compare against DB
    private static final String[][] TABS = {
            {"All",         "all"},
            {"Food",        "food,bakery,restaurant"},
            {"Water",       "water,water_station"},
            {"Medical",     "medical,pharmacy,hospital"},
            {"Fuel",        "fuel,fuel_station"},
            {"Transport",   "transport"},
            {"Clothes",     "clothes"},
            {"Other",       "other"}
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_needs);

        recyclerView = findViewById(R.id.recyclerNeeds);
        tabLayout    = findViewById(R.id.tabLayout);

        list     = new ArrayList<>();
        fullList = new ArrayList<>();
        adapter  = new NeedsAdapter(list);

        recyclerView.setLayoutManager(new LinearLayoutManager(this));
        recyclerView.setAdapter(adapter);

        setupTabs();
        setupTabListener();
        loadNeeds();
    }

    private void setupTabs() {
        for (String[] tab : TABS) {
            tabLayout.addTab(tabLayout.newTab().setText(tab[0]));
        }
    }

    private void setupTabListener() {
        tabLayout.addOnTabSelectedListener(new TabLayout.OnTabSelectedListener() {
            @Override
            public void onTabSelected(TabLayout.Tab tab) {
                int pos = tab.getPosition();
                String dbValues = TABS[pos][1];
                filterByCategory(dbValues);
            }
            @Override public void onTabUnselected(TabLayout.Tab tab) {}
            @Override public void onTabReselected(TabLayout.Tab tab) {}
        });
    }

    private void filterByCategory(String dbValues) {
        list.clear();
        if (dbValues.equals("all")) {
            list.addAll(fullList);
        } else {
            // dbValues can be comma-separated e.g. "food,bakery,restaurant"
            String[] values = dbValues.split(",");
            for (Need need : fullList) {
                if (need.getCategory() != null) {
                    for (String val : values) {
                        if (need.getCategory().trim()
                                .equalsIgnoreCase(val.trim())) {
                            list.add(need);
                            break;
                        }
                    }
                }
            }
        }
        adapter.notifyDataSetChanged();
    }

    private void loadNeeds() {
        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.GET, url, null,
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