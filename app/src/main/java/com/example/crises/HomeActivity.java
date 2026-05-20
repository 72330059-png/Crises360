package com.example.crises;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.net.Uri;
import android.os.Bundle;
import android.widget.Button;
import android.widget.ImageButton;
import android.widget.LinearLayout;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AlertDialog;
import androidx.cardview.widget.CardView;

import com.google.android.material.bottomnavigation.BottomNavigationView;

public class HomeActivity extends BaseActivity {

    String username;
    Button quickCall;

    @Override
    protected void attachBaseContext(Context newBase) {
        SharedPreferences prefs = newBase.getSharedPreferences("settings", MODE_PRIVATE);
        String lang = prefs.getString("lang", "en");
        super.attachBaseContext(LocaleHelper.setLocale(newBase, lang));
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        // 🔐 SESSION CHECK
        SharedPreferences prefs = getSharedPreferences("user_session", MODE_PRIVATE);
        boolean isLoggedIn = prefs.getBoolean("isLoggedIn", false);

        if (!isLoggedIn) {
            startActivity(new Intent(this, Login.class));
            finish();
            return;
        }

        if (!checkProfileCompletion()) return;

        setContentView(R.layout.activity_home);

        // ✅ CLEAN USERNAME FETCH
        username = prefs.getString("username", "");

        initTopAppBar();
        initQuickActions();
        initQuickCall();
        initNewsSection();
        initBottomNavigation();
    }

    // ---------------- TOP BAR ----------------
    private void initTopAppBar() {

        ImageButton notificationBtn = findViewById(R.id.notificationBtn);
        ImageButton settingsBtn = findViewById(R.id.settingsBtn);

        if (notificationBtn != null) {
            notificationBtn.setOnClickListener(v ->
                    startActivity(new Intent(this, Tips.class)));
        }

        if (settingsBtn != null) {
            settingsBtn.setOnClickListener(v ->
                    startActivity(new Intent(this, Settings.class)));
        }
    }

    // ---------------- NEWS ----------------
    private void initNewsSection() {

        CardView newsCard = findViewById(R.id.newsCard);

        if (newsCard != null) {
            newsCard.setOnClickListener(v ->
                    startActivity(new Intent(this, News.class)));
        }
    }

    // ---------------- QUICK CALL (SOS) ----------------
    private void initQuickCall() {

        quickCall = findViewById(R.id.sosButton);

        quickCall.setOnClickListener(v -> {

            String[] options = {
                    "🚑 Ambulance (140)",
                    "🚓 Police (112)",
                    "🚒 Fire Brigade (175)"
            };

            String[] numbers = {"140", "112", "175"};

            new AlertDialog.Builder(this)
                    .setTitle("Choose Emergency Service")
                    .setItems(options, (dialog, which) -> {

                        String number = numbers[which];

                        Intent intent = new Intent(Intent.ACTION_DIAL);
                        intent.setData(Uri.parse("tel:" + number));
                        startActivity(intent);

                    })
                    .show();
        });
    }

    // ---------------- QUICK ACTIONS ----------------
    private void initQuickActions() {

        LinearLayout findShelter = findViewById(R.id.btnFindShelter);
        LinearLayout medicalHelp = findViewById(R.id.btnMedicalHelp);
        LinearLayout needs = findViewById(R.id.btnNeeds);

        if (findShelter != null) {
            findShelter.setOnClickListener(v ->
                    startActivity(new Intent(this, PublicShelters.class)));
        }

        if (medicalHelp != null) {
            medicalHelp.setOnClickListener(v ->
                    startActivity(new Intent(this, Hospitals.class)));
        }

        if (needs != null) {
            needs.setOnClickListener(v ->
                    startActivity(new Intent(this, Needs.class)));
        }
    }

    // ---------------- BOTTOM NAVIGATION ----------------
    private void initBottomNavigation() {

        BottomNavigationView bottomNav = findViewById(R.id.bottomNavigation);
        if (bottomNav == null) return;

        bottomNav.setSelectedItemId(R.id.nav_home);

        bottomNav.setOnItemSelectedListener(item -> {

            int id = item.getItemId();

            if (id == R.id.nav_home) return true;

            Intent intent = null;

            if (id == R.id.nav_alerts) {
                intent = new Intent(this, Alerts.class);

            } else if (id == R.id.nav_map) {
                intent = new Intent(this, MapActivity.class);

            } else if (id == R.id.nav_profile) {
                intent = new Intent(this, Account.class);
                intent.putExtra("username", username);
            }

            if (intent != null) {
                startActivity(intent);
                overridePendingTransition(0, 0);
                return true;
            }

            return false;
        });
    }

    // ---------------- LOGOUT METHOD ----------------
    public void logout() {

        SharedPreferences prefs = getSharedPreferences("user_session", MODE_PRIVATE);
        prefs.edit().clear().apply();

        startActivity(new Intent(this, StartingActivity.class));
        finish();
    }


}