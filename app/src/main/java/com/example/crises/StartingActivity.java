package com.example.crises;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.TextView;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

public class StartingActivity extends AppCompatActivity {

    Button btnGetStarted;
    TextView tvLoginAction;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_starting);

        // Handle padding for system bars
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        // 🔗 Connect UI
        btnGetStarted = findViewById(R.id.btnGetStarted);
        tvLoginAction = findViewById(R.id.tvLoginAction);

        // 🚀 GET STARTED button
        btnGetStarted.setOnClickListener(v -> {
            startActivity(new Intent(StartingActivity.this, Login.class));
        });

        // 🔐 Already have account → Login
        tvLoginAction.setOnClickListener(v -> {
            startActivity(new Intent(StartingActivity.this, Login.class));
        });
    }
}