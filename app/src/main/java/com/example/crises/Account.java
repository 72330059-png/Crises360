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

    RequestQueue queue;

    String username;
    String mode;

    String GET_URL = "http://10.0.2.2/crises_api/get_members.php";
    String UPDATE_URL = "http://10.0.2.2/crises_api/update_member.php";
    String ADD_URL = "http://10.0.2.2/crises_api/add_members.php";

    EditText etName, etId, etPhone, etDob, etFather, etMother, etCountry, etPlace;
    AutoCompleteTextView spGender, spStatus, spBlood;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_account);

        queue = Volley.newRequestQueue(this);

        username = getIntent().getStringExtra("username");
        mode = getIntent().getStringExtra("mode");

        if (mode == null) mode = "register";

        initViews();
        setupDropdowns();
        setupCalendar();
        setupBottomNav();

        if ("profile".equals(mode) && username != null) {
            loadData();
        }

        findViewById(R.id.btnSave).setOnClickListener(v -> {

            if ("register".equals(mode)) {
                addMember();
            } else {
                updateData();
            }

        });
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

    // ---------------- REGISTER ----------------
    private void addMember() {

        StringRequest request = new StringRequest(Request.Method.POST, ADD_URL,
                response -> {
                    try {

                        JSONObject obj = new JSONObject(response);

                        if (obj.getString("status").equals("success")) {

                            String username = obj.getString("username");
                            String password = obj.getString("password");

                            new androidx.appcompat.app.AlertDialog.Builder(Account.this)
                                    .setTitle("Success")
                                    .setMessage(
                                            "Member Created Successfully!\n\n" +
                                                    "Username: " + username + "\n" +
                                                    "Password: " + password
                                    )
                                    .setNeutralButton("Copy Password", (d, w) -> {

                                        android.content.ClipboardManager clipboard =
                                                (android.content.ClipboardManager) getSystemService(CLIPBOARD_SERVICE);

                                        android.content.ClipData clip =
                                                android.content.ClipData.newPlainText("password", password);

                                        clipboard.setPrimaryClip(clip);

                                        android.widget.Toast.makeText(this,
                                                "Password copied",
                                                android.widget.Toast.LENGTH_SHORT).show();
                                    })
                                    .setPositiveButton("Continue", (dialog, which) -> {

                                        dialog.dismiss();

                                        Intent intent = new Intent(Account.this, HomeActivity.class);
                                        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                                        startActivity(intent);
                                    })

                                    .show();

                        } else {
                            Toast.makeText(this,
                                    obj.getString("message"),
                                    Toast.LENGTH_SHORT).show();
                        }

                    } catch (Exception e) {
                        Toast.makeText(this, "Parse Error", Toast.LENGTH_SHORT).show();
                    }
                },
                error -> Toast.makeText(this,
                        "Network Error: " + error.getMessage(),
                        Toast.LENGTH_LONG).show()
        ) {
            @Override
            protected Map<String, String> getParams() {

                Map<String, String> map = new HashMap<>();

                map.put("full_name", etName.getText().toString().trim());
                map.put("national_id", etId.getText().toString().trim());
                map.put("phone", etPhone.getText().toString().trim());
                map.put("gender", spGender.getText().toString().trim());
                map.put("dob", etDob.getText().toString().trim());
                map.put("family_status", spStatus.getText().toString().trim());
                map.put("blood_group", spBlood.getText().toString().trim());
                map.put("father_name", etFather.getText().toString().trim());
                map.put("mother_name", etMother.getText().toString().trim());
                map.put("country", etCountry.getText().toString().trim());
                map.put("place_of_birth", etPlace.getText().toString().trim());

                return map;
            }
        };

        queue.add(request);
    }

    // ---------------- UPDATE PROFILE ----------------
    private void updateData() {

        StringRequest request = new StringRequest(Request.Method.POST, UPDATE_URL,
                response -> {
                    if (response.trim().equals("success")) {
                        Toast.makeText(this, "Updated", Toast.LENGTH_SHORT).show();
                    } else {
                        Toast.makeText(this, "Failed", Toast.LENGTH_SHORT).show();
                    }
                },
                error -> Toast.makeText(this,
                        "Network Error",
                        Toast.LENGTH_SHORT).show()
        ) {
            @Override
            protected Map<String, String> getParams() {

                Map<String, String> map = new HashMap<>();

                map.put("username", username == null ? "" : username);

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

    // ---------------- LOAD ----------------
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

    // ---------------- CALENDAR ----------------
    private void setupCalendar() {
        etDob.setOnClickListener(v -> {

            Calendar c = Calendar.getInstance();

            DatePickerDialog dialog = new DatePickerDialog(this,
                    (view, year, month, day) -> {
                        etDob.setText(year + "-" + (month + 1) + "-" + day);
                    },
                    c.get(Calendar.YEAR),
                    c.get(Calendar.MONTH),
                    c.get(Calendar.DAY_OF_MONTH));

            dialog.show();
        });
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