package com.example.crises;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.widget.ImageView;

import androidx.appcompat.app.AppCompatActivity;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.engine.DiskCacheStrategy;

public class MainActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
    }

    @Override
    protected void onPostResume() {
        super.onPostResume();

        // 🎨 Load background image
        ImageView bg = findViewById(R.id.backgroundImage);
        if (bg != null) {
            Glide.with(this)
                    .load(R.drawable.background)
                    .centerCrop()
                    .diskCacheStrategy(DiskCacheStrategy.ALL)
                    .into(bg);
        }

        // ⏳ Splash delay (2 seconds)
        new Handler(Looper.getMainLooper()).postDelayed(() -> {
            if (!isFinishing()) {

                // 🔐 SESSION CHECK
                SharedPreferences prefs = getSharedPreferences("user_session", MODE_PRIVATE);

                boolean isLoggedIn = prefs.getBoolean("isLoggedIn", false);
                boolean isProfileComplete = prefs.getBoolean("isProfileComplete", false);

                Intent intent;

                if (!isLoggedIn) {
                    // ❌ First time user
                    intent = new Intent(MainActivity.this, StartingActivity.class);

                } else if (!isProfileComplete) {
                    // ⚠️ Logged in but profile not completed
                    intent = new Intent(MainActivity.this, Account.class);

                } else {
                    // ✅ User already logged in → go directly Home
                    intent = new Intent(MainActivity.this, HomeActivity.class);
                }

                startActivity(intent);
                finish();
            }
        }, 2000);
    }
}