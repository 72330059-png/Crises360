package com.example.crises;

import android.content.Intent;
import android.os.Bundle;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;

import androidx.appcompat.app.AppCompatActivity;

import com.google.android.material.bottomnavigation.BottomNavigationView;

public class Map extends AppCompatActivity {

    WebView webView;
    BottomNavigationView bottomNavigation;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_map); // your XML file

        // 📍 WebView setup
        webView = findViewById(R.id.webview);

        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true);      // IMPORTANT for Leaflet
        settings.setDomStorageEnabled(true);      // important for map storage

        webView.setWebViewClient(new WebViewClient()); // open inside app

        // Load your map
        webView.loadUrl("file:///android_asset/map.html");
        initBottomNavigation();

    }
    private void initBottomNavigation() {
        BottomNavigationView bottomNav = findViewById(R.id.bottomNavigation);
        if (bottomNav == null) return;
        bottomNav.setSelectedItemId(R.id.nav_home);

        bottomNav.setOnItemSelectedListener(item -> {
            int id = item.getItemId();
            if (id == R.id.nav_home) return true;

            Intent intent = null;
            if (id == R.id.nav_alerts) intent = new Intent(this, Alerts.class);
            else if (id == R.id.nav_map) intent = new Intent(this, Map.class);
            else if (id == R.id.nav_service) intent = new Intent(this, Services.class);
            else if (id == R.id.nav_profile) intent = new Intent(this, Account.class);

            if (intent != null) {
                intent.addFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT);
                startActivity(intent);
                overridePendingTransition(0, 0);
                return true;
            }
            return false;
        });
    }
}