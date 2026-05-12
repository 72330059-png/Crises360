package com.example.crises;

import android.app.DatePickerDialog;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.AutoCompleteTextView;
import android.widget.EditText;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.google.android.material.bottomnavigation.BottomNavigationView;

import java.util.Calendar;

public class Account extends AppCompatActivity {

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
        setContentView(R.layout.activity_account);
        View mainView = findViewById(R.id.main);
        if (mainView != null) {
            ViewCompat.setOnApplyWindowInsetsListener(mainView, (v, insets) -> {
                Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
                v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
                return insets;
            });
        }

        // Setup the Dropdowns
        setupDropdowns();

        // Setup the Calendar
        setupCalendar();

        //  Save Button
        View btnSave = findViewById(R.id.btnSave);
        if (btnSave != null) {
            btnSave.setOnClickListener(v -> {
                Toast.makeText(this, "Profile Updated Successfully!", Toast.LENGTH_SHORT).show();

            });
        }


        initBottomNavigation();
    }

    private void setupDropdowns() {
        String[] genders = {"Male", "Female", "Other"};
        ArrayAdapter<String> genderAdapter = new ArrayAdapter<>(this,
                android.R.layout.simple_list_item_1, genders);
        AutoCompleteTextView genderDropdown = findViewById(R.id.spGender);
        if (genderDropdown != null) {
            genderDropdown.setAdapter(genderAdapter);
        }


        String[] statusList = {"Single", "Married", "Divorced", "Widowed"};
        ArrayAdapter<String> statusAdapter = new ArrayAdapter<>(this,
                android.R.layout.simple_list_item_1, statusList);
        AutoCompleteTextView statusDropdown = findViewById(R.id.spStatus);
        if (statusDropdown != null) {
            statusDropdown.setAdapter(statusAdapter);
        }

        String[] bloodGroups = {"A+", "A-", "B+", "B-", "O+", "O-", "AB+", "AB-"};
        ArrayAdapter<String> bloodAdapter = new ArrayAdapter<>(this,
                android.R.layout.simple_list_item_1, bloodGroups);
        AutoCompleteTextView bloodDropdown = findViewById(R.id.spBlood);
        if (bloodDropdown != null) {
            bloodDropdown.setAdapter(bloodAdapter);
        }


    }

    private void setupCalendar() {
        EditText etDob = findViewById(R.id.etDob);
        if (etDob == null) return;
        etDob.setOnClickListener(v -> {
            final Calendar c = Calendar.getInstance();
            int year = c.get(Calendar.YEAR);
            int month = c.get(Calendar.MONTH);
            int day = c.get(Calendar.DAY_OF_MONTH);

            DatePickerDialog datePickerDialog = new DatePickerDialog(this,
                    (view, selectedYear, selectedMonth, selectedDay) -> {
                        String date = selectedDay + "/" + (selectedMonth + 1) + "/" + selectedYear;
                        etDob.setText(date);
                    }, year, month, day);
            datePickerDialog.getDatePicker().setMaxDate(System.currentTimeMillis());
            datePickerDialog.show();
        });
    }

    private void initBottomNavigation() {
        BottomNavigationView bottomNav = findViewById(R.id.bottomNavigation);
        if (bottomNav == null) return;

        bottomNav.setSelectedItemId(R.id.nav_profile);

        bottomNav.setOnItemSelectedListener(item -> {
            int id = item.getItemId();
            if (id == R.id.nav_profile) return true;

            Intent intent = null;
            if (id == R.id.nav_home) {
                intent = new Intent(this, HomeActivity.class);
            } else if (id == R.id.nav_alerts) {
                intent = new Intent(this, Alerts.class);
            } else if (id == R.id.nav_map) {
                intent = new Intent(this, Map.class);
            } else if (id == R.id.nav_service) {
                intent = new Intent(this, Services.class);
            }

            if (intent != null) {
                intent.addFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT);
                startActivity(intent);
                overridePendingTransition(0, 0); // No jump animation
                return true;
            }
            return false;
        });
    }
}
