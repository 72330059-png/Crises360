package com.example.crises;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

public class StartingActivity extends AppCompatActivity {

    Button btnLogin, btnSignUp;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_starting);

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        btnLogin  = findViewById(R.id.btnlogin);
        btnSignUp = findViewById(R.id.btnSign);

        // ✅ EXISTING USER → LOGIN
        btnLogin.setOnClickListener(v -> {
            startActivity(new Intent(StartingActivity.this, Login.class));
            finish();
        });

        // ✅ NEW CITIZEN → SIGN UP
        btnSignUp.setOnClickListener(v -> {
            startActivity(new Intent(StartingActivity.this, SignUp.class));
            finish();
        });
    }
}