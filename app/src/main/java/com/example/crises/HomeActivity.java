package com.example.crises;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.net.Uri;
import android.os.Bundle;
import android.widget.ImageButton;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;
import androidx.core.content.FileProvider;

import com.google.android.material.bottomnavigation.BottomNavigationView;

import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStream;

public class HomeActivity extends BaseActivity {

    String username;
    CardView quickCall;

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
        if (!checkProfileCompletion()) return;
        setContentView(R.layout.activity_home);

        // 🔥 GET USERNAME (SAFE METHOD)
        username = getIntent().getStringExtra("username");

        if (username == null || username.isEmpty()) {
            SharedPreferences sp = getSharedPreferences("user", MODE_PRIVATE);
            username = sp.getString("username", "");
        }

        if (!username.isEmpty()) {
            SharedPreferences sp = getSharedPreferences("user", MODE_PRIVATE);
            sp.edit().putString("username", username).apply();
        }

        initQuickCall();
        initTopAppBar();
        initGuidesSection();
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

    // ---------------- GUIDE PDF ----------------
    private void initGuidesSection() {

        CardView guideCard = findViewById(R.id.guideCard);
        if (guideCard == null) return;

        guideCard.setOnClickListener(v -> {

            try {
                File file = new File(getCacheDir(), "Emergency.pdf");

                if (!file.exists()) {
                    InputStream is = getAssets().open("Emergency.pdf");
                    FileOutputStream os = new FileOutputStream(file);

                    byte[] buffer = new byte[1024];
                    int length;

                    while ((length = is.read(buffer)) > 0) {
                        os.write(buffer, 0, length);
                    }

                    os.close();
                    is.close();
                }

                Uri uri = FileProvider.getUriForFile(
                        this,
                        getPackageName() + ".provider",
                        file
                );

                Intent intent = new Intent(Intent.ACTION_VIEW);
                intent.setDataAndType(uri, "application/pdf");
                intent.addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION);
                startActivity(intent);

            } catch (Exception e) {
                Toast.makeText(this, "Error opening PDF", Toast.LENGTH_SHORT).show();
            }
        });
    }

    // ---------------- NEWS ----------------
    private void initNewsSection() {

        CardView newsCard = findViewById(R.id.newsCard);
        if (newsCard != null) {
            newsCard.setOnClickListener(v ->
                    startActivity(new Intent(this, News.class)));
        }
    }

    // ---------------- QUICK CALL ----------------
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
                            case 0: number = "140"; break;
                            case 1: number = "112"; break;
                            case 2: number = "175"; break;
                            default: number = "112";
                        }

                        Intent intent = new Intent(Intent.ACTION_DIAL);
                        intent.setData(Uri.parse("tel:" + number));
                        startActivity(intent);

                    })
                    .show();
        });
    }

    // ---------------- BOTTOM NAV ----------------
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
                intent = new Intent(this, Map.class);

            } else if (id == R.id.nav_service) {
                intent = new Intent(this, Services.class);

            } else if (id == R.id.nav_profile) {

                intent = new Intent(HomeActivity.this, Account.class);

                // 🔥 SAFE USERNAME PASSING
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
}