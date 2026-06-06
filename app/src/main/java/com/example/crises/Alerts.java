package com.example.crises; // Define the package this class belongs to

import android.content.Context; // Import Context for locale/language override in attachBaseContext
import android.content.Intent; // Import Intent for navigating between activities
import android.content.SharedPreferences; // Import SharedPreferences to read saved settings
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.os.Bundle; // Import Bundle for passing data in onCreate
import android.view.View; // Import View to use visibility constants (VISIBLE, GONE)
import android.widget.ProgressBar; // Import ProgressBar to show loading indicator
import android.widget.TextView; // Import TextView to show empty-state message

import androidx.activity.EdgeToEdge; // Import EdgeToEdge for full-screen display
import androidx.recyclerview.widget.LinearLayoutManager; // Import LinearLayoutManager to arrange RecyclerView items vertically
import androidx.recyclerview.widget.RecyclerView; // Import RecyclerView to display the scrollable list of alerts

import com.android.volley.Request; // Import Volley Request for HTTP method constants
import com.android.volley.RequestQueue; // Import RequestQueue to manage network requests
import com.android.volley.toolbox.JsonArrayRequest; // Import JsonArrayRequest for GET requests that return a JSON array
import com.android.volley.toolbox.Volley; // Import Volley helper to create a RequestQueue
import com.google.android.material.bottomnavigation.BottomNavigationView; // Import bottom navigation bar widget
import com.google.android.material.tabs.TabLayout; // Import TabLayout for the All/Warning/Critical/Info filter tabs

import org.json.JSONObject; // Import JSONObject to read individual alert objects from the JSON array

import java.util.ArrayList; // Import ArrayList to store alert lists

public class Alerts extends BaseActivity { // Alerts screen; extends BaseActivity (which likely handles profile checks)

    RecyclerView          recyclerView; // RecyclerView that displays the list of alerts
    ArrayList<AlertModel> fullList     = new ArrayList<>(); // Master list of all alerts loaded from the server
    ArrayList<AlertModel> filteredList = new ArrayList<>(); // Currently displayed list after tab filter is applied
    AlertsAdapter         adapter; // Adapter that connects filteredList to the RecyclerView
    BottomNavigationView  bottomNav; // Bottom navigation bar
    TabLayout             tabLayout; // Tab bar for filtering by severity (All, Warning, Critical, Info)
    ProgressBar           progressBar; // Loading spinner shown while fetching alerts
    TextView              tvEmpty; // Text shown when the list is empty or fails to load

    // Login date anchor — alerts created before this date are hidden
    private String loginDate; // Stores the date the user logged in; used to filter out old alerts

    @Override
    protected void attachBaseContext(Context newBase) { // Called before onCreate to apply the correct language
        SharedPreferences prefs =
                newBase.getSharedPreferences("settings", MODE_PRIVATE); // Open the settings prefs file
        String lang = prefs.getString("lang", "en"); // Read the saved language code (default "en")
        super.attachBaseContext(LocaleHelper.setLocale(newBase, lang)); // Apply the language to this activity's context
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) { // Called when the activity is first created
        super.onCreate(savedInstanceState); // Call the parent onCreate
        EdgeToEdge.enable(this); // Enable edge-to-edge full-screen display
        if (!checkProfileCompletion()) return; // If profile is not complete, redirect and stop setup

        setContentView(R.layout.activity_alerts); // Set the layout XML for this activity

        // Read the login date saved when the user authenticated
        loginDate = getSharedPreferences("notification_prefs", MODE_PRIVATE)
                .getString("login_date", null); // Load login date from prefs (null if not set)

        recyclerView = findViewById(R.id.recyclerAlerts); // Find the RecyclerView in the layout
        progressBar  = findViewById(R.id.progressBar); // Find the progress bar
        tvEmpty      = findViewById(R.id.tvEmpty); // Find the empty-state TextView

        recyclerView.setLayoutManager(new LinearLayoutManager(this)); // Set vertical linear layout for the RecyclerView
        adapter = new AlertsAdapter(filteredList); // Create adapter with the filtered list
        recyclerView.setAdapter(adapter); // Attach adapter to RecyclerView

        initTabFilters(); // Set up tab filter listeners
        initBottomNavigation(); // Set up bottom navigation bar
        loadAlertsFromServer(); // Fetch alerts from the API
    }

    private void loadAlertsFromServer() { // Method to fetch all alerts from the server
        if (progressBar != null) progressBar.setVisibility(View.VISIBLE); // Show loading spinner
        if (tvEmpty != null)     tvEmpty.setVisibility(View.GONE); // Hide empty-state message while loading

        String url = "http://192.168.0.106/crises_api/get_alerts.php"; // API endpoint that returns a JSON array of alerts

        JsonArrayRequest request = new JsonArrayRequest( // Create a GET request expecting a JSON array response
                Request.Method.GET, url, null, // GET method, target URL, no request body
                response -> { // Lambda: runs when the server responds successfully
                    try {
                        fullList.clear(); // Clear old data before repopulating

                        for (int i = 0; i < response.length(); i++) { // Loop through each alert object in the array
                            JSONObject obj = response.getJSONObject(i); // Get the alert at index i

                            // Extract the alert's creation date (format: "yyyy-MM-dd HH:mm:ss")
                            String rawTime  = obj.optString("time", ""); // Read full timestamp string (empty if missing)
                            String alertDate = rawTime.contains(" ")
                                    ? rawTime.split(" ")[0]   // "yyyy-MM-dd" — take only the date part before the space
                                    : rawTime; // If no space found, use the whole string as the date

                            // Skip alerts created before the user's login date
                            if (loginDate != null && !alertDate.isEmpty()
                                    && alertDate.compareTo(loginDate) < 0) { // compareTo < 0 means alertDate is earlier
                                continue; // Skip this alert and move to the next one
                            }

                            int    id       = obj.getInt("id"); // Read alert ID
                            String message  = obj.optString("message", ""); // Read alert message (empty if missing)
                            String region   = obj.optString("region", ""); // Read region name (empty if missing)
                            String severity = obj.optString("severity", ""); // Read severity level (empty if missing)

                            fullList.add(new AlertModel( // Create an AlertModel and add it to the master list
                                    id, severity, message, region, rawTime)); // Pass all fields to the model constructor
                        }

                        if (progressBar != null)
                            progressBar.setVisibility(View.GONE); // Hide spinner once data is loaded

                        int position = (tabLayout != null)
                                ? tabLayout.getSelectedTabPosition() : 0; // Get currently selected tab (default 0 = All)
                        filterList(position); // Apply the current tab filter to the loaded data

                    } catch (Exception e) {
                        e.printStackTrace(); // Log the parsing error
                        if (progressBar != null)
                            progressBar.setVisibility(View.GONE); // Hide spinner on error
                        if (tvEmpty != null)
                            tvEmpty.setVisibility(View.VISIBLE); // Show empty state if parsing fails
                    }
                },
                error -> { // Lambda: runs when the network request fails
                    error.printStackTrace(); // Log the network error
                    if (progressBar != null)
                        progressBar.setVisibility(View.GONE); // Hide spinner on failure
                    if (tvEmpty != null) {
                        tvEmpty.setVisibility(View.VISIBLE); // Show empty-state message
                        tvEmpty.setText("Failed to load alerts.\nCheck connection."); // Set a descriptive error message
                    }
                }
        );

        RequestQueue queue = Volley.newRequestQueue(this); // Create a new Volley request queue
        queue.add(request); // Add the request to the queue to execute it
    }

    private void initTabFilters() { // Method to set up the severity filter tabs
        tabLayout = findViewById(R.id.tabFilters); // Find the TabLayout in the layout
        if (tabLayout == null) return; // Abort if TabLayout is not in the layout

        TabLayout.Tab defaultTab = tabLayout.getTabAt(0); // Get the first tab (All)
        if (defaultTab != null) defaultTab.select(); // Select the All tab by default

        tabLayout.addOnTabSelectedListener( // Register a listener for tab selection events
                new TabLayout.OnTabSelectedListener() {
                    @Override
                    public void onTabSelected(TabLayout.Tab tab) { // Called when a tab is tapped
                        filterList(tab.getPosition()); // Filter the list based on the selected tab index
                    }
                    @Override public void onTabUnselected(TabLayout.Tab tab) {} // Not used
                    @Override public void onTabReselected(TabLayout.Tab tab) {} // Not used
                });
    }

    private void filterList(int position) { // Method to filter fullList by severity and update the RecyclerView
        filteredList.clear(); // Remove all items from the currently displayed list

        for (AlertModel alert : fullList) { // Loop through every alert in the master list
            String severity = alert.getSeverity() != null
                    ? alert.getSeverity() : ""; // Get severity string, fallback to empty if null

            if (position == 0) { // Tab 0 = All — include every alert
                filteredList.add(alert);
            } else if (position == 1) { // Tab 1 = Warning
                if (severity.equalsIgnoreCase("Warning"))
                    filteredList.add(alert); // Only add if severity matches "Warning"
            } else if (position == 2) { // Tab 2 = Critical
                if (severity.equalsIgnoreCase("Critical"))
                    filteredList.add(alert); // Only add if severity matches "Critical"
            } else if (position == 3) { // Tab 3 = Info
                if (severity.equalsIgnoreCase("Info"))
                    filteredList.add(alert); // Only add if severity matches "Info"
            }
        }

        if (tvEmpty != null)
            tvEmpty.setVisibility(
                    filteredList.isEmpty() ? View.VISIBLE : View.GONE); // Show empty state if nothing passed the filter

        adapter.notifyDataSetChanged(); // Tell the adapter the data changed so it refreshes the RecyclerView
    }

    private void initBottomNavigation() { // Method to configure the bottom navigation bar
        BottomNavigationView bottomNav = findViewById(R.id.bottomNavigation); // Find the bottom nav view
        bottomNav.setItemActiveIndicatorColor(
                ColorStateList.valueOf(Color.parseColor("#EEF2FF"))
        );
        bottomNav.setSelectedItemId(R.id.nav_alerts); // Highlight the Profile tab as currently active
        bottomNav.setOnItemSelectedListener(item -> { // Listen for tab selections
            int id = item.getItemId(); // Get the ID of the tapped tab
            if (id == R.id.nav_home) {
                startActivity(new Intent(this, HomeActivity.class)); // Navigate to Home
            } else if (id == R.id.nav_profile) {
                startActivity(new Intent(this, Account.class)); // Navigate to Alerts
            } else if (id == R.id.nav_map) {
                startActivity(new Intent(this, MapActivity.class)); // Navigate to Map
            }
            return true; // Consume the event
        });
    }}