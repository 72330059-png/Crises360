package com.example.crises;

import android.app.DatePickerDialog;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.AutoCompleteTextView;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;

// ✅ NEW IMPORTS (Retry Policy)
import com.android.volley.DefaultRetryPolicy;
import com.android.volley.RetryPolicy;

import com.google.android.material.bottomnavigation.BottomNavigationView;
import com.google.android.material.textfield.TextInputEditText;

import org.json.JSONObject;

import java.util.Calendar;
import java.util.HashMap;
import java.util.Map;

public class Account extends AppCompatActivity {

    RequestQueue queue;
    SharedPreferences prefs;

    static final String GET_URL    = "https://crises360-mobile-api.onrender.com/get_members.php";
    static final String UPDATE_URL = "https://crises360-mobile-api.onrender.com/update_member.php";

    TextInputEditText etName, etId, etPhone, etDob,
            etFather, etMother, etCountry, etPlace;

    AutoCompleteTextView spGender, spStatus, spBlood;

    TextView tvAvatarInitials, tvHeaderName, tvHeaderId, tvProgressPercent;
    ProgressBar progressProfile;

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

        String savedName = prefs.getString("full_name", "");
        String savedId   = prefs.getString("national_id", "");

        if (!savedName.isEmpty()) etName.setText(savedName);
        if (!savedId.isEmpty())   etId.setText(savedId);

        loadData();

        findViewById(R.id.btnSave).setOnClickListener(v -> updateData());
    }

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

        tvAvatarInitials  = findViewById(R.id.tvAvatarInitials);
        tvHeaderName      = findViewById(R.id.tvHeaderName);
        tvHeaderId        = findViewById(R.id.tvHeaderId);
        tvProgressPercent = findViewById(R.id.tvProgressPercent);
        progressProfile   = findViewById(R.id.progressProfile);
    }

    private void updateHeader() {
        String name = etName.getText().toString().trim();
        String id   = etId.getText().toString().trim();

        if (!name.isEmpty()) {
            String[] parts = name.split("\\s+");
            String initials = parts.length >= 2
                    ? "" + parts[0].charAt(0) + parts[1].charAt(0)
                    : "" + parts[0].charAt(0);

            tvAvatarInitials.setText(initials.toUpperCase());
            tvHeaderName.setText(name);
        }

        if (!id.isEmpty()) tvHeaderId.setText("ID: " + id);

        updateProgress();
    }

    private void updateProgress() {
        int filled = 0, total = 9;

        if (isRealValue(etName.getText().toString()))    filled++;
        if (isRealValue(etId.getText().toString()))      filled++;
        if (isRealValue(etPhone.getText().toString()))   filled++;
        if (isRealValue(etDob.getText().toString()))     filled++;
        if (isRealValue(spGender.getText().toString()))  filled++;
        if (isRealValue(spBlood.getText().toString()))   filled++;
        if (isRealValue(etFather.getText().toString()))  filled++;
        if (isRealValue(etMother.getText().toString()))  filled++;
        if (isRealValue(etCountry.getText().toString())) filled++;

        int percent = (filled * 100) / total;
        progressProfile.setProgress(percent);
        tvProgressPercent.setText(percent + "%");
    }

    private boolean isRealValue(String val) {
        if (val == null) return false;
        String trimmed = val.trim();
        return !trimmed.isEmpty() && !trimmed.equalsIgnoreCase("null");
    }

    // ✅ UPDATED loadData WITH RetryPolicy
    private void loadData() {
        int userId = prefs.getInt("user_id", -1);
        if (userId == -1) return;

        StringRequest request = new StringRequest(Request.Method.POST, GET_URL,
                response -> {
                    try {
                        JSONObject obj = new JSONObject(response);

                        if (obj.getString("status").equals("success")) {
                            JSONObject data = obj.getJSONObject("data");

                            etName.setText(cleanField(data.optString("full_name")));
                            etId.setText(cleanField(data.optString("national_id")));
                            etPhone.setText(cleanField(data.optString("phone")));
                            etDob.setText(cleanField(data.optString("dob")));
                            etFather.setText(cleanField(data.optString("father_name")));
                            etMother.setText(cleanField(data.optString("mother_name")));
                            etCountry.setText(cleanField(data.optString("country")));
                            etPlace.setText(cleanField(data.optString("place_of_birth")));

                            String gender = cleanField(data.optString("gender"));
                            String status = cleanField(data.optString("family_status"));
                            String blood  = cleanField(data.optString("blood_group"));

                            if (!gender.isEmpty()) spGender.setText(gender, false);
                            if (!status.isEmpty()) spStatus.setText(status, false);
                            if (!blood.isEmpty())  spBlood.setText(blood, false);

                            updateHeader();
                        }

                    } catch (Exception e) {
                        Toast.makeText(this, "Load error: " + e.getMessage(),
                                Toast.LENGTH_SHORT).show();
                    }
                },
                error -> Toast.makeText(this, "Network Error", Toast.LENGTH_SHORT).show()
        ) {
            @Override
            public RetryPolicy getRetryPolicy() {
                return new DefaultRetryPolicy(
                        60000,
                        DefaultRetryPolicy.DEFAULT_MAX_RETRIES,
                        DefaultRetryPolicy.DEFAULT_BACKOFF_MULT
                );
            }

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> map = new HashMap<>();
                map.put("user_id", String.valueOf(prefs.getInt("user_id", -1)));
                return map;
            }
        };

        queue.add(request);
    }

    private String cleanField(String val) {
        if (val == null) return "";
        String trimmed = val.trim();
        return trimmed.equalsIgnoreCase("null") ? "" : trimmed;
    }

    // ✅ ALSO UPDATED updateData WITH RetryPolicy (IMPORTANT)
    private void updateData() {

        StringRequest request = new StringRequest(Request.Method.POST, UPDATE_URL,
                response -> {
                    try {
                        JSONObject obj = new JSONObject(response);

                        if (obj.getString("status").equals("success")) {
                            Toast.makeText(this, "Profile updated!", Toast.LENGTH_SHORT).show();
                            updateHeader();

                            Intent intent = new Intent(Account.this, HomeActivity.class);
                            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                            startActivity(intent);
                        } else {
                            Toast.makeText(this,
                                    obj.optString("message", "Update failed"),
                                    Toast.LENGTH_SHORT).show();
                        }

                    } catch (Exception e) {
                        Toast.makeText(this, "Update failed", Toast.LENGTH_SHORT).show();
                    }
                },
                error -> Toast.makeText(this, "Network Error", Toast.LENGTH_SHORT).show()
        ) {
            @Override
            public RetryPolicy getRetryPolicy() {
                return new DefaultRetryPolicy(
                        60000,
                        DefaultRetryPolicy.DEFAULT_MAX_RETRIES,
                        DefaultRetryPolicy.DEFAULT_BACKOFF_MULT
                );
            }

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> map = new HashMap<>();

                map.put("user_id", String.valueOf(prefs.getInt("user_id", -1)));
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
                    c.get(Calendar.DAY_OF_MONTH)).show();
        });
    }

    private void setupDropdowns() {

        spGender.setAdapter(new ArrayAdapter<>(this,
                android.R.layout.simple_dropdown_item_1line,
                new String[]{"Male", "Female", "Other"}));
        spGender.setOnClickListener(v -> spGender.showDropDown());

        spStatus.setAdapter(new ArrayAdapter<>(this,
                android.R.layout.simple_dropdown_item_1line,
                new String[]{"Single", "Married", "Divorced", "Widowed"}));
        spStatus.setOnClickListener(v -> spStatus.showDropDown());

        spBlood.setAdapter(new ArrayAdapter<>(this,
                android.R.layout.simple_dropdown_item_1line,
                new String[]{"A+", "A-", "B+", "B-", "O+", "O-", "AB+", "AB-"}));
        spBlood.setOnClickListener(v -> spBlood.showDropDown());
    }

    private void setupBottomNav() {
        BottomNavigationView bottomNav = findViewById(R.id.bottomNavigation);

        bottomNav.setItemActiveIndicatorColor(
                ColorStateList.valueOf(Color.parseColor("#EEF2FF"))
        );

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