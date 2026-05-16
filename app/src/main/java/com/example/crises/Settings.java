package com.example.crises;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

public class Settings extends AppCompatActivity {

    TextView backBtn;
    TextView tvUsername, tvPassword;
    Button btnChange;

    SharedPreferences userPrefs;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_settings);

        // ---------------- INIT VIEWS ----------------
        backBtn = findViewById(R.id.backBtn);
        tvUsername = findViewById(R.id.tvUsername);
        tvPassword = findViewById(R.id.tvPassword);
        btnChange = findViewById(R.id.btnChange);

        // ---------------- LOAD USER DATA ----------------
        userPrefs = getSharedPreferences("user", MODE_PRIVATE);

        String username = userPrefs.getString("username", "N/A");
        String password = userPrefs.getString("password", "N/A");

        tvUsername.setText(username);
        tvPassword.setText(password);

        // ---------------- BACK BUTTON ----------------
        backBtn.setOnClickListener(v -> {
            finish(); // simple back
        });

        // ---------------- CHANGE BUTTON ----------------
        btnChange.setOnClickListener(v -> {

            Toast.makeText(this,
                    "Change feature will be added (update username/password)",
                    Toast.LENGTH_SHORT).show();

            // Later you will open edit screen:
            // startActivity(new Intent(this, EditAccount.class));
        });
    }
}