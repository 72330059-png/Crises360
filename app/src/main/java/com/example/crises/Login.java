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

import androidx.appcompat.app.AppCompatActivity;

import org.json.JSONObject;

import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.util.Scanner;

public class Login extends AppCompatActivity {

    EditText etUser, etPassword;
    Button btnLogin;
    TextView btnRegister;
    CheckBox cbRememberMe;

    SharedPreferences prefs;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        prefs = getSharedPreferences("user_session", MODE_PRIVATE);

        // ✅ AUTO LOGIN FIXED
        boolean isLoggedIn = prefs.getBoolean("isLoggedIn", false);
        boolean isProfileComplete = prefs.getBoolean("isProfileComplete", false);

        if (isLoggedIn) {
            if (isProfileComplete) {
                startActivity(new Intent(Login.this, HomeActivity.class));
            } else {
                startActivity(new Intent(Login.this, Account.class));
            }
            finish();
            return;
        }

        etUser = findViewById(R.id.etUsername);
        etPassword = findViewById(R.id.etPassword);
        btnLogin = findViewById(R.id.btnLogin);
        btnRegister = findViewById(R.id.tvCreateAccount);
        cbRememberMe = findViewById(R.id.cbRememberMe);

        btnLogin.setOnClickListener(v -> {

            String username = etUser.getText().toString().trim();
            String password = etPassword.getText().toString().trim();

            if (TextUtils.isEmpty(username) || TextUtils.isEmpty(password)) {
                Toast.makeText(this, "Please fill all fields", Toast.LENGTH_SHORT).show();
                return;
            }

            btnLogin.setEnabled(false);

            new Thread(() -> {
                try {

                    URL url = new URL("http://10.0.2.2/crises_api/login.php");
                    HttpURLConnection conn = (HttpURLConnection) url.openConnection();

                    conn.setRequestMethod("POST");
                    conn.setDoOutput(true);

                    String data =
                            "username=" + URLEncoder.encode(username, "UTF-8") +
                                    "&password=" + URLEncoder.encode(password, "UTF-8");

                    OutputStream os = conn.getOutputStream();
                    os.write(data.getBytes());
                    os.flush();
                    os.close();

                    Scanner scanner = new Scanner(conn.getInputStream());
                    StringBuilder response = new StringBuilder();

                    while (scanner.hasNext()) {
                        response.append(scanner.nextLine());
                    }

                    JSONObject json = new JSONObject(response.toString());

                    runOnUiThread(() -> {
                        btnLogin.setEnabled(true);

                        try {
                            if (json.getString("status").equals("success")) {

                                int userId = json.getInt("user_id");

                                boolean remember = cbRememberMe.isChecked();

                                SharedPreferences.Editor editor = prefs.edit();

                                editor.putInt("user_id", userId);
                                editor.putString("username", username);

                                editor.putBoolean("isLoggedIn", true);
                                editor.putBoolean("rememberMe", remember);

                                // default until Account page updates it
                                editor.putBoolean("isProfileComplete", false);

                                editor.apply();

                                Toast.makeText(this, "Login Successful", Toast.LENGTH_SHORT).show();

                                // ✅ CLEAN FLOW
                                startActivity(new Intent(Login.this, Account.class));
                                finish();

                            } else {
                                Toast.makeText(this,
                                        json.getString("message"),
                                        Toast.LENGTH_SHORT).show();
                            }

                        } catch (Exception e) {
                            Toast.makeText(this, "Parsing error", Toast.LENGTH_SHORT).show();
                        }
                    });

                } catch (Exception e) {
                    runOnUiThread(() -> {
                        btnLogin.setEnabled(true);
                        Toast.makeText(this,
                                "Server error: " + e.getMessage(),
                                Toast.LENGTH_SHORT).show();
                    });
                }
            }).start();
        });

        // REGISTER
        btnRegister.setOnClickListener(v -> {
            startActivity(new Intent(Login.this, Account.class));
        });
    }
}