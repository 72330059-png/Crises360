package com.example.crises;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.net.Uri;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AlertDialog;
import androidx.cardview.widget.CardView;

import com.google.android.material.bottomnavigation.BottomNavigationView;

public class HomeActivity extends BaseActivity {

    SharedPreferences prefs;
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

        // ✅ SESSION CHECK — if not logged in go to login
        prefs = getSharedPreferences("user_session", MODE_PRIVATE);
        if (!prefs.getBoolean("isLoggedIn", false)) {
            startActivity(new Intent(this, Login.class)
                    .setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK));
            finish();
            return;
        }

        setContentView(R.layout.activity_home);

        initTopAppBar();
        initProfileBanner();
        initQuickActions();
        initQuickCall();
        initNewsSection();
        initBottomNavigation();
    }

    // ── TOP BAR ───────────────────────────────────────────────
    private void initTopAppBar() {
        // ✅ Show full_name instead of username
        TextView tvHello = findViewById(R.id.appBrandName);
        if (tvHello != null) {
            String name = prefs.getString("full_name", "");
            tvHello.setText(name.isEmpty() ? "Hello!" : "Hello, " + name.split(" ")[0]);
        }

        ImageButton notificationBtn = findViewById(R.id.notificationBtn);
        ImageButton settingsBtn     = findViewById(R.id.settingsBtn);

        if (notificationBtn != null) {
            notificationBtn.setOnClickListener(v ->
                    startActivity(new Intent(this, Tips.class)));
        }

        if (settingsBtn != null) {
            settingsBtn.setOnClickListener(v -> {
                try {
                    startActivity(new Intent(this, Settings.class));
                } catch (Exception e) {
                    android.widget.Toast.makeText(this,
                            "Settings not found!", android.widget.Toast.LENGTH_SHORT).show();
                }
            });
        }
    }

    // ── PROFILE COMPLETION BANNER ─────────────────────────────
    // ✅ Shows a yellow banner for new users who haven't filled their profile
    private void initProfileBanner() {
        View banner = findViewById(R.id.profileBanner);
        if (banner == null) return; // banner not in XML yet — safe to skip

        boolean profileComplete = prefs.getBoolean("isProfileComplete", false);

        if (!profileComplete) {
            banner.setVisibility(View.VISIBLE);
            Button btnComplete = findViewById(R.id.btnCompleteProfile);
            if (btnComplete != null) {
                btnComplete.setOnClickListener(v ->
                        startActivity(new Intent(this, Account.class)));
            }
        } else {
            banner.setVisibility(View.GONE);
        }
    }

    // ── NEWS ──────────────────────────────────────────────────
    private void initNewsSection() {
        CardView newsCard = findViewById(R.id.newsCard);
        if (newsCard != null) {
            newsCard.setOnClickListener(v ->
                    startActivity(new Intent(this, News.class)));
        }
    }

    // ── SOS BUTTON ────────────────────────────────────────────
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
                        Intent intent = new Intent(Intent.ACTION_DIAL);
                        intent.setData(Uri.parse("tel:" + numbers[which]));
                        startActivity(intent);
                    })
                    .show();
        });
    }

    // ── QUICK ACTIONS ─────────────────────────────────────────
    private void initQuickActions() {
        LinearLayout findShelter = findViewById(R.id.btnFindShelter);
        LinearLayout medicalHelp = findViewById(R.id.btnMedicalHelp);
        LinearLayout needs       = findViewById(R.id.btnNeeds);

        if (findShelter != null)
            findShelter.setOnClickListener(v ->
                    startActivity(new Intent(this, PublicShelters.class)));

        if (medicalHelp != null)
            medicalHelp.setOnClickListener(v ->
                    startActivity(new Intent(this, Hospitals.class)));

        if (needs != null)
            needs.setOnClickListener(v ->
                    startActivity(new Intent(this, Needs.class)));
    }

    // ── BOTTOM NAV ────────────────────────────────────────────
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
            }

            if (intent != null) {
                startActivity(intent);
                overridePendingTransition(0, 0);
                return true;
            }
            return false;
        });
    }

    // ── LOGOUT ────────────────────────────────────────────────
    public void logout() {
        new AlertDialog.Builder(this)
                .setTitle("Log Out")
                .setMessage("Are you sure you want to log out?")
                .setPositiveButton("Log Out", (d, w) -> {
                    prefs.edit().clear().apply();
                    Intent intent = new Intent(this, StartingActivity.class);
                    intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK
                            | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                    startActivity(intent);
                    finish();
                })
                .setNegativeButton("Cancel", null)
                .show();
    }
}