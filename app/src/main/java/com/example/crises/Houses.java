package com.example.crises;

import android.os.Bundle;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;

public class Houses extends AppCompatActivity {

    RecyclerView recyclerView;
    ArrayList<House> houseList;
    HouseAdapter adapter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_houses);

        recyclerView = findViewById(R.id.recyclerHouses);
        recyclerView.setLayoutManager(new LinearLayoutManager(this));

        houseList = new ArrayList<>();

        // 🏡 Sample Data (Lebanon cities)
        houseList.add(new House("Beirut", "2 Bedrooms • Sea view", "$800/month", "03123456"));
        houseList.add(new House("Tripoli", "3 Bedrooms • Spacious apartment", "$600/month", "70111222"));
        houseList.add(new House("Saida", "Studio • Near center", "$450/month", "76123456"));
        houseList.add(new House("Zahle", "Villa • Quiet area", "$1000/month", "71123456"));

        adapter = new HouseAdapter(this, houseList);
        recyclerView.setAdapter(adapter);
    }
}