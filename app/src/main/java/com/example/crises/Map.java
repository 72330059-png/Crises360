package com.example.crises;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.view.View;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.google.android.material.bottomnavigation.BottomNavigationView;

public class Map extends AppCompatActivity {
    @Override
    protected void attachBaseContext(Context newBase) {

        SharedPreferences prefs = newBase.getSharedPreferences("settings", MODE_PRIVATE);
        String lang = prefs.getString("lang", "en");

        super.attachBaseContext(LocaleHelper.setLocale(newBase, lang));
    }
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // 1. Enable EdgeToEdge
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_map);

        // 2. Safely apply window insets to prevent the NullPointerException crash
        View mainView = findViewById(R.id.main);
        if (mainView != null) {
            ViewCompat.setOnApplyWindowInsetsListener(mainView, (v, insets) -> {
                Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
                v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
                return insets;
            });
        }

        // 3. Setup Bottom Navigation
        initBottomNavigation();
    }

    private void initBottomNavigation() {
        BottomNavigationView bottomNav = findViewById(R.id.bottomNavigation);

        // Ensure the Map icon is highlighted
        bottomNav.setSelectedItemId(R.id.nav_map);

        bottomNav.setOnItemSelectedListener(item -> {
            int id = item.getItemId();

            // If user clicks Map while already on Map, do nothing
            if (id == R.id.nav_map) return true;

            Intent intent = null;
            if (id == R.id.nav_home) {
                intent = new Intent(this, HomeActivity.class);
            } else if (id == R.id.nav_alerts) {
                intent = new Intent(this, Alerts.class);
            } else if (id == R.id.nav_service) {
                intent = new Intent(this, Services.class);
            } else if (id == R.id.nav_profile) {
                intent = new Intent(this, Account.class);
            }

            if (intent != null) {
                // FLAG_ACTIVITY_REORDER_TO_FRONT prevents creating multiple copies of pages
                intent.addFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT);
                startActivity(intent);
                overridePendingTransition(0, 0); // Smooth "no-jump" transition
                return true;
            }
            return false;
        });
    }
}