package com.example.crises;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.os.Handler;
import android.view.View;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.ImageButton; // Added for top buttons
import android.widget.ImageSwitcher;
import android.widget.ImageView;
import android.widget.ViewSwitcher;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;

import com.google.android.material.bottomnavigation.BottomNavigationView;

public class HomeActivity extends AppCompatActivity {

    // --- Variables for top Image Slideshow ---
    private ImageSwitcher imageSwitcher;
    // Ensure these image names exist in your res/drawable folder
    private int[] slideshowImages = {R.drawable.image1, R.drawable.image2, R.drawable.image3,R.drawable.image4,R.drawable.image5,R.drawable.image6,R.drawable.image7,R.drawable.image8};
    private int currentSlideIndex = 0;
    private Handler slideshowHandler = new Handler();
    private final int SLIDE_DELAY = 5000; // Change every 5 seconds

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
        setContentView(R.layout.activity_home);
        initTopAppBar();
        initImageSwitcher();
        initNewsSection();
        initBottomNavigation();
    }

    private void initTopAppBar() {
        ImageButton notificationBtn = findViewById(R.id.notificationBtn);
        ImageButton settingsBtn = findViewById(R.id.settingsBtn);
        notificationBtn.setOnClickListener(v -> {
            Intent intent = new Intent(HomeActivity.this, Notifications.class);
            startActivity(intent);
        });
        settingsBtn.setOnClickListener(v -> {
            Intent intent = new Intent(HomeActivity.this, Settings.class);
            startActivity(intent);
        });
    }
    private void initImageSwitcher() {
        imageSwitcher = findViewById(R.id.imageSwitcher);

        imageSwitcher.setFactory(new ViewSwitcher.ViewFactory() {
            @Override
            public View makeView() {
                ImageView imageView = new ImageView(getApplicationContext());
                imageView.setScaleType(ImageView.ScaleType.CENTER_CROP);
                imageView.setLayoutParams(new ImageSwitcher.LayoutParams(
                        ImageSwitcher.LayoutParams.MATCH_PARENT,
                        ImageSwitcher.LayoutParams.MATCH_PARENT));
                return imageView;
            }
        });

        // Add standard fade transitions
        imageSwitcher.setInAnimation(AnimationUtils.loadAnimation(this, android.R.anim.fade_in));
        imageSwitcher.setOutAnimation(AnimationUtils.loadAnimation(this, android.R.anim.fade_out));

        // Show first image immediately and start loop
        if (slideshowImages.length > 0) {
            imageSwitcher.setImageResource(slideshowImages[0]);
            startSlideshowLoop();
        }
    }

    private void startSlideshowLoop() {
        slideshowHandler.postDelayed(new Runnable() {
            @Override
            public void run() {
                currentSlideIndex++;
                if (currentSlideIndex == slideshowImages.length) {
                    currentSlideIndex = 0;
                }
                imageSwitcher.setImageResource(slideshowImages[currentSlideIndex]);
                slideshowHandler.postDelayed(this, SLIDE_DELAY);
            }
        }, SLIDE_DELAY);
    }
    private void initNewsSection() {
        CardView newsCard = findViewById(R.id.newsCard);
        ImageView newsImage = findViewById(R.id.newsImage);

        // Load and start the "Ken Burns" zoom animation from your news.xml file
        Animation zoomAnim = AnimationUtils.loadAnimation(this, R.anim.news);
        newsImage.startAnimation(zoomAnim);

        // Click listener for the news card
        newsCard.setOnClickListener(v -> {
            Toast.makeText(HomeActivity.this, "Opening Full Story...", Toast.LENGTH_SHORT).show();
            Intent intent = new Intent(this, News.class);
            startActivity(intent);
        });
    }
    private void initBottomNavigation() {
        BottomNavigationView bottomNav = findViewById(R.id.bottomNavigation);
        bottomNav.setSelectedItemId(R.id.nav_home);

        bottomNav.setOnItemSelectedListener(item -> {
            int id = item.getItemId();

            if (id == R.id.nav_home) {
                return true;
            }

            else if (id == R.id.nav_alerts) {
                startActivity(new Intent(HomeActivity.this, Alerts.class));
                return true;
            }

            else if (id == R.id.nav_map) {
                startActivity(new Intent(HomeActivity.this, Map.class));
                return true;
            }

            else if (id == R.id.nav_service) {
                startActivity(new Intent(HomeActivity.this, Services.class));
                return true;
            }

            else if (id == R.id.nav_profile) {
                startActivity(new Intent(HomeActivity.this, Account.class));
                return true;
            }

            return false;
        });
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        slideshowHandler.removeCallbacksAndMessages(null);
    }
}