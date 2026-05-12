package com.example.crises;

import android.content.Intent;
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
        setContentView(R.layout.activity_main);}

    @Override
    protected void onPostResume() {
        super.onPostResume();
        ImageView bg = findViewById(R.id.backgroundImage);
        if (bg != null) {
            Glide.with(this)
                    .load(R.drawable.logo2)
                    .override(800, 800)
                    .into(bg);
        }

        new Handler(Looper.getMainLooper()).postDelayed(() -> {
            if (!isFinishing()) {
                startActivity(new Intent(MainActivity.this, Login.class));
                finish();
            }
        }, 2000);
    }
}