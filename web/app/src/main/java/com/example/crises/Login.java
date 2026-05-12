package com.example.crises;

import android.content.Intent;
import android.os.Bundle;
import android.text.TextUtils;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

public class Login extends AppCompatActivity {

    EditText etEmail, etPassword;
    Button btnLogin;
    TextView  btnRegister;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        etEmail = findViewById(R.id.etEmail);
        etPassword = findViewById(R.id.etPassword);
        btnLogin = findViewById(R.id.btnLogin);
        btnRegister = findViewById(R.id.btnRegister);

        // 🔐 LOGIN
        btnLogin.setOnClickListener(v -> {

            String email = etEmail.getText().toString().trim();
            String password = etPassword.getText().toString().trim();

            // ❗ check empty fields
            if (TextUtils.isEmpty(email) || TextUtils.isEmpty(password)) {
                Toast.makeText(this, "Please fill all fields", Toast.LENGTH_SHORT).show();
                return;
            }

            // ✅ SIMPLE VALIDATION (later DB/Firebase)
            if (email.equals("admin") && password.equals("1234")) {

                Toast.makeText(this, "Login Successful", Toast.LENGTH_SHORT).show();

                // 👉 OPEN HOME
                Intent intent = new Intent(Login.this, HomeActivity.class);
                startActivity(intent);
                finish();

            } else {

                Toast.makeText(this, "Invalid credentials", Toast.LENGTH_SHORT).show();
            }
        });

        // 🆕 NEW MEMBER
        btnRegister.setOnClickListener(v -> {

            Intent intent = new Intent(Login.this, Account.class);
            startActivity(intent);
        });
    }
}