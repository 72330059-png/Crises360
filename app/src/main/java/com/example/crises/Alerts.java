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

import com.google.android.material.bottomnavigation.BottomNavigationView;

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

        // ✅ SAMPLE DATA
        list = new ArrayList<>();
        list.add(new AlertModel(1, "Danger", "Explosion reported", "Beirut", "2 min ago", "ACTIVE"));
        list.add(new AlertModel(2, "Safe", "Area secured", "Hamra", "5 min ago", "ACTIVE"));
        list.add(new AlertModel(3, "Shelter", "Shelter available", "Jounieh", "10 min ago", "ACTIVE"));

        adapter = new AlertsAdapter(list);
        recyclerView.setAdapter(adapter);

        initSwipe();
        initBottomNavigation();
    }

    // 🚨 SWIPE ACTIONS
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
                AlertModel item = list.get(position);

                if (direction == ItemTouchHelper.RIGHT) {
                    item.status = "DELETED";
                } else {
                    item.status = "ARCHIVED";
                }

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
                Drawable icon;

                if (dX > 0) {

                    paint.setColor(Color.parseColor("#FF3B30"));
                    c.drawRect(
                            (float) itemView.getLeft(),
                            (float) itemView.getTop(),
                            itemView.getLeft() + dX,
                            (float) itemView.getBottom(),
                            paint
                    );

                    icon = ContextCompat.getDrawable(Alerts.this,
                            android.R.drawable.ic_menu_delete);

                } else {

                    paint.setColor(Color.parseColor("#007AFF"));
                    c.drawRect(
                            (float) itemView.getRight() + dX,
                            (float) itemView.getTop(),
                            (float) itemView.getRight(),
                            (float) itemView.getBottom(),
                            paint
                    );

                    icon = ContextCompat.getDrawable(Alerts.this,
                            android.R.drawable.ic_menu_save);
                }

                super.onChildDraw(c, recyclerView, viewHolder,
                        dX, dY, actionState, isCurrentlyActive);
            }
        };

        new ItemTouchHelper(swipe).attachToRecyclerView(recyclerView);
    }

    // 📌 BOTTOM NAVIGATION
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