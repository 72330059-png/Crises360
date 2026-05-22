package com.example.crises;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.os.CountDownTimer;
import android.text.TextUtils;
import android.widget.Button;
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

public class Verify extends AppCompatActivity {

    EditText etCode;
    Button btnVerify, btnResend;
    TextView tvEmailSentTo, tvTimer;

    String email;
    CountDownTimer countDownTimer;
    SharedPreferences prefs;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_verify);

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        prefs = getSharedPreferences("user_session", MODE_PRIVATE);
        email = getIntent().getStringExtra("email");

        etCode       = findViewById(R.id.etCode);
        btnVerify    = findViewById(R.id.btnVerify);
        btnResend    = findViewById(R.id.btnResend);
        tvEmailSentTo = findViewById(R.id.tvEmailSentTo);
        tvTimer      = findViewById(R.id.tvTimer);

        tvEmailSentTo.setText("A 6-digit code was sent to:\n" + email);

        // ✅ Send code automatically when screen opens
        sendCode();

        // ── Verify button ─────────────────────────────────────
        btnVerify.setOnClickListener(v -> {
            String code = etCode.getText().toString().trim();
            if (TextUtils.isEmpty(code) || code.length() != 6) {
                Toast.makeText(this, "Enter the 6-digit code", Toast.LENGTH_SHORT).show();
                return;
            }
            btnVerify.setEnabled(false);
            verifyCode(code);
        });

        // ── Resend button ─────────────────────────────────────
        btnResend.setOnClickListener(v -> {
            etCode.setText("");
            btnResend.setEnabled(false);
            sendCode();
        });
    }

    // ── SEND CODE ─────────────────────────────────────────────
    private void sendCode() {
        new Thread(() -> {
            try {
                URL url = new URL("http://10.0.2.2/crises_api/send_2fa.php");
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setDoOutput(true);
                conn.setConnectTimeout(8000);
                conn.setReadTimeout(8000);

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
                        if (json.getString("status").equals("success")) {
                            startCountdown();
                            Toast.makeText(this,
                                    "Code sent! Check your email.",
                                    Toast.LENGTH_SHORT).show();
                        } else {
                            Toast.makeText(this,
                                    json.optString("message", "Could not send code"),
                                    Toast.LENGTH_LONG).show();
                        }
                    } catch (Exception e) {
                        Toast.makeText(this, "Error: " + e.getMessage(), Toast.LENGTH_SHORT).show();
                    }
                });

            } catch (Exception e) {
                runOnUiThread(() ->
                        Toast.makeText(this,
                                "Connection error: " + e.getMessage(),
                                Toast.LENGTH_SHORT).show());
            }
        }).start();
    }

    // ── VERIFY CODE ───────────────────────────────────────────
    private void verifyCode(String code) {
        new Thread(() -> {
            try {
                URL url = new URL("http://10.0.2.2/crises_api/verify_2fa.php");
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setDoOutput(true);
                conn.setConnectTimeout(8000);
                conn.setReadTimeout(8000);

                String data = "email=" + URLEncoder.encode(email, "UTF-8")
                        + "&code=" + URLEncoder.encode(code, "UTF-8");
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
                    btnVerify.setEnabled(true);
                    try {
                        if (json.getString("status").equals("success")) {

                            if (countDownTimer != null) countDownTimer.cancel();

                            // ✅ 2FA passed — NOW mark as fully logged in
                            prefs.edit()
                                    .putBoolean("isLoggedIn", true)
                                    .apply();

                            Toast.makeText(this, "Verified!", Toast.LENGTH_SHORT).show();

                            // ✅ Go to dashboard
                            Intent intent = new Intent(Verify.this, HomeActivity.class);
                            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK
                                    | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                            startActivity(intent);

                        } else {
                            Toast.makeText(this,
                                    json.optString("message", "Incorrect or expired code"),
                                    Toast.LENGTH_LONG).show();
                        }
                    } catch (Exception e) {
                        Toast.makeText(this, "Error: " + e.getMessage(), Toast.LENGTH_SHORT).show();
                    }
                });

            } catch (Exception e) {
                runOnUiThread(() -> {
                    btnVerify.setEnabled(true);
                    Toast.makeText(this,
                            "Connection error: " + e.getMessage(),
                            Toast.LENGTH_SHORT).show();
                });
            }
        }).start();
    }

    // ── COUNTDOWN TIMER ───────────────────────────────────────
    private void startCountdown() {
        if (countDownTimer != null) countDownTimer.cancel();
        btnResend.setEnabled(false);

        countDownTimer = new CountDownTimer(10 * 60 * 1000, 1000) {
            public void onTick(long ms) {
                long mins = ms / 60000;
                long secs = (ms % 60000) / 1000;
                tvTimer.setText(String.format("Code expires in %02d:%02d", mins, secs));
            }
            public void onFinish() {
                tvTimer.setText("Code expired — tap Resend");
                btnResend.setEnabled(true);
            }
        }.start();
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        if (countDownTimer != null) countDownTimer.cancel();
    }
}