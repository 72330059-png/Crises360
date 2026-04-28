package com.example.crises;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.graphics.drawable.Drawable;
import android.os.Bundle;
import android.view.View;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.content.ContextCompat;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import androidx.recyclerview.widget.ItemTouchHelper;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.Volley;
import com.google.android.material.bottomnavigation.BottomNavigationView;

import org.json.JSONObject;

import java.util.ArrayList;

public class Alerts extends AppCompatActivity {

    RecyclerView recyclerView;
    ArrayList<AlertModel> list;
    AlertsAdapter adapter;
    BottomNavigationView bottomNav;

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
        setContentView(R.layout.activity_alerts);

        View mainView = findViewById(R.id.main);
        if (mainView != null) {
            ViewCompat.setOnApplyWindowInsetsListener(mainView, (v, insets) -> {
                Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
                v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
                return insets;
            });
        }

        recyclerView = findViewById(R.id.recyclerAlerts);
        recyclerView.setLayoutManager(new LinearLayoutManager(this));

        list = new ArrayList<>();

        loadAlertsFromServer();

        initSwipe();
        initBottomNavigation();
    }


    private void loadAlertsFromServer() {

        String url = "http://10.0.2.2/crises_api/get_alerts.php";

        JsonArrayRequest request = new JsonArrayRequest(Request.Method.GET, url, null,
                response -> {

                    try {

                        list.clear();

                        for (int i = 0; i < response.length(); i++) {

                            JSONObject obj = response.getJSONObject(i);

                            int id = obj.getInt("id");
                            String type = obj.getString("type");
                            String message = obj.getString("message");
                            String location = obj.getString("location");
                            String time = obj.getString("time");
                            String status = obj.getString("status");

                            list.add(new AlertModel(id, type, message, location, time, status));
                        }

                        adapter = new AlertsAdapter(list);
                        recyclerView.setAdapter(adapter);

                    } catch (Exception e) {
                        e.printStackTrace();
                    }

                },
                error -> error.printStackTrace()
        );

        RequestQueue queue = Volley.newRequestQueue(this);
        queue.add(request);
    }

    // 🔥 SWIPE (UI only for now)
    private void initSwipe() {

        ItemTouchHelper.SimpleCallback swipe = new ItemTouchHelper.SimpleCallback(0,
                ItemTouchHelper.LEFT | ItemTouchHelper.RIGHT) {

            @Override
            public boolean onMove(RecyclerView recyclerView,
                                  RecyclerView.ViewHolder viewHolder,
                                  RecyclerView.ViewHolder target) {
                return false;
            }

            @Override
            public void onSwiped(RecyclerView.ViewHolder viewHolder, int direction) {

                int position = viewHolder.getAdapterPosition();

                // ⚠️ UI remove only (DB update later)
                list.remove(position);
                adapter.notifyItemRemoved(position);
            }

            @Override
            public void onChildDraw(Canvas c, RecyclerView recyclerView,
                                    RecyclerView.ViewHolder viewHolder,
                                    float dX, float dY,
                                    int actionState, boolean isCurrentlyActive) {

                View itemView = viewHolder.itemView;
                Paint paint = new Paint();

                if (dX > 0) {

                    paint.setColor(Color.parseColor("#FF3B30"));
                    c.drawRect(
                            itemView.getLeft(),
                            itemView.getTop(),
                            itemView.getLeft() + dX,
                            itemView.getBottom(),
                            paint
                    );

                } else {

                    paint.setColor(Color.parseColor("#007AFF"));
                    c.drawRect(
                            itemView.getRight() + dX,
                            itemView.getTop(),
                            itemView.getRight(),
                            itemView.getBottom(),
                            paint
                    );
                }

                super.onChildDraw(c, recyclerView, viewHolder,
                        dX, dY, actionState, isCurrentlyActive);
            }
        };

        new ItemTouchHelper(swipe).attachToRecyclerView(recyclerView);
    }

    private void initBottomNavigation() {

        bottomNav = findViewById(R.id.bottomNavigation);
        bottomNav.setSelectedItemId(R.id.nav_alerts);

        bottomNav.setOnItemSelectedListener(item -> {

            int id = item.getItemId();

            if (id == R.id.nav_alerts) return true;

            if (id == R.id.nav_home) {
                startActivity(new Intent(this, HomeActivity.class));
            } else if (id == R.id.nav_map) {
                startActivity(new Intent(this, Map.class));
            } else if (id == R.id.nav_service) {
                startActivity(new Intent(this, Services.class));
            } else if (id == R.id.nav_profile) {
                startActivity(new Intent(this, Account.class));
            }

            overridePendingTransition(0, 0);
            finish();
            return true;
        });
    }
}