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

    CardView quickCall, sheltersCard, sosCard;
    LinearLayout shelterOptions;
    TextView houses, schools;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_services);

        initSOS();
        initQuickCall();
        initShelters();
        initBottomNav();
    }

    private void initSOS() {
        sosCard = findViewById(R.id.cardSOS);

        sosCard.setOnClickListener(v -> {
            // OPEN NEW PAGE ONLY
            Intent intent = new Intent(Services.this, SOSActivity.class);
            startActivity(intent);
        });
    }
    private void initQuickCall() {

        quickCall = findViewById(R.id.btnQuickCall);

        quickCall.setOnClickListener(v -> {

            String[] options = {
                    "🚑 Ambulance (140)",
                    "🚓 Police (112)",
                    "🚒 Fire Brigade (175)"
            };

            new AlertDialog.Builder(this)
                    .setTitle("Choose Emergency Service")
                    .setItems(options, (dialog, which) -> {

                        String number;

                        switch (which) {
                            case 0:
                                number = "140";
                                break;
                            case 1:
                                number = "112";
                                break;
                            case 2:
                                number = "175";
                                break;
                            default:
                                number = "112";
                        }

                        Intent intent = new Intent(Intent.ACTION_DIAL);
                        intent.setData(Uri.parse("tel:" + number));
                        startActivity(intent);

                    })
                    .show();
        });
    }

    private void initShelters() {

        sheltersCard = findViewById(R.id.cardShelters);
        shelterOptions = findViewById(R.id.shelterOptions);
        houses = findViewById(R.id.optionHouses);
        schools = findViewById(R.id.optionSchools);

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

        schools.setOnClickListener(v ->
                startActivity(new Intent(this, Schools.class))
        );
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