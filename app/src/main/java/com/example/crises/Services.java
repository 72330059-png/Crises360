package com.example.crises;

import android.app.AlertDialog;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.view.View;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;

import com.google.android.material.bottomnavigation.BottomNavigationView;

public class Services extends AppCompatActivity {

    CardView  sheltersCard, sosCard, cardHospitals, cardNeeds;
    LinearLayout shelterOptions;
    TextView houses, publicShelters;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_services);

        initHospitals();
        initSOS();
        initShelters();
        initBottomNav();
        initNeeds();
    }

    private void initSOS() {
        sosCard = findViewById(R.id.cardSOS);

        sosCard.setOnClickListener(v -> {
            Intent intent = new Intent(Services.this, SOSActivity.class);
            startActivity(intent);
        });
    }
    private void initNeeds() {
        cardNeeds = findViewById(R.id.cardNeeds);
        cardNeeds.setOnClickListener(v -> {
            Intent intent = new Intent(Services.this, Needs.class);
            startActivity(intent);
        });
    }

    private void initShelters() {

        sheltersCard = findViewById(R.id.cardShelters);
        shelterOptions = findViewById(R.id.shelterOptions);
        houses = findViewById(R.id.optionHouses);
        publicShelters = findViewById(R.id.optionPublicShelters);

        sheltersCard.setOnClickListener(v -> {
            shelterOptions.setVisibility(
                    shelterOptions.getVisibility() == View.VISIBLE
                            ? View.GONE
                            : View.VISIBLE
            );
        });

        houses.setOnClickListener(v ->
                startActivity(new Intent(this, Houses.class))
        );

        publicShelters.setOnClickListener(v ->
                startActivity(new Intent(this, PublicShelters.class))
        );
    }

    private void initHospitals() {

        cardHospitals = findViewById(R.id.cardHospitals);

        cardHospitals.setOnClickListener(v -> {
            Intent intent = new Intent(Services.this, Hospitals.class);
            startActivity(intent);
        });
    }
    private void initBottomNav() {

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
            }

            return true;
        });
    }
}