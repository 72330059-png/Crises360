package com.example.crises;

import android.Manifest;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.widget.ImageView;
import android.widget.Toast;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.widget.SwitchCompat;
import androidx.core.content.ContextCompat;

public class Settings extends AppCompatActivity {

    ImageView btnBack;
    SharedPreferences prefs;
    SwitchCompat switchNotifications, switchLocation;

    ActivityResultLauncher<String> notificationPermLauncher;
    ActivityResultLauncher<String[]> locationPermLauncher;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_settings);

        prefs = getSharedPreferences("user_session", MODE_PRIVATE);

        btnBack             = findViewById(R.id.btnBack);
        switchNotifications = findViewById(R.id.switchNotifications);
        switchLocation      = findViewById(R.id.switchLocation);

        // Restore real permission states
        switchNotifications.setChecked(hasNotificationPermission());
        switchLocation.setChecked(hasLocationPermission());

        // ── PERMISSION LAUNCHERS ──────────────────────────────
        notificationPermLauncher = registerForActivityResult(
                new ActivityResultContracts.RequestPermission(),
                granted -> {
                    switchNotifications.setChecked(granted);
                    Toast.makeText(this,
                            granted ? "Notifications enabled ✓"
                                    : "Denied — enable in App Settings",
                            Toast.LENGTH_SHORT).show();
                    if (!granted) openSystemSettings();
                }
        );

        locationPermLauncher = registerForActivityResult(
                new ActivityResultContracts.RequestMultiplePermissions(),
                result -> {
                    boolean granted = Boolean.TRUE.equals(
                            result.get(Manifest.permission.ACCESS_FINE_LOCATION));
                    switchLocation.setChecked(granted);
                    Toast.makeText(this,
                            granted ? "Location enabled ✓"
                                    : "Denied — enable in App Settings",
                            Toast.LENGTH_SHORT).show();
                    if (!granted) openSystemSettings();
                }
        );

        // ── BACK ──────────────────────────────────────────────
        btnBack.setOnClickListener(v -> {
            startActivity(new Intent(this, HomeActivity.class));
            finish();
        });

        // ── ACCOUNT ───────────────────────────────────────────
        findViewById(R.id.rowAccount).setOnClickListener(v ->
                startActivity(new Intent(this, Account.class)));

        // ── NOTIFICATIONS ─────────────────────────────────────
        switchNotifications.setOnCheckedChangeListener((btn, isChecked) -> {
            if (isChecked) {
                if (hasNotificationPermission()) {
                    Toast.makeText(this, "Notifications already enabled ✓",
                            Toast.LENGTH_SHORT).show();
                } else if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
                    notificationPermLauncher.launch(Manifest.permission.POST_NOTIFICATIONS);
                } else {
                    Toast.makeText(this, "Notifications enabled ✓",
                            Toast.LENGTH_SHORT).show();
                }
            } else {
                switchNotifications.setChecked(true);
                new AlertDialog.Builder(this)
                        .setTitle("Disable Notifications")
                        .setMessage("To turn off notifications, disable them in App Settings.")
                        .setPositiveButton("Open App Settings", (d, w) -> openSystemSettings())
                        .setNegativeButton("Cancel", null)
                        .show();
            }
        });

        // ── LOCATION ──────────────────────────────────────────
        switchLocation.setOnCheckedChangeListener((btn, isChecked) -> {
            if (isChecked) {
                if (hasLocationPermission()) {
                    Toast.makeText(this, "Location already enabled ✓",
                            Toast.LENGTH_SHORT).show();
                } else {
                    locationPermLauncher.launch(new String[]{
                            Manifest.permission.ACCESS_FINE_LOCATION,
                            Manifest.permission.ACCESS_COARSE_LOCATION
                    });
                }
            } else {
                switchLocation.setChecked(true);
                new AlertDialog.Builder(this)
                        .setTitle("Disable Location")
                        .setMessage("To turn off location, disable it in App Settings.")
                        .setPositiveButton("Open App Settings", (d, w) -> openSystemSettings())
                        .setNegativeButton("Cancel", null)
                        .show();
            }
        });

        // ── ABOUT ─────────────────────────────────────────────
        findViewById(R.id.rowAbout).setOnClickListener(v ->
                new AlertDialog.Builder(this)
                        .setTitle("About Crises360")
                        .setMessage("Crises360\nVersion 1.0.0\n\n" +
                                "A disaster management and emergency system " +
                                "designed to keep citizens safe and connected " +
                                "during crises.\n\n© 2026 Crises360.")
                        .setPositiveButton("OK", null)
                        .show()
        );

        // ── LOGOUT ────────────────────────────────────────────
        findViewById(R.id.rowLogout).setOnClickListener(v ->
                new AlertDialog.Builder(this)
                        .setTitle("Logout")
                        .setMessage("Are you sure you want to logout?")
                        .setPositiveButton("Yes", (dialog, which) -> {
                            prefs.edit().clear().apply();
                            Toast.makeText(this, "Logged out successfully",
                                    Toast.LENGTH_SHORT).show();
                            Intent intent = new Intent(Settings.this, StartingActivity.class);
                            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK
                                    | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                            startActivity(intent);
                        })
                        .setNegativeButton("Cancel", null)
                        .show()
        );
    }

    // ── HELPERS ───────────────────────────────────────────────
    private boolean hasNotificationPermission() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            return ContextCompat.checkSelfPermission(this,
                    Manifest.permission.POST_NOTIFICATIONS)
                    == PackageManager.PERMISSION_GRANTED;
        }
        return true;
    }

    private boolean hasLocationPermission() {
        return ContextCompat.checkSelfPermission(this,
                Manifest.permission.ACCESS_FINE_LOCATION)
                == PackageManager.PERMISSION_GRANTED;
    }

    private void openSystemSettings() {
        Intent intent = new Intent(
                android.provider.Settings.ACTION_APPLICATION_DETAILS_SETTINGS);
        intent.setData(Uri.fromParts("package", getPackageName(), null));
        startActivity(intent);
    }
}