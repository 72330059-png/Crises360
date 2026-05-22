package com.example.crises;

import android.app.DatePickerDialog;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.AutoCompleteTextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;
import com.google.android.material.bottomnavigation.BottomNavigationView;
import com.google.android.material.textfield.TextInputEditText;

import org.json.JSONObject;

import java.util.Calendar;
import java.util.HashMap;
import java.util.Map;

public class Account extends AppCompatActivity {

    RequestQueue queue;
    SharedPreferences prefs;

    // URLs
    static final String GET_URL    = "http://10.0.2.2/crises_api/get_members.php";
    static final String UPDATE_URL = "http://10.0.2.2/crises_api/update_member.php";

    TextInputEditText etName, etId, etPhone, etDob, etFather, etMother, etCountry, etPlace;
    AutoCompleteTextView spGender, spStatus, spBlood;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_account);

        queue = Volley.newRequestQueue(this);
        prefs = getSharedPreferences("user_session", MODE_PRIVATE);

        initViews();
        setupDropdowns();
        setupCalendar();
        setupBottomNav();

        // ✅ Pre-fill name and national ID from registration (saved in prefs)
        String savedName = prefs.getString("full_name", "");
        String savedId   = prefs.getString("national_id", "");
        if (!savedName.isEmpty()) etName.setText(savedName);
        if (!savedId.isEmpty())   etId.setText(savedId);

        // ✅ Load rest of profile from server
        loadData();

        // ✅ Save button — only updates profile
        findViewById(R.id.btnSave).setOnClickListener(v -> updateData());
    }

    // ── INIT VIEWS ────────────────────────────────────────────
    private void initViews() {
        etName    = findViewById(R.id.etName);
        etId      = findViewById(R.id.etId);
        etPhone   = findViewById(R.id.etPhone);
        etDob     = findViewById(R.id.etDob);
        etFather  = findViewById(R.id.etFather);
        etMother  = findViewById(R.id.etMother);
        etCountry = findViewById(R.id.etCountry);
        etPlace   = findViewById(R.id.etPlace);
        spGender  = findViewById(R.id.spGender);
        spStatus  = findViewById(R.id.spStatus);
        spBlood   = findViewById(R.id.spBlood);
    }

    // ── LOAD DATA FROM SERVER ─────────────────────────────────
    private void loadData() {
        int userId = prefs.getInt("user_id", -1);
        if (userId == -1) return;

        StringRequest request = new StringRequest(Request.Method.POST, GET_URL,
                response -> {
                    try {
                        JSONObject obj = new JSONObject(response);
                        if (obj.getString("status").equals("success")) {
                            JSONObject data = obj.getJSONObject("data");

                            etName.setText(data.optString("full_name"));
                            etId.setText(data.optString("national_id"));
                            etPhone.setText(data.optString("phone"));
                            etDob.setText(data.optString("dob"));
                            etFather.setText(data.optString("father_name"));
                            etMother.setText(data.optString("mother_name"));
                            etCountry.setText(data.optString("country"));
                            etPlace.setText(data.optString("place_of_birth"));

                            spGender.setText(data.optString("gender"), false);
                            spStatus.setText(data.optString("family_status"), false);
                            spBlood.setText(data.optString("blood_group"), false);
                        }
                    } catch (Exception e) {
                        Toast.makeText(this, "Load error: " + e.getMessage(),
                                Toast.LENGTH_SHORT).show();
                    }
                },
                error -> Toast.makeText(this, "Network Error", Toast.LENGTH_SHORT).show()
        ) {
            @Override
            protected Map<String, String> getParams() {
                Map<String, String> map = new HashMap<>();
                map.put("user_id", String.valueOf(prefs.getInt("user_id", -1)));
                return map;
            }
        };

        queue.add(request);
    }

    // ── UPDATE PROFILE ────────────────────────────────────────
    private void updateData() {
        StringRequest request = new StringRequest(Request.Method.POST, UPDATE_URL,
                response -> {
                    try {
                        JSONObject obj = new JSONObject(response);
                        if (obj.getString("status").equals("success")) {

                            // ✅ Mark profile as complete
                            prefs.edit()
                                    .putBoolean("isProfileComplete", true)
                                    .apply();

                            Toast.makeText(this, "Profile updated!", Toast.LENGTH_SHORT).show();

                            Intent intent = new Intent(Account.this, HomeActivity.class);
                            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK
                                    | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                            startActivity(intent);

                        } else {
                            Toast.makeText(this,
                                    obj.optString("message", "Update failed"),
                                    Toast.LENGTH_SHORT).show();
                        }
                    } catch (Exception e) {
                        // Handle plain "success" response too
                        if (response.trim().equals("success")) {
                            prefs.edit().putBoolean("isProfileComplete", true).apply();
                            Toast.makeText(this, "Profile updated!", Toast.LENGTH_SHORT).show();
                            Intent intent = new Intent(Account.this, HomeActivity.class);
                            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK
                                    | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                            startActivity(intent);
                        } else {
                            Toast.makeText(this, "Update failed", Toast.LENGTH_SHORT).show();
                        }
                    }
                },
                error -> Toast.makeText(this, "Network Error", Toast.LENGTH_SHORT).show()
        ) {
            @Override
            protected Map<String, String> getParams() {
                Map<String, String> map = new HashMap<>();
                map.put("user_id",       String.valueOf(prefs.getInt("user_id", -1)));
                map.put("full_name",     etName.getText().toString().trim());
                map.put("national_id",   etId.getText().toString().trim());
                map.put("phone",         etPhone.getText().toString().trim());
                map.put("dob",           etDob.getText().toString().trim());
                map.put("father_name",   etFather.getText().toString().trim());
                map.put("mother_name",   etMother.getText().toString().trim());
                map.put("country",       etCountry.getText().toString().trim());
                map.put("place_of_birth",etPlace.getText().toString().trim());
                map.put("gender",        spGender.getText().toString().trim());
                map.put("family_status", spStatus.getText().toString().trim());
                map.put("blood_group",   spBlood.getText().toString().trim());
                return map;
            }
        };

        queue.add(request);
    }

    // ── CALENDAR ─────────────────────────────────────────────
    private void setupCalendar() {
        etDob.setOnClickListener(v -> {
            Calendar c = Calendar.getInstance();
            new DatePickerDialog(this,
                    (view, year, month, day) ->
                            etDob.setText(year + "-"
                                    + String.format("%02d", month + 1)
                                    + "-" + String.format("%02d", day)),
                    c.get(Calendar.YEAR),
                    c.get(Calendar.MONTH),
                    c.get(Calendar.DAY_OF_MONTH))
                    .show();
        });
    }

    // ── DROPDOWNS ─────────────────────────────────────────────
    private void setupDropdowns() {
        spGender.setAdapter(new ArrayAdapter<>(this,
                android.R.layout.simple_list_item_1,
                new String[]{"Male", "Female", "Other"}));

        spStatus.setAdapter(new ArrayAdapter<>(this,
                android.R.layout.simple_list_item_1,
                new String[]{"Single", "Married", "Divorced", "Widowed"}));

        spBlood.setAdapter(new ArrayAdapter<>(this,
                android.R.layout.simple_list_item_1,
                new String[]{"A+", "A-", "B+", "B-", "O+", "O-", "AB+", "AB-"}));
    }

    // ── BOTTOM NAV ────────────────────────────────────────────
    private void setupBottomNav() {
        BottomNavigationView bottomNav = findViewById(R.id.bottomNavigation);
        bottomNav.setSelectedItemId(R.id.nav_profile);

        bottomNav.setOnItemSelectedListener(item -> {
            int id = item.getItemId();
            if (id == R.id.nav_home) {
                startActivity(new Intent(this, HomeActivity.class));
            } else if (id == R.id.nav_alerts) {
                startActivity(new Intent(this, Alerts.class));
            } else if (id == R.id.nav_map) {
                startActivity(new Intent(this, MapActivity.class));
            }
            return true;
        });
    }
}