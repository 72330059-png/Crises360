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
        setContentView(R.layout.activity_main);

        ImageView bg = findViewById(R.id.backgroundImage);
        Glide.with(this)
                .load(R.drawable.logo2)
                .override(1080, 1080)
                .diskCacheStrategy(DiskCacheStrategy.ALL)
                .centerCrop()
                .into(bg);

        new Handler(Looper.getMainLooper()).postDelayed(() -> {
            if (!isFinishing()) {
                startActivity(new Intent(MainActivity.this, HomeActivity.class));
                Glide.with(getApplicationContext()).clear(bg);
                finish();
            }
        }, 2500);
    }
}