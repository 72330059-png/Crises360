package com.example.crises;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.text.TextUtils;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;

import org.json.JSONObject;

import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;
import java.util.Scanner;

public class Login extends AppCompatActivity {

    EditText etEmail, etPassword;
    Button btnLogin;
    TextView tvCreateAccount, tvForgotPassword;
    CheckBox cbRememberMe;

    SharedPreferences prefs;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        prefs = getSharedPreferences("user_session", MODE_PRIVATE);

        // ✅ Already fully logged in → skip login entirely
        if (prefs.getBoolean("isLoggedIn", false)) {
            goToDashboard();
            finish();
            return;
        }

        etEmail          = findViewById(R.id.etUsername);
        etPassword       = findViewById(R.id.etPassword);
        btnLogin         = findViewById(R.id.btnLogin);
        tvCreateAccount  = findViewById(R.id.tvCreateAccount);
        tvForgotPassword = findViewById(R.id.tvForgotPassword);
        cbRememberMe     = findViewById(R.id.cbRememberMe);

        // ✅ Pre-fill email if coming from SignUp
        String preEmail = getIntent().getStringExtra("email");
        if (preEmail != null) etEmail.setText(preEmail);

        // ── Login button ──────────────────────────────────────
        btnLogin.setOnClickListener(v -> {
            String email    = etEmail.getText().toString().trim();
            String password = etPassword.getText().toString().trim();

            if (TextUtils.isEmpty(email) || TextUtils.isEmpty(password)) {
                Toast.makeText(this, "Please fill all fields", Toast.LENGTH_SHORT).show();
                return;
            }

            if (!android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
                Toast.makeText(this, "Please enter a valid email", Toast.LENGTH_SHORT).show();
                return;
            }

            btnLogin.setEnabled(false);
            doLogin(email, password);
        });

        // ── Forgot password ───────────────────────────────────
        tvForgotPassword.setOnClickListener(v -> showForgotDialog());

        // ── Go to Sign Up ─────────────────────────────────────
        tvCreateAccount.setOnClickListener(v -> {
            startActivity(new Intent(Login.this, SignUp.class));
            finish();
        });
    }

    // ── LOGIN REQUEST ─────────────────────────────────────────
    private void doLogin(String email, String password) {
        new Thread(() -> {
            try {
                URL url = new URL("http://10.0.2.2/crises_api/login.php");
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setDoOutput(true);
                conn.setConnectTimeout(8000);
                conn.setReadTimeout(8000);

                String data = "email="    + URLEncoder.encode(email,    "UTF-8")
                        + "&password=" + URLEncoder.encode(password, "UTF-8");

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
                    btnLogin.setEnabled(true);
                    try {
                        if (json.getString("status").equals("success")) {

                            // ✅ Save login date to notification_prefs
                            // This is used to filter out old alerts/notifications
                            // that existed before the user logged in.
                            String loginDate = new SimpleDateFormat(
                                    "yyyy-MM-dd", Locale.getDefault()).format(new Date());
                            getSharedPreferences("notification_prefs", MODE_PRIVATE)
                                    .edit()
                                    .putString("login_date", loginDate)
                                    // ✅ Reset the seed flag so NotificationWorker
                                    //    re-seeds with the new login date baseline
                                    .putBoolean("initial_seed_done", false)
                                    .apply();

                            // ✅ Save partial session — NOT logged in yet, 2FA comes next
                            prefs.edit()
                                    .putInt("user_id",       json.getInt("user_id"))
                                    .putString("email",      email)
                                    .putString("full_name",  json.optString("full_name", ""))
                                    .putString("national_id",json.optString("national_id", ""))
                                    .putBoolean("isProfileComplete",
                                            json.optBoolean("profile_complete", false))
                                    .putBoolean("isLoggedIn", false) // ← 2FA not done yet
                                    .apply();

                            // ✅ Go to 2FA screen
                            Intent intent = new Intent(Login.this, Verify.class);
                            intent.putExtra("email", email);
                            startActivity(intent);

                        } else {
                            Toast.makeText(this,
                                    json.optString("message", "Login failed"),
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
                    btnLogin.setEnabled(true);
                    Toast.makeText(this,
                            "Connection error: " + e.getMessage(),
                            Toast.LENGTH_SHORT).show();
                });
            }
        }).start();
    }

    // ── FORGOT PASSWORD DIALOG ────────────────────────────────
    private void showForgotDialog() {
        EditText input = new EditText(this);
        input.setHint("Enter your email address");
        input.setInputType(android.text.InputType.TYPE_TEXT_VARIATION_EMAIL_ADDRESS);
        input.setPadding(40, 20, 40, 20);

        new AlertDialog.Builder(this)
                .setTitle("Reset Password")
                .setMessage("We'll send a reset link to your email.")
                .setView(input)
                .setPositiveButton("Send", (dialog, which) -> {
                    String email = input.getText().toString().trim();
                    if (TextUtils.isEmpty(email)) {
                        Toast.makeText(this, "Enter your email", Toast.LENGTH_SHORT).show();
                        return;
                    }
                    sendForgotPassword(email);
                })
                .setNegativeButton("Cancel", null)
                .show();
    }

    private void sendForgotPassword(String email) {
        new Thread(() -> {
            try {
                URL url = new URL("http://10.0.2.2/crises_api/forgot_password.php");
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setDoOutput(true);
                conn.setConnectTimeout(8000);
                conn.setReadTimeout(10000);

                String data = "email=" + URLEncoder.encode(email, "UTF-8");
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
                    try {
                        new AlertDialog.Builder(this)
                                .setTitle("Email Sent")
                                .setMessage(json.optString("message"))
                                .setPositiveButton("OK", null)
                                .show();
                    } catch (Exception ignored) {}
                });
            } catch (Exception e) {
                runOnUiThread(() ->
                        Toast.makeText(this,
                                "Connection error: " + e.getMessage(),
                                Toast.LENGTH_SHORT).show());
            }
        }).start();
    }

    // ── ROUTING ───────────────────────────────────────────────
    private void goToDashboard() {
        Intent intent = new Intent(Login.this, HomeActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        startActivity(intent);
    }
}