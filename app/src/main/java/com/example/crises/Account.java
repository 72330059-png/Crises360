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

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;

import com.google.android.material.bottomnavigation.BottomNavigationView;

import java.util.Calendar;
import java.util.HashMap;
import java.util.Map;

public class Account extends AppCompatActivity {

    EditText etName, etId, etPhone, etDob, etFather, etMother, etCountry, etPlace;
    AutoCompleteTextView spGender, spStatus, spBlood;

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

        initViews();
        setupDropdowns();
        setupCalendar();
        initBottomNavigation();

        findViewById(R.id.btnSave).setOnClickListener(v -> sendDataToDatabase());
    }

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

    private void setupDropdowns() {

        String[] genders = {"Male", "Female", "Other"};
        spGender.setAdapter(new ArrayAdapter<>(this, android.R.layout.simple_list_item_1, genders));

        String[] status = {"Single", "Married", "Divorced", "Widowed"};
        spStatus.setAdapter(new ArrayAdapter<>(this, android.R.layout.simple_list_item_1, status));

        String[] blood = {"A+", "A-", "B+", "B-", "O+", "O-", "AB+", "AB-"};
        spBlood.setAdapter(new ArrayAdapter<>(this, android.R.layout.simple_list_item_1, blood));
    }

    private void setupCalendar() {
        etDob.setOnClickListener(v -> {
            Calendar c = Calendar.getInstance();

            DatePickerDialog dialog = new DatePickerDialog(this,
                    (view, y, m, d) -> {
                        // Use String.format to ensure months and days have leading zeros (e.g., 05 instead of 5)
                        // Format: YYYY-MM-DD
                        String formattedDate = String.format("%d-%02d-%02d", y, (m + 1), d);
                        etDob.setText(formattedDate);
                    },
                    c.get(Calendar.YEAR),
                    c.get(Calendar.MONTH),
                    c.get(Calendar.DAY_OF_MONTH));

            dialog.getDatePicker().setMaxDate(System.currentTimeMillis());
            dialog.show();
        });
    }

    private void sendDataToDatabase() {
        String name = etName.getText().toString().trim();
        String phone = etPhone.getText().toString().trim();

        // 1. Local Validation
        if (name.isEmpty() || phone.isEmpty()) {
            Toast.makeText(this, "⚠️ Please enter name and phone", Toast.LENGTH_SHORT).show();
            return;
        }

        String url = "http://10.0.2.2/crises_api/add_members.php";

        StringRequest request = new StringRequest(Request.Method.POST, url,
                response -> {
                    android.util.Log.d("SERVER_RESPONSE", response);

                    try {
                        org.json.JSONObject jsonObject = new org.json.JSONObject(response);
                        String status = jsonObject.getString("status");
                        String message = jsonObject.getString("message");

                        if (status.equals("success")) {
                            // 2. Extract the new credentials from PHP
                            String generatedUser = jsonObject.getString("username");
                            String generatedPass = jsonObject.getString("password");

                            // 3. Show an AlertDialog so the user can see/copy their credentials
                            new androidx.appcompat.app.AlertDialog.Builder(this)
                                    .setTitle("Registration Successful")
                                    .setMessage("Account created!\n\nPlease save your login details:\n\n" +
                                            "Username: " + generatedUser + "\n" +
                                            "Password: " + generatedPass)
                                    .setCancelable(false) // User must click OK
                                    .setPositiveButton("Copy & Finish", (dialog, which) -> {
                                        // Copy to clipboard (Optional but helpful)
                                        android.content.ClipboardManager clipboard = (android.content.ClipboardManager) getSystemService(Context.CLIPBOARD_SERVICE);
                                        android.content.ClipData clip = android.content.ClipData.newPlainText("Crises Credentials",
                                                "User: " + generatedUser + " Pass: " + generatedPass);
                                        clipboard.setPrimaryClip(clip);

                                        Toast.makeText(this, "Credentials copied to clipboard", Toast.LENGTH_SHORT).show();
                                        finish(); // Close the activity
                                    })
                                    .show();

                        } else {
                            Toast.makeText(this, "❌ " + message, Toast.LENGTH_LONG).show();
                        }
                    } catch (org.json.JSONException e) {
                        Toast.makeText(this, "Format Error: " + response, Toast.LENGTH_LONG).show();
                    }
                },
                error -> {
                    android.util.Log.e("VOLLEY_ERROR", error.toString());
                    Toast.makeText(this, "📡 Connection Error!", Toast.LENGTH_LONG).show();
                }
        ) {
            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();
                params.put("full_name", name);
                params.put("phone", phone);
                params.put("national_id", etId.getText().toString().trim());
                params.put("gender", spGender.getText().toString().trim());
                params.put("dob", etDob.getText().toString().trim());
                params.put("family_status", spStatus.getText().toString().trim());
                params.put("blood_group", spBlood.getText().toString().trim());
                params.put("father_name", etFather.getText().toString().trim());
                params.put("mother_name", etMother.getText().toString().trim());
                params.put("country", etCountry.getText().toString().trim());
                params.put("place_of_birth", etPlace.getText().toString().trim());
                return params;
            }
        };

        Volley.newRequestQueue(this).add(request);
    }

    private void initBottomNavigation() {
        BottomNavigationView bottomNav = findViewById(R.id.bottomNavigation);

        bottomNav.setSelectedItemId(R.id.nav_profile);

        bottomNav.setOnItemSelectedListener(item -> {
            int id = item.getItemId();

            if (id == R.id.nav_home) startActivity(new Intent(this, HomeActivity.class));
            else if (id == R.id.nav_alerts) startActivity(new Intent(this, Alerts.class));
            else if (id == R.id.nav_map) startActivity(new Intent(this, Map.class));
            else if (id == R.id.nav_service) startActivity(new Intent(this, Services.class));
            else return true;

            return true;
        });
    }
}