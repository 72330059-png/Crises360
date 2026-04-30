package com.example.crises;

import android.Manifest;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.location.Location;
import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;

import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationServices;

public class SOSActivity extends AppCompatActivity {

    Spinner typeSpinner;
    EditText messageInput;
    TextView locationText;
    Button sendBtn;

    FusedLocationProviderClient fusedLocationClient;

    String latitude = "Unknown";
    String longitude = "Unknown";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_sosactivity);

        typeSpinner = findViewById(R.id.sosTypeSpinner);
        messageInput = findViewById(R.id.sosMessage);
        locationText = findViewById(R.id.locationText);
        sendBtn = findViewById(R.id.btnSendSOS);

        fusedLocationClient = LocationServices.getFusedLocationProviderClient(this);

        setupSpinner();
        getLocation();

        sendBtn.setOnClickListener(v -> sendSOS());
    }

    private void setupSpinner() {
        String[] types = {
                "Medical Emergency",
                "Fire Emergency",
                "Security Threat",
                "Natural Disaster",
                "Other"
        };

        ArrayAdapter<String> adapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_spinner_dropdown_item,
                types
        );

        typeSpinner.setAdapter(adapter);
    }

    private void getLocation() {

        if (ActivityCompat.checkSelfPermission(this,
                Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED) {

            ActivityCompat.requestPermissions(this,
                    new String[]{Manifest.permission.ACCESS_FINE_LOCATION}, 100);
            return;
        }

        fusedLocationClient.getLastLocation()
                .addOnSuccessListener(location -> {
                    if (location != null) {

                        latitude = String.valueOf(location.getLatitude());
                        longitude = String.valueOf(location.getLongitude());

                        locationText.setText(
                                "📍 Location Found\nLat: " + latitude + "\nLng: " + longitude
                        );

                    } else {
                        locationText.setText("⚠ Location not available");
                    }
                });
    }

    private void sendSOS() {

        String type = typeSpinner.getSelectedItem().toString();
        String message = messageInput.getText().toString();

        if (message.isEmpty()) {
            messageInput.setError("Enter message");
            return;
        }

        String finalMessage =
                "🚨 SOS EMERGENCY\n\n" +
                        "Type: " + type + "\n" +
                        "Message: " + message + "\n\n" +
                        "📍 Location:\n" +
                        "Lat: " + latitude + "\n" +
                        "Lng: " + longitude;


        Intent intent = new Intent(Intent.ACTION_VIEW);
        intent.setData(android.net.Uri.parse("sms:112"));
        intent.putExtra("sms_body", finalMessage);

        startActivity(intent);

        Toast.makeText(this, "SOS Ready to send", Toast.LENGTH_SHORT).show();
    }

    @Override
    public void onRequestPermissionsResult(int requestCode,
                                           @NonNull String[] permissions,
                                           @NonNull int[] grantResults) {

        super.onRequestPermissionsResult(requestCode, permissions, grantResults);

        if (requestCode == 100 &&
                grantResults.length > 0 &&
                grantResults[0] == PackageManager.PERMISSION_GRANTED) {

            getLocation();
        } else {
            locationText.setText("⚠ Permission denied");
        }
    }
}