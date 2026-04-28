package com.example.crises;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.net.Uri;
import android.os.Bundle;
import android.view.View;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.ImageButton;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;
import androidx.core.content.FileProvider;

import com.google.android.material.bottomnavigation.BottomNavigationView;

import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStream;

public class HomeActivity extends AppCompatActivity {

    private TextView tipDetails;
    private boolean isTipExpanded = false;

    // 10 Life-Saving Safety Tips
    private final String[] safetyTips = {
            "FIRE: Touch doors with the back of your hand. If hot, fire is behind it; find another exit.",
            "AIRSTRIKE: Lie flat on your stomach and keep your mouth slightly open to protect your eardrums.",
            "FIRE: If your clothes catch fire, Stop, Drop, and Roll immediately. Do not run.",
            "NETWORK: Use SMS/Texting instead of calls during disasters to keep emergency lines open.",
            "EARTHQUAKE: If no table is near, sit against an interior wall away from glass and cover your head.",
            "FLOOD: Avoid walking in moving water. Just 6 inches (15cm) can knock an adult down.",
            "MEDICAL: Apply constant direct pressure to a wound with a clean cloth to stop severe bleeding.",
            "AWARENESS: Identify at least two exits every time you enter a new building or public space.",
            "BURNS: Run cool (not cold) water over a burn for 20 minutes. Avoid ice, butter, or ointments.",
            "CONFLICT: Stay away from military bases or government buildings; these are high-risk targets."
    };

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
        initGuidesSection();
        initSafetyTips();
        initNewsSection();
        initBottomNavigation();
    }

    private void initTopAppBar() {
        ImageButton notificationBtn = findViewById(R.id.notificationBtn);
        ImageButton settingsBtn = findViewById(R.id.settingsBtn);

        if (notificationBtn != null) {
            notificationBtn.setOnClickListener(v -> startActivity(new Intent(this, Notifications.class)));
        }
        if (settingsBtn != null) {
            settingsBtn.setOnClickListener(v -> startActivity(new Intent(this, Settings.class)));
        }
    }

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
                    os.flush();
                    os.close();
                    is.close();
                }

                Uri uri = FileProvider.getUriForFile(this, getPackageName() + ".provider", file);
                Intent intent = new Intent(Intent.ACTION_VIEW);
                intent.setDataAndType(uri, "application/pdf");
                intent.addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION);
                intent.addFlags(Intent.FLAG_ACTIVITY_NO_HISTORY);
                startActivity(intent);

            } catch (Exception e) {
                Toast.makeText(this, "Error opening PDF", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void initSafetyTips() {
        CardView tipCard = findViewById(R.id.tipCard);
        tipDetails = findViewById(R.id.tipDetails);

        if (tipCard == null || tipDetails == null) return;

        tipCard.setOnClickListener(v -> {
            isTipExpanded = !isTipExpanded;

            if (isTipExpanded) {
                // Build a single string containing all tips
                StringBuilder allTipsBuilder = new StringBuilder();
                for (int i = 0; i < safetyTips.length; i++) {
                    allTipsBuilder.append("• ").append(safetyTips[i]);
                    // Add space between tips, but not after the last one
                    if (i < safetyTips.length - 1) {
                        allTipsBuilder.append("\n\n");
                    }
                }

                tipDetails.setText(allTipsBuilder.toString());
                tipDetails.setVisibility(View.VISIBLE);

                Animation fadeIn = AnimationUtils.loadAnimation(this, android.R.anim.fade_in);
                tipDetails.startAnimation(fadeIn);
            } else {
                tipDetails.setVisibility(View.GONE);
            }
        });
    }

    private void initNewsSection() {
        CardView newsCard = findViewById(R.id.newsCard);
        if (newsCard != null) {
            newsCard.setOnClickListener(v -> startActivity(new Intent(this, News.class)));
        }
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