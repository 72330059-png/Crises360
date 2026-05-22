package com.example.crises;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.widget.ImageView;
import android.widget.Toast;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;

public class Settings extends AppCompatActivity {

    ImageView btnBack;

    SharedPreferences userPrefs;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_settings);

        // 🔹 LINK VIEWS
        btnBack = findViewById(R.id.btnBack);

        // 🔹 SHARED PREFERENCES
        userPrefs = getSharedPreferences("user", MODE_PRIVATE);

        // 🔹 BACK BUTTON
        btnBack.setOnClickListener(v -> finish());

        // 🔹 LOGOUT BUTTON
        findViewById(R.id.rowLogout).setOnClickListener(v -> {

            // Confirmation dialog (better UX)
            new AlertDialog.Builder(this)
                    .setTitle("Logout")
                    .setMessage("Are you sure you want to logout?")
                    .setPositiveButton("Yes", (dialog, which) -> {

                        // 1. Clear user session
                        SharedPreferences.Editor editor = userPrefs.edit();
                        editor.clear();
                        editor.apply();

                        // 2. Go to Login screen
                        Intent intent = new Intent(Settings.this, Login.class);

                        // 3. Clear back stack
                        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);

                        startActivity(intent);

                        // 4. Message
                        Toast.makeText(this, "Logged out successfully", Toast.LENGTH_SHORT).show();
                    })
                    .setNegativeButton("Cancel", null)
                    .show();
        });

        // 🔹 OPTIONAL: OTHER CLICK HANDLERS

        findViewById(R.id.rowAccount).setOnClickListener(v ->
                Toast.makeText(this, "Open Account Info", Toast.LENGTH_SHORT).show()
        );

        findViewById(R.id.rowLanguage).setOnClickListener(v ->
                Toast.makeText(this, "Change Language", Toast.LENGTH_SHORT).show()
        );

        findViewById(R.id.rowAbout).setOnClickListener(v ->
                Toast.makeText(this, "App Version 1.0.0", Toast.LENGTH_SHORT).show()
        );
    }
}