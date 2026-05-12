package com.example.crises;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.widget.RadioGroup;
import android.widget.TextView;
import android.widget.Switch;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

public class Settings extends AppCompatActivity {

    TextView backBtn;

    Switch swNotifications, swDanger, swSafe, swSound, swVibration;
    RadioGroup languageGroup;

    SharedPreferences prefs;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        prefs = getSharedPreferences("settings", MODE_PRIVATE);
        if (prefs.getBoolean("dark", false)) {
            AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_YES);
        } else {
            AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        }

        setContentView(R.layout.activity_settings);
        backBtn = findViewById(R.id.backBtn);

        swNotifications = findViewById(R.id.switch_notifications);
        swDanger = findViewById(R.id.switch_danger);
        swSafe = findViewById(R.id.switch_safe);
        swSound = findViewById(R.id.switch_sound);
        swVibration = findViewById(R.id.switch_vibration);
        languageGroup = findViewById(R.id.language_group);
        swNotifications.setChecked(prefs.getBoolean("notifications", true));
        swDanger.setChecked(prefs.getBoolean("danger", true));
        swSafe.setChecked(prefs.getBoolean("safe", true));
        swSound.setChecked(prefs.getBoolean("sound", true));
        swVibration.setChecked(prefs.getBoolean("vibration", true));

        String lang = prefs.getString("lang", "en");

        if (lang.equals("ar")) {
            languageGroup.check(R.id.lang_ar);
        } else {
            languageGroup.check(R.id.lang_en);
        }

        swNotifications.setOnCheckedChangeListener((b, v) ->
                prefs.edit().putBoolean("notifications", v).apply());

        swDanger.setOnCheckedChangeListener((b, v) ->
                prefs.edit().putBoolean("danger", v).apply());

        swSafe.setOnCheckedChangeListener((b, v) ->
                prefs.edit().putBoolean("safe", v).apply());

        swSound.setOnCheckedChangeListener((b, v) ->
                prefs.edit().putBoolean("sound", v).apply());

        swVibration.setOnCheckedChangeListener((b, v) ->
                prefs.edit().putBoolean("vibration", v).apply());


        languageGroup.setOnCheckedChangeListener((group, checkedId) -> {

            if (checkedId == R.id.lang_ar) {
                prefs.edit().putString("lang", "ar").apply();
            } else {
                prefs.edit().putString("lang", "en").apply();
            }

            recreate();
        });
        backBtn.setOnClickListener(v -> {
            Intent intent = new Intent(Settings.this, HomeActivity.class);
            intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_SINGLE_TOP);
            startActivity(intent);
            finish();
        });
    }
}