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
import java.util.Scanner;

public class Login extends AppCompatActivity {

    EditText etUser, etPassword;
    Button btnLogin;
    TextView btnRegister;
    CheckBox cbRememberMe;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

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

            new Thread(() -> {
                try {

                    URL url = new URL("http://10.0.2.2/crises_api/login.php");
                    HttpURLConnection conn = (HttpURLConnection) url.openConnection();

                    conn.setRequestMethod("POST");
                    conn.setDoOutput(true);

                    String data = "username=" + username + "&password=" + password;

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

                        try {

                            if (json.getString("status").equals("success")) {

                                int userId = json.getInt("user_id");

                                // 🔥 USE ONE SHARED PREF
                                SharedPreferences prefs = getSharedPreferences("user_session", MODE_PRIVATE);
                                SharedPreferences.Editor editor = prefs.edit();

                                editor.putInt("user_id", userId);
                                editor.putString("username", username);

                                // ✅ IMPORTANT
                                editor.putBoolean("isLoggedIn", true);

                                // ✅ OPTIONAL (Remember me)
                                editor.putBoolean("rememberMe", cbRememberMe.isChecked());

                                editor.apply();

                                boolean isComplete = prefs.getBoolean("isProfileComplete", false);

                                Toast.makeText(this, "Login Successful", Toast.LENGTH_SHORT).show();

                                if (isComplete) {
                                    startActivity(new Intent(Login.this, HomeActivity.class));
                                } else {
                                    Intent intent = new Intent(Login.this, Account.class);
                                    intent.putExtra("mode", "register");
                                    startActivity(intent);
                                }

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
                    runOnUiThread(() ->
                            Toast.makeText(this,
                                    "Server error: " + e.getMessage(),
                                    Toast.LENGTH_SHORT).show()
                    );
                }
            }).start();
        });

        // 🔗 Register
        btnRegister.setOnClickListener(v -> {
            Intent intent = new Intent(Login.this, Account.class);
            intent.putExtra("mode", "register");
            startActivity(intent);
        });
    }
}