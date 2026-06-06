package com.example.crises;

import android.Manifest;
import android.content.pm.PackageManager;
import android.graphics.Color;
import android.os.Bundle;

import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;

import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.BitmapDescriptorFactory;
import com.google.android.gms.maps.model.CircleOptions;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.MarkerOptions;
import com.google.android.gms.maps.model.PolygonOptions;
import com.google.android.gms.maps.model.PolylineOptions;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;

public class MapActivity extends AppCompatActivity implements OnMapReadyCallback {

    private GoogleMap mMap;
    private static final String API_URL = "http://192.168.0.106/crises_api/get_map_data.php";

    // Default center: Beirut
    private static final double DEFAULT_LAT = 33.8938;
    private static final double DEFAULT_LNG = 35.5018;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_map);

        SupportMapFragment mapFragment = (SupportMapFragment)
                getSupportFragmentManager().findFragmentById(R.id.map);
        if (mapFragment != null)
            mapFragment.getMapAsync(this);
    }

    @Override
    public void onMapReady(GoogleMap googleMap) {
        mMap = googleMap;

        // Show user's blue dot
        if (ActivityCompat.checkSelfPermission(this,
                Manifest.permission.ACCESS_FINE_LOCATION)
                == PackageManager.PERMISSION_GRANTED) {
            mMap.setMyLocationEnabled(true);
        } else {
            ActivityCompat.requestPermissions(this,
                    new String[]{Manifest.permission.ACCESS_FINE_LOCATION}, 1);
        }

        // Default camera position: Beirut
        mMap.moveCamera(CameraUpdateFactory.newLatLngZoom(
                new LatLng(DEFAULT_LAT, DEFAULT_LNG), 12f));

        // Load data from API
        fetchMapData();
    }

    private void fetchMapData() {
        new Thread(() -> {
            try {
                URL url = new URL(API_URL);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("GET");
                conn.setConnectTimeout(10000);
                conn.setReadTimeout(10000);

                BufferedReader br = new BufferedReader(
                        new InputStreamReader(conn.getInputStream()));
                StringBuilder sb = new StringBuilder();
                String line;
                while ((line = br.readLine()) != null) sb.append(line);
                br.close();

                JSONObject data = new JSONObject(sb.toString());

                runOnUiThread(() -> {
                    try {
                        drawAlerts(data.getJSONArray("alerts"));
                        drawZones(data.getJSONArray("zones"));
                        drawRoads(data.getJSONArray("roads"));
                        drawRoutes(data.getJSONArray("routes"));
                    } catch (Exception e) {
                        e.printStackTrace();
                    }
                });

            } catch (Exception e) {
                e.printStackTrace();
            }
        }).start();
    }

    // ── ALERTS → colored markers by severity ──────────────────────────────
    private void drawAlerts(JSONArray alerts) throws Exception {
        for (int i = 0; i < alerts.length(); i++) {
            JSONObject a = alerts.getJSONObject(i);
            double lat      = a.getDouble("lat");
            double lng      = a.getDouble("lng");
            String title    = a.optString("title", "Alert");
            String severity = a.optString("severity", "");

            float markerColor;
            switch (severity.toLowerCase()) {
                case "critical": markerColor = BitmapDescriptorFactory.HUE_RED;    break;
                case "high":     markerColor = BitmapDescriptorFactory.HUE_ORANGE; break;
                case "medium":   markerColor = BitmapDescriptorFactory.HUE_YELLOW; break;
                default:         markerColor = BitmapDescriptorFactory.HUE_AZURE;  break;
            }

            mMap.addMarker(new MarkerOptions()
                    .position(new LatLng(lat, lng))
                    .title(title)
                    .snippet(severity.toUpperCase())
                    .icon(BitmapDescriptorFactory.defaultMarker(markerColor)));
        }
    }

    // ── ZONES → circle or polygon ──────────────────────────────────────────
    private void drawZones(JSONArray zones) throws Exception {
        for (int i = 0; i < zones.length(); i++) {
            JSONObject z    = zones.getJSONObject(i);
            String type     = z.optString("type", "");
            String name     = z.optString("name", "Zone");
            JSONArray points = z.optJSONArray("polygon_points");

            if (points != null && points.length() > 0) {
                // Draw as polygon
                PolygonOptions poly = new PolygonOptions()
                        .strokeColor(Color.parseColor("#A32D2D"))
                        .fillColor(0x33A32D2D)
                        .strokeWidth(3f);
                for (int j = 0; j < points.length(); j++) {
                    JSONObject pt = points.getJSONObject(j);
                    poly.add(new LatLng(pt.getDouble("lat"), pt.getDouble("lng")));
                }
                mMap.addPolygon(poly);
            } else {
                // Draw as circle using center + radius
                double centerLat = z.optDouble("center_lat", 0);
                double centerLng = z.optDouble("center_lng", 0);
                double radius    = z.optDouble("radius_meters", 500);

                mMap.addCircle(new CircleOptions()
                        .center(new LatLng(centerLat, centerLng))
                        .radius(radius)
                        .strokeColor(Color.parseColor("#A32D2D"))
                        .fillColor(0x33A32D2D)
                        .strokeWidth(3f));

                mMap.addMarker(new MarkerOptions()
                        .position(new LatLng(centerLat, centerLng))
                        .title(name)
                        .icon(BitmapDescriptorFactory.defaultMarker(
                                BitmapDescriptorFactory.HUE_RED)));
            }
        }
    }

    // ── ROADS → polyline colored by status ────────────────────────────────
    // ── ROADS → polyline colored by status ────────────────────────────────
    private void drawRoads(JSONArray roads) throws Exception {
        for (int i = 0; i < roads.length(); i++) {
            JSONObject road  = roads.getJSONObject(i);
            String status    = road.optString("status", "");
            JSONArray points = road.optJSONArray("route_points");

            if (points == null || points.length() == 0) continue;

            int lineColor;
            switch (status.toLowerCase()) {
                case "blocked":  lineColor = Color.parseColor("#F44336"); break;
                case "warning":  lineColor = Color.parseColor("#FF9800"); break;
                case "open":     lineColor = Color.parseColor("#4CAF50"); break;
                default:         lineColor = Color.parseColor("#9E9E9E"); break;
            }

            PolylineOptions polyline = new PolylineOptions()
                    .color(lineColor)
                    .width(8f);

            for (int j = 0; j < points.length(); j++) {
                // route_points is array of arrays: [[lat,lng],[lat,lng]]
                JSONArray pt = points.getJSONArray(j);
                polyline.add(new LatLng(pt.getDouble(0), pt.getDouble(1)));
            }

            mMap.addPolyline(polyline);
        }
    }

    // ── ROUTES → blue polyline ─────────────────────────────────────────────
    private void drawRoutes(JSONArray routes) throws Exception {
        for (int i = 0; i < routes.length(); i++) {
            JSONObject route = routes.getJSONObject(i);
            JSONArray points = route.optJSONArray("route_points");
            String from      = route.optString("from_name", "");
            String to        = route.optString("to_name", "");

            if (points == null || points.length() == 0) continue;

            PolylineOptions polyline = new PolylineOptions()
                    .color(Color.parseColor("#1976D2"))
                    .width(6f);

            LatLng first = null;
            for (int j = 0; j < points.length(); j++) {
                // route_points is array of arrays: [[lat,lng],[lat,lng]]
                JSONArray pt = points.getJSONArray(j);
                LatLng pos = new LatLng(pt.getDouble(0), pt.getDouble(1));
                polyline.add(pos);
                if (j == 0) first = pos;
            }

            mMap.addPolyline(polyline);

            if (first != null) {
                mMap.addMarker(new MarkerOptions()
                        .position(first)
                        .title(from + " → " + to)
                        .icon(BitmapDescriptorFactory.defaultMarker(
                                BitmapDescriptorFactory.HUE_BLUE)));
            }
        }
    }



}