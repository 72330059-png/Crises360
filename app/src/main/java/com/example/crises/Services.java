package com.example.crises;

import android.app.AlertDialog;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;


import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;

import com.google.android.material.bottomnavigation.BottomNavigationView;

public class Services extends AppCompatActivity {

    CardView quickCall;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_services);
        quickCall = findViewById(R.id.btnQuickCall);

        quickCall.setOnClickListener(v -> {

            String[] options = {"🚑 Ambulance", "🚓 Police", "🚒 Fire Brigade"};

            AlertDialog.Builder builder = new AlertDialog.Builder(this);
            builder.setTitle("Choose Emergency Service");

            builder.setItems(options, (dialog, which) -> {

                String number = "112"; // default emergency number

                Intent intent = new Intent(Intent.ACTION_DIAL);
                intent.setData(Uri.parse("tel:" + number));
                startActivity(intent);

            });

            builder.show();
        });
        BottomNavigationView bottomNav = findViewById(R.id.bottomNavigation);
        bottomNav.setSelectedItemId(R.id.nav_service);

        bottomNav.setOnItemSelectedListener(item -> {

            int id = item.getItemId();

            if (id == R.id.nav_home) {
                startActivity(new Intent(this, HomeActivity.class));
            } else if (id == R.id.nav_alerts) {
                startActivity(new Intent(this, Alerts.class));
            } else if (id == R.id.nav_map) {
                startActivity(new Intent(this, Map.class));
            } else if (id == R.id.nav_profile) {
                startActivity(new Intent(this, Account.class));
            } else {
                return true;
            }

            return true;
        });
    }
}