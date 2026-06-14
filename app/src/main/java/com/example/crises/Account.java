package com.example.crises; // Define the package this class belongs to

import android.app.DatePickerDialog; // Import dialog for picking a date
import android.content.Intent; // Import Intent for navigating between activities
import android.content.SharedPreferences; // Import SharedPreferences to read/write local key-value data
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.os.Bundle; // Import Bundle for passing data in onCreate
import android.widget.ArrayAdapter; // Import ArrayAdapter to populate dropdown lists
import android.widget.AutoCompleteTextView; // Import AutoCompleteTextView for dropdown inputs
import android.widget.ProgressBar; // Import ProgressBar widget to show profile completion
import android.widget.TextView; // Import TextView to display text on screen
import android.widget.Toast; // Import Toast to show short pop-up messages

import androidx.activity.EdgeToEdge; // Import EdgeToEdge for full-screen / edge-to-edge display
import androidx.appcompat.app.AppCompatActivity; // Import AppCompatActivity as the base class

import com.android.volley.Request; // Import Volley's Request class for HTTP method constants
import com.android.volley.RequestQueue; // Import RequestQueue to manage a queue of network requests
import com.android.volley.toolbox.StringRequest; // Import StringRequest to make HTTP requests that return a String
import com.android.volley.toolbox.Volley; // Import Volley helper to create a RequestQueue

import com.google.android.material.bottomnavigation.BottomNavigationView; // Import bottom navigation bar widget
import com.google.android.material.textfield.TextInputEditText; // Import Material text input field

import org.json.JSONObject; // Import JSONObject to parse JSON responses

import java.util.Calendar; // Import Calendar to get the current date for the date picker
import java.util.HashMap; // Import HashMap to build key-value pairs for POST params
import java.util.Map; // Import Map interface used by getParams()

public class Account extends AppCompatActivity { // Declare Account activity, extends AppCompatActivity

    RequestQueue queue; // Volley request queue for managing HTTP requests
    SharedPreferences prefs; // SharedPreferences instance to read saved session data

    static final String GET_URL    = "https://crises360-mobile-api.onrender.com/get_members.php"; // URL to fetch member data from server
    static final String UPDATE_URL = "https://crises360-mobile-api.onrender.com/update_member.php"; // URL to update member data on server

    TextInputEditText etName, etId, etPhone, etDob,
            etFather, etMother, etCountry, etPlace; // Text fields for name, ID, phone, DOB, father, mother, country, place
    AutoCompleteTextView spGender, spStatus, spBlood; // Dropdown inputs for gender, family status, and blood group

    TextView tvAvatarInitials, tvHeaderName, tvHeaderId, tvProgressPercent; // TextViews for avatar initials, header name, header ID, progress percent
    ProgressBar progressProfile; // ProgressBar showing how complete the profile is

    @Override
    protected void onCreate(Bundle savedInstanceState) { // Called when the activity is first created
        super.onCreate(savedInstanceState); // Call the parent onCreate
        EdgeToEdge.enable(this); // Enable edge-to-edge display (content behind system bars)
        setContentView(R.layout.activity_account); // Set the layout XML for this activity

        queue = Volley.newRequestQueue(this); // Initialize Volley request queue
        prefs = getSharedPreferences("user_session", MODE_PRIVATE); // Open the user_session SharedPreferences file (private mode)

        initViews(); // Link Java variables to XML views
        setupDropdowns(); // Populate and configure the dropdown menus
        setupCalendar(); // Set up the date picker for the DOB field
        setupBottomNav(); // Set up the bottom navigation bar

        String savedName = prefs.getString("full_name", ""); // Read saved full name from prefs (default empty string)
        String savedId   = prefs.getString("national_id", ""); // Read saved national ID from prefs (default empty string)
        if (!savedName.isEmpty()) etName.setText(savedName); // If name was saved, pre-fill the name field
        if (!savedId.isEmpty())   etId.setText(savedId); // If ID was saved, pre-fill the ID field

        loadData(); // Fetch profile data from the server

        findViewById(R.id.btnSave).setOnClickListener(v -> updateData()); // When Save button clicked, call updateData()
    }

    private void initViews() { // Method to find and assign all views by their XML IDs
        etName    = findViewById(R.id.etName); // Find full name input field
        etId      = findViewById(R.id.etId); // Find national ID input field
        etPhone   = findViewById(R.id.etPhone); // Find phone number input field
        etDob     = findViewById(R.id.etDob); // Find date of birth input field
        etFather  = findViewById(R.id.etFather); // Find father's name input field
        etMother  = findViewById(R.id.etMother); // Find mother's name input field
        etCountry = findViewById(R.id.etCountry); // Find country input field
        etPlace   = findViewById(R.id.etPlace); // Find place of birth input field
        spGender  = findViewById(R.id.spGender); // Find gender dropdown
        spStatus  = findViewById(R.id.spStatus); // Find family status dropdown
        spBlood   = findViewById(R.id.spBlood); // Find blood group dropdown

        tvAvatarInitials  = findViewById(R.id.tvAvatarInitials); // Find TextView for avatar initials circle
        tvHeaderName      = findViewById(R.id.tvHeaderName); // Find TextView for name in header
        tvHeaderId        = findViewById(R.id.tvHeaderId); // Find TextView for ID in header
        tvProgressPercent = findViewById(R.id.tvProgressPercent); // Find TextView showing progress percentage
        progressProfile   = findViewById(R.id.progressProfile); // Find the progress bar widget
    }

    // ── HEADER ────────────────────────────────────────────────
    private void updateHeader() { // Method to refresh the header card with current field values
        String name = etName.getText().toString().trim(); // Get name value and remove whitespace
        String id   = etId.getText().toString().trim(); // Get ID value and remove whitespace

        if (!name.isEmpty()) { // Only update avatar if name is not empty
            String[] parts = name.trim().split("\\s+"); // Split name by whitespace into parts
            String initials = parts.length >= 2
                    ? String.valueOf(parts[0].charAt(0)) + parts[1].charAt(0) // Two initials if two or more words
                    : String.valueOf(parts[0].charAt(0)); // One initial if only one word
            tvAvatarInitials.setText(initials.toUpperCase()); // Set initials in uppercase to avatar circle
            tvHeaderName.setText(name); // Set full name in header
        }
        if (!id.isEmpty()) tvHeaderId.setText("ID: " + id); // Display ID with label in header

        updateProgress(); // Recalculate and update the progress bar
    }

    // ── PROGRESS — FIXED ──────────────────────────────────────
    private void updateProgress() { // Method to calculate profile completion percentage
        int filled = 0, total = 9; // Start with 0 filled fields out of 9 tracked fields

        if (isRealValue(etName.getText().toString()))    filled++; // Count name if filled
        if (isRealValue(etId.getText().toString()))      filled++; // Count ID if filled
        if (isRealValue(etPhone.getText().toString()))   filled++; // Count phone if filled
        if (isRealValue(etDob.getText().toString()))     filled++; // Count DOB if filled
        if (isRealValue(spGender.getText().toString()))  filled++; // Count gender if selected
        if (isRealValue(spBlood.getText().toString()))   filled++; // Count blood group if selected
        if (isRealValue(etFather.getText().toString()))  filled++; // Count father's name if filled
        if (isRealValue(etMother.getText().toString()))  filled++; // Count mother's name if filled
        if (isRealValue(etCountry.getText().toString())) filled++; // Count country if filled

        int percent = (filled * 100) / total; // Calculate completion percentage
        progressProfile.setProgress(percent); // Update progress bar to new percentage
        tvProgressPercent.setText(percent + "%"); // Update percentage label text
    }

    private boolean isRealValue(String val) { // Helper: returns true only if the value is a real, non-null, non-empty string
        if (val == null) return false; // Null values are not real
        String trimmed = val.trim(); // Remove leading/trailing whitespace
        return !trimmed.isEmpty() && !trimmed.equalsIgnoreCase("null"); // Must not be empty or the literal string "null"
    }

    private void loadData() { // Method to fetch user profile from the server
        int userId = prefs.getInt("user_id", -1); // Read saved user ID (-1 if not found)
        if (userId == -1) return; // If no user ID, abort loading

        StringRequest request = new StringRequest(Request.Method.POST, GET_URL, // Create a POST request to GET_URL
                response -> { // Lambda: runs when server responds successfully
                    try {
                        JSONObject obj = new JSONObject(response); // Parse the response string as JSON
                        if (obj.getString("status").equals("success")) { // Check if server returned success
                            JSONObject data = obj.getJSONObject("data"); // Extract the "data" object from response

                            etName.setText(cleanField(data.optString("full_name"))); // Set name field from server data
                            etId.setText(cleanField(data.optString("national_id"))); // Set ID field
                            etPhone.setText(cleanField(data.optString("phone"))); // Set phone field
                            etDob.setText(cleanField(data.optString("dob"))); // Set date of birth field
                            etFather.setText(cleanField(data.optString("father_name"))); // Set father name field
                            etMother.setText(cleanField(data.optString("mother_name"))); // Set mother name field
                            etCountry.setText(cleanField(data.optString("country"))); // Set country field
                            etPlace.setText(cleanField(data.optString("place_of_birth"))); // Set place of birth field

                            String gender = cleanField(data.optString("gender")); // Clean gender value from server
                            String status = cleanField(data.optString("family_status")); // Clean family status value
                            String blood  = cleanField(data.optString("blood_group")); // Clean blood group value
                            if (!gender.isEmpty()) spGender.setText(gender, false); // Set gender dropdown if not empty (false = don't filter)
                            if (!status.isEmpty()) spStatus.setText(status, false); // Set status dropdown if not empty
                            if (!blood.isEmpty())  spBlood.setText(blood, false); // Set blood dropdown if not empty

                            updateHeader(); // Refresh the header with loaded data
                        }
                    } catch (Exception e) {
                        Toast.makeText(this, "Load error: " + e.getMessage(),
                                Toast.LENGTH_SHORT).show(); // Show error message if JSON parsing fails
                    }
                },
                error -> Toast.makeText(this, "Network Error", Toast.LENGTH_SHORT).show() // Show error if network request fails
        ) {
            @Override
            protected Map<String, String> getParams() { // Override to add POST body parameters
                Map<String, String> map = new HashMap<>(); // Create a new map for parameters
                map.put("user_id", String.valueOf(prefs.getInt("user_id", -1))); // Add user_id to POST params
                return map; // Return the parameters map
            }
        };
        queue.add(request); // Add the request to the Volley queue to execute it
    }

    private String cleanField(String val) { // Helper: sanitizes a server field value
        if (val == null) return ""; // Return empty string if null
        String trimmed = val.trim(); // Remove surrounding whitespace
        return trimmed.equalsIgnoreCase("null") ? "" : trimmed; // Return empty string if literal "null", otherwise return as-is
    }

    private void updateData() { // Method to send updated profile data to the server
        StringRequest request = new StringRequest(Request.Method.POST, UPDATE_URL, // Create a POST request to UPDATE_URL
                response -> { // Lambda: runs when server responds successfully
                    try {
                        JSONObject obj = new JSONObject(response); // Parse response as JSON
                        if (obj.getString("status").equals("success")) { // Check for success status
                            prefs.edit().putBoolean("isProfileComplete", true).apply(); // Mark profile as complete in prefs
                            Toast.makeText(this, "Profile updated!", Toast.LENGTH_SHORT).show(); // Show success toast
                            updateHeader(); // Refresh header with new data
                            Intent intent = new Intent(Account.this, HomeActivity.class); // Create intent to go to HomeActivity
                            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK
                                    | Intent.FLAG_ACTIVITY_CLEAR_TASK); // Clear back stack so user can't go back to Account
                            startActivity(intent); // Launch HomeActivity
                        } else {
                            Toast.makeText(this,
                                    obj.optString("message", "Update failed"),
                                    Toast.LENGTH_SHORT).show(); // Show server error message or fallback
                        }
                    } catch (Exception e) { // If response is not valid JSON
                        if (response.trim().equals("success")) { // Check for plain-text "success" response
                            prefs.edit().putBoolean("isProfileComplete", true).apply(); // Mark profile complete
                            Toast.makeText(this, "Profile updated!", Toast.LENGTH_SHORT).show(); // Show success toast
                            Intent intent = new Intent(Account.this, HomeActivity.class); // Navigate to HomeActivity
                            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK
                                    | Intent.FLAG_ACTIVITY_CLEAR_TASK); // Clear back stack
                            startActivity(intent); // Launch HomeActivity
                        } else {
                            Toast.makeText(this, "Update failed", Toast.LENGTH_SHORT).show(); // Show generic failure toast
                        }
                    }
                },
                error -> Toast.makeText(this, "Network Error", Toast.LENGTH_SHORT).show() // Show error if network fails
        ) {
            @Override
            protected Map<String, String> getParams() { // Override to send all profile fields as POST params
                Map<String, String> map = new HashMap<>(); // Create params map
                map.put("user_id",        String.valueOf(prefs.getInt("user_id", -1))); // Add user ID
                map.put("full_name",      etName.getText().toString().trim()); // Add full name
                map.put("national_id",    etId.getText().toString().trim()); // Add national ID
                map.put("phone",          etPhone.getText().toString().trim()); // Add phone
                map.put("dob",            etDob.getText().toString().trim()); // Add date of birth
                map.put("father_name",    etFather.getText().toString().trim()); // Add father's name
                map.put("mother_name",    etMother.getText().toString().trim()); // Add mother's name
                map.put("country",        etCountry.getText().toString().trim()); // Add country
                map.put("place_of_birth", etPlace.getText().toString().trim()); // Add place of birth
                map.put("gender",         spGender.getText().toString().trim()); // Add gender
                map.put("family_status",  spStatus.getText().toString().trim()); // Add family status
                map.put("blood_group",    spBlood.getText().toString().trim()); // Add blood group
                return map; // Return the complete params map
            }
        };
        queue.add(request); // Add the update request to the Volley queue
    }

    private void setupCalendar() { // Method to attach a date picker to the DOB field
        etDob.setOnClickListener(v -> { // When DOB field is clicked
            Calendar c = Calendar.getInstance(); // Get today's date
            new DatePickerDialog(this,
                    (view, year, month, day) ->
                            etDob.setText(year + "-"
                                    + String.format("%02d", month + 1) // Month is 0-indexed so add 1, pad to 2 digits
                                    + "-" + String.format("%02d", day)), // Pad day to 2 digits
                    c.get(Calendar.YEAR), // Default picker to current year
                    c.get(Calendar.MONTH), // Default picker to current month
                    c.get(Calendar.DAY_OF_MONTH)).show(); // Default picker to current day, then show it
        });
    }

    private void setupDropdowns() { // Method to configure all three dropdown menus
        spGender.setThreshold(1); // Show suggestions after 1 character (needed to trigger on tap)
        spGender.setAdapter(new ArrayAdapter<>(this,
                android.R.layout.simple_dropdown_item_1line,
                new String[]{"Male", "Female", "Other"})); // Set gender options
        spGender.setOnClickListener(v -> spGender.showDropDown()); // Force dropdown open on click

        spStatus.setThreshold(1); // Show suggestions after 1 character
        spStatus.setAdapter(new ArrayAdapter<>(this,
                android.R.layout.simple_dropdown_item_1line,
                new String[]{"Single", "Married", "Divorced", "Widowed"})); // Set family status options
        spStatus.setOnClickListener(v -> spStatus.showDropDown()); // Force dropdown open on click

        spBlood.setThreshold(1); // Show suggestions after 1 character
        spBlood.setAdapter(new ArrayAdapter<>(this,
                android.R.layout.simple_dropdown_item_1line,
                new String[]{"A+", "A-", "B+", "B-", "O+", "O-", "AB+", "AB-"})); // Set blood group options
        spBlood.setOnClickListener(v -> spBlood.showDropDown()); // Force dropdown open on click
    }

    private void setupBottomNav() { // Method to configure the bottom navigation bar
        BottomNavigationView bottomNav = findViewById(R.id.bottomNavigation); // Find the bottom nav view
        bottomNav.setItemActiveIndicatorColor(
                ColorStateList.valueOf(Color.parseColor("#EEF2FF"))
        );
        bottomNav.setSelectedItemId(R.id.nav_profile); // Highlight the Profile tab as currently active
        bottomNav.setOnItemSelectedListener(item -> { // Listen for tab selections
            int id = item.getItemId(); // Get the ID of the tapped tab
            if (id == R.id.nav_home) {
                startActivity(new Intent(this, HomeActivity.class)); // Navigate to Home
            } else if (id == R.id.nav_alerts) {
                startActivity(new Intent(this, Alerts.class)); // Navigate to Alerts
            } else if (id == R.id.nav_map) {
                startActivity(new Intent(this, MapActivity.class)); // Navigate to Map
            }
            return true; // Consume the event
        });
    }
}