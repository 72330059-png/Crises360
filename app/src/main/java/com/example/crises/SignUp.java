package com.example.crises;

import android.content.Intent;
import android.os.Bundle;
import android.text.TextUtils;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import org.json.JSONObject;

import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.util.Scanner;

public class SignUp extends AppCompatActivity {

    EditText etFullName, etEmail, etNationalId, etPassword;
    Button btnSignUp;
    CheckBox cbTerms;
    TextView tvLoginRedirect;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_sign_up);

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        etFullName   = findViewById(R.id.etUsername);   // "Name" field in your XML
        etEmail      = findViewById(R.id.etEmail);
        etNationalId = findViewById(R.id.etNationalId);
        etPassword   = findViewById(R.id.etPassword);
        btnSignUp    = findViewById(R.id.btnSignUp);
        cbTerms      = findViewById(R.id.cbTerms);
        tvLoginRedirect = findViewById(R.id.tvLoginRedirect);

        // ── Sign Up button ────────────────────────────────────
        btnSignUp.setOnClickListener(v -> {

            String fullName   = etFullName.getText().toString().trim();
            String email      = etEmail.getText().toString().trim();
            String nationalId = etNationalId.getText().toString().trim();
            String password   = etPassword.getText().toString().trim();

            // Validation
            if (TextUtils.isEmpty(fullName) || TextUtils.isEmpty(email)
                    || TextUtils.isEmpty(nationalId) || TextUtils.isEmpty(password)) {
                Toast.makeText(this, "Please fill all fields", Toast.LENGTH_SHORT).show();
                return;
            }

            if (!android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
                Toast.makeText(this, "Please enter a valid email", Toast.LENGTH_SHORT).show();
                return;
            }

            if (password.length() < 6) {
                Toast.makeText(this, "Password must be at least 6 characters", Toast.LENGTH_SHORT).show();
                return;
            }

            if (!cbTerms.isChecked()) {
                Toast.makeText(this, "Please agree to the Terms & Conditions", Toast.LENGTH_SHORT).show();
                return;
            }

            btnSignUp.setEnabled(false);
            btnSignUp.setText("Creating account...");

            doRegister(fullName, email, nationalId, password);
        });

        // ── Already have account → Login ──────────────────────
        tvLoginRedirect.setOnClickListener(v -> {
            startActivity(new Intent(SignUp.this, Login.class));
            finish();
        });
    }

    private void doRegister(String fullName, String email,
                            String nationalId, String password) {
        new Thread(() -> {
            try {
                URL url = new URL("http://192.168.0.109/crises_api/register.php");
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setDoOutput(true);
                conn.setConnectTimeout(8000);
                conn.setReadTimeout(8000);

                String data = "full_name="   + URLEncoder.encode(fullName,   "UTF-8")
                        + "&email="      + URLEncoder.encode(email,      "UTF-8")
                        + "&national_id="+ URLEncoder.encode(nationalId, "UTF-8")
                        + "&password="   + URLEncoder.encode(password,   "UTF-8");

                OutputStream os = conn.getOutputStream();
                os.write(data.getBytes());
                os.flush();
                os.close();

                Scanner sc = new Scanner(conn.getInputStream());
                StringBuilder sb = new StringBuilder();
                while (sc.hasNext()) sb.append(sc.nextLine());
                sc.close();

                JSONObject json = new JSONObject(sb.toString());

                runOnUiThread(() -> {
                    btnSignUp.setEnabled(true);
                    btnSignUp.setText("SIGN UP");
                    try {
                        if (json.getString("status").equals("success")) {

                            Toast.makeText(this,
                                    "Account created! Please log in.",
                                    Toast.LENGTH_LONG).show();

                            // ✅ Go to Login and pre-fill the email
                            Intent intent = new Intent(SignUp.this, Login.class);
                            intent.putExtra("email", email);
                            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK
                                    | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                            startActivity(intent);

                        } else {
                            Toast.makeText(this,
                                    json.optString("message", "Registration failed"),
                                    Toast.LENGTH_LONG).show();
                        }
                    } catch (Exception e) {
                        Toast.makeText(this,
                                "Error: " + e.getMessage(),
                                Toast.LENGTH_SHORT).show();
                    }
                });

            } catch (Exception e) {
                runOnUiThread(() -> {
                    btnSignUp.setEnabled(true);
                    btnSignUp.setText("SIGN UP");
                    Toast.makeText(this,
                            "Connection error: " + e.getMessage(),
                            Toast.LENGTH_SHORT).show();
                });
            }
        }).start();
    }
}