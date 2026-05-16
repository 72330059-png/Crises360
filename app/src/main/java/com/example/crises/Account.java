package com.example.crises;

import android.app.DatePickerDialog;
import android.content.Intent;
import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.AutoCompleteTextView;
import android.widget.EditText;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;
import com.google.android.material.bottomnavigation.BottomNavigationView;

import org.json.JSONObject;

import java.util.Calendar;
import java.util.HashMap;
import java.util.Map;

public class Account extends AppCompatActivity {

    // 🔥 VARIABLES
    RequestQueue queue;
    String username;

    String GET_URL = "http://10.0.2.2/crises_api/get_members.php";
    String UPDATE_URL = "http://10.0.2.2/crises_api/update_member.php";

    EditText etName, etId, etPhone, etDob, etFather, etMother, etCountry, etPlace;
    AutoCompleteTextView spGender, spStatus, spBlood;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_account);

        // 🔥 INIT VOLLEY
        queue = Volley.newRequestQueue(this);

        // 🔥 GET USERNAME SAFELY
        username = getIntent().getStringExtra("username");

        if (username == null || username.isEmpty()) {
            Toast.makeText(this, "Username missing", Toast.LENGTH_LONG).show();
            finish();
            return;
        }

        initViews();
        setupDropdowns();
        setupCalendar();
        setupBottomNav();

        loadData(); // 🔥 LOAD PROFILE FROM DATABASE

        findViewById(R.id.btnSave).setOnClickListener(v -> updateData());
    }

    // ---------------- INIT ----------------
    private void initViews() {
        etName = findViewById(R.id.etName);
        etId = findViewById(R.id.etId);
        etPhone = findViewById(R.id.etPhone);
        etDob = findViewById(R.id.etDob);
        etFather = findViewById(R.id.etFather);
        etMother = findViewById(R.id.etMother);
        etCountry = findViewById(R.id.etCountry);
        etPlace = findViewById(R.id.etPlace);

        spGender = findViewById(R.id.spGender);
        spStatus = findViewById(R.id.spStatus);
        spBlood = findViewById(R.id.spBlood);
    }

    // ---------------- DROPDOWNS ----------------
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

    // ---------------- DATE PICKER ----------------
    private void setupCalendar() {
        etDob.setOnClickListener(v -> {
            Calendar c = Calendar.getInstance();

            DatePickerDialog dialog = new DatePickerDialog(this,
                    (view, year, month, day) -> {
                        String date = String.format("%d-%02d-%02d", year, month + 1, day);
                        etDob.setText(date);
                    },
                    c.get(Calendar.YEAR),
                    c.get(Calendar.MONTH),
                    c.get(Calendar.DAY_OF_MONTH));

            dialog.getDatePicker().setMaxDate(System.currentTimeMillis());
            dialog.show();
        });
    }

    // ---------------- LOAD DATA ----------------
    private void loadData() {

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
                        Toast.makeText(this, "JSON Error: " + e.getMessage(), Toast.LENGTH_LONG).show();
                    }

                },
                error -> Toast.makeText(this, "Network Error: " + error.toString(), Toast.LENGTH_LONG).show()
        ) {
            @Override
            protected Map<String, String> getParams() {
                Map<String, String> map = new HashMap<>();
                map.put("username", username);
                return map;
            }
        };

        queue.add(request);
    }

    // ---------------- UPDATE DATA ----------------
    private void updateData() {

        StringRequest request = new StringRequest(Request.Method.POST, UPDATE_URL,
                response -> {

                    if (response.trim().equals("success")) {
                        Toast.makeText(this, "Profile Updated Successfully", Toast.LENGTH_SHORT).show();
                    } else {
                        Toast.makeText(this, "Update Failed", Toast.LENGTH_SHORT).show();
                    }

                },
                error -> Toast.makeText(this, "Network Error: " + error.toString(), Toast.LENGTH_LONG).show()
        ) {
            @Override
            protected Map<String, String> getParams() {

                Map<String, String> map = new HashMap<>();

                map.put("username", username);

                map.put("full_name", etName.getText().toString().trim());
                map.put("national_id", etId.getText().toString().trim());
                map.put("phone", etPhone.getText().toString().trim());
                map.put("dob", etDob.getText().toString().trim());
                map.put("father_name", etFather.getText().toString().trim());
                map.put("mother_name", etMother.getText().toString().trim());
                map.put("country", etCountry.getText().toString().trim());
                map.put("place_of_birth", etPlace.getText().toString().trim());

                map.put("gender", spGender.getText().toString().trim());
                map.put("family_status", spStatus.getText().toString().trim());
                map.put("blood_group", spBlood.getText().toString().trim());

                return map;
            }
        };

        queue.add(request);
    }

    // ---------------- BOTTOM NAV ----------------
    private void setupBottomNav() {

        BottomNavigationView bottomNav = findViewById(R.id.bottomNavigation);
        bottomNav.setSelectedItemId(R.id.nav_profile);

        bottomNav.setOnItemSelectedListener(item -> {

            if (item.getItemId() == R.id.nav_home) {
                startActivity(new Intent(this, HomeActivity.class));

            } else if (item.getItemId() == R.id.nav_alerts) {
                startActivity(new Intent(this, Alerts.class));

            } else if (item.getItemId() == R.id.nav_map) {
                startActivity(new Intent(this, Map.class));

            } else if (item.getItemId() == R.id.nav_service) {
                startActivity(new Intent(this, Services.class));
            }

            return true;
        });
    }
}