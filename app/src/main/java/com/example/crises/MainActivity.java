package com.example.crises;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.widget.ImageView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.engine.DiskCacheStrategy;

public class MainActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        ImageView bg = findViewById(R.id.backgroundImage);
        if (bg != null) {
            Glide.with(this)
                    .load(R.drawable.background)
                    .centerCrop()
                    .diskCacheStrategy(DiskCacheStrategy.ALL)
                    .into(bg);

            // DEV TOOL: long-press splash to reset session for testing
            bg.setOnLongClickListener(v -> {
                getSharedPreferences("user_session", MODE_PRIVATE)
                        .edit().clear().apply();
                Toast.makeText(this, "Session cleared", Toast.LENGTH_SHORT).show();
                Intent intent = new Intent(MainActivity.this, StartingActivity.class);
                intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                startActivity(intent);
                finish();
                return true;
            });
        }

        new Handler(Looper.getMainLooper()).postDelayed(() -> {

            SharedPreferences prefs = getSharedPreferences("user_session", MODE_PRIVATE);
            boolean isLoggedIn = prefs.getBoolean("isLoggedIn", false);

            Intent intent;

            if (isLoggedIn) {
                // ✅ Fully logged in (passed 2FA) → go straight to dashboard
                intent = new Intent(MainActivity.this, HomeActivity.class);
            } else {
                // ✅ Not logged in → show intro screen
                intent = new Intent(MainActivity.this, StartingActivity.class);
            }

            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
            startActivity(intent);
            finish();

        }, 2000);
    }
}