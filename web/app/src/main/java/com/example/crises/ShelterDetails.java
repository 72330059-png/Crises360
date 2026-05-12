package com.example.crises;

import android.content.Intent;
import android.os.Bundle;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

public class ShelterDetails extends AppCompatActivity {

    TextView name, type, status, address, phone;
    TextView capacity, current, emptyRooms;
    TextView food, water, electricity, medical;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_shelter_details);

        name = findViewById(R.id.name);
        type = findViewById(R.id.type);
        status = findViewById(R.id.status);

        address = findViewById(R.id.address);
        phone = findViewById(R.id.phone);

        capacity = findViewById(R.id.capacity);
        current = findViewById(R.id.current);
        emptyRooms = findViewById(R.id.emptyRooms);

        food = findViewById(R.id.food);
        water = findViewById(R.id.water);
        electricity = findViewById(R.id.electricity);
        medical = findViewById(R.id.medical);


        Intent i = getIntent();

        name.setText(i.getStringExtra("name"));
        type.setText(i.getStringExtra("type"));
        status.setText(i.getStringExtra("status"));

        address.setText("🏠 " + i.getStringExtra("address"));
        phone.setText("📞 " + i.getStringExtra("phone"));

        int cap = i.getIntExtra("capacity", 0);
        int cur = i.getIntExtra("current", 0);
        int empty = i.getIntExtra("empty", 0);

        capacity.setText("Total Capacity: " + cap);
        current.setText("Current People: " + cur);
        emptyRooms.setText("Empty Rooms: " + empty);


        food.setText(i.getBooleanExtra("food", false) ? "🍞 Food: Available" : "🍞 Food: Not available");
        water.setText(i.getBooleanExtra("water", false) ? "💧 Water: Available" : "💧 Not available");
        electricity.setText(i.getBooleanExtra("electricity", false) ? "⚡ Electricity: Available" : "⚡ Not available");
        medical.setText(i.getBooleanExtra("medical", false) ? "🏥 Medical: Available" : "🏥 Not available");
    }
}