package com.example.crises;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;

import androidx.appcompat.app.AppCompatActivity;

public class BaseActivity extends AppCompatActivity {

    protected boolean checkProfileCompletion() {
        SharedPreferences prefs = getSharedPreferences("user_session", MODE_PRIVATE);
        boolean isComplete = prefs.getBoolean("isProfileComplete", false);

        if (!isComplete) {
            startActivity(new Intent(this, Account.class));
            finish();
            return false;
        }
        return true;
    }
}