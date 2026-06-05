package com.example.crises;

import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.text.TextUtils;
import android.view.View;
import android.view.inputmethod.InputMethodManager;
import android.widget.EditText;
import android.widget.FrameLayout;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.Locale;
import java.util.concurrent.TimeUnit;

import okhttp3.Call;
import okhttp3.Callback;
import okhttp3.MediaType;
import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.RequestBody;
import okhttp3.Response;
import okhttp3.ResponseBody;

public class CounselorChat extends AppCompatActivity {

    // ── API CONFIG — key is now safe on your PHP server ───────────────
    private static final String API_URL = "http://192.168.0.109/crises_api/chat.php";

    private static final String SYSTEM_PROMPT =
            "You are a warm, professional psychological counselor specialized in " +
                    "crisis support for people living through war, conflict, and natural disasters. " +
                    "You respond like a real human counselor — with empathy, validation, and " +
                    "practical psychological first aid. " +
                    "Your responses are conversational (3-5 sentences), never clinical or robotic. " +
                    "You never dismiss or minimize what the person is feeling. " +
                    "If the person expresses suicidal thoughts or immediate danger, " +
                    "gently urge them to call the emergency hotline (+961 70 123 456) immediately. " +
                    "Do not diagnose. Do not give medical advice. " +
                    "Always start by acknowledging what the person shared before offering any guidance.";

    // ── UI ────────────────────────────────────────────────────────────
    private RecyclerView  rvMessages;
    private EditText      etMessage;
    private FrameLayout   btnSend;
    private LinearLayout  layoutTyping;

    // ── Data ──────────────────────────────────────────────────────────
    private ChatAdapter       adapter;
    private List<ChatMessage> messageList;
    private final JSONArray   history = new JSONArray();

    // ── Network ───────────────────────────────────────────────────────
    private final OkHttpClient http = new OkHttpClient.Builder()
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(60,    TimeUnit.SECONDS)
            .writeTimeout(30,   TimeUnit.SECONDS)
            .build();

    private final Handler mainHandler = new Handler(Looper.getMainLooper());

    // ─────────────────────────────────────────────────────────────────

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_counselor_chat);

        ImageButton btnBack = findViewById(R.id.btnBack);
        if (btnBack != null) btnBack.setOnClickListener(v -> finish());

        rvMessages   = findViewById(R.id.rvMessages);
        etMessage    = findViewById(R.id.etMessage);
        btnSend      = findViewById(R.id.btnSend);
        layoutTyping = findViewById(R.id.layoutTyping);

        messageList = new ArrayList<>();
        adapter     = new ChatAdapter(this, messageList);
        LinearLayoutManager lm = new LinearLayoutManager(this);
        lm.setStackFromEnd(true);
        rvMessages.setLayoutManager(lm);
        rvMessages.setAdapter(adapter);

        btnSend.setOnClickListener(v -> sendMessage());

        setChip(R.id.suggestionAnxiety, "I'm feeling very anxious right now");
        setChip(R.id.suggestionSad,     "I feel hopeless and overwhelmed");
        setChip(R.id.suggestionStress,  "I can't sleep because of fear");
        setChip(R.id.suggestionLonely,  "I lost someone and I don't know how to cope");

        addAiMessage("Hello 💜 I'm here with you. Whatever you're going through right now, " +
                "you don't have to face it alone. How are you feeling today?");
    }

    // ── Send flow ─────────────────────────────────────────────────────

    private void setChip(int id, String text) {
        TextView chip = findViewById(id);
        if (chip != null) chip.setOnClickListener(v -> {
            etMessage.setText(text);
            sendMessage();
        });
    }

    private void sendMessage() {
        String text = etMessage.getText().toString().trim();
        if (TextUtils.isEmpty(text)) return;
        etMessage.setText("");
        hideKeyboard();
        addUserMessage(text);
        appendHistory("user", text);
        showTyping(true);
        callClaude();
    }

    // ── Claude API via PHP proxy ───────────────────────────────────────

    private void callClaude() {
        try {
            JSONObject body = new JSONObject();
            body.put("system",   SYSTEM_PROMPT);
            body.put("messages", history);

            Request request = new Request.Builder()
                    .url(API_URL)
                    .addHeader("Content-Type", "application/json")
                    .post(RequestBody.create(
                            body.toString(),
                            MediaType.get("application/json; charset=utf-8")))
                    .build();

            http.newCall(request).enqueue(new Callback() {

                @Override
                public void onFailure(@NonNull Call call, @NonNull IOException e) {
                    mainHandler.post(() -> {
                        showTyping(false);
                        addAiMessage(
                                "I'm having trouble connecting right now. " +
                                        "Please check your internet and try again 💜");
                    });
                }

                @Override
                public void onResponse(@NonNull Call call,
                                       @NonNull Response response) throws IOException {
                    ResponseBody rb  = response.body();
                    String       raw = rb != null ? rb.string() : "";

                    mainHandler.post(() -> {
                        showTyping(false);
                        try {
                            if (!response.isSuccessful()) {
                                // Show the actual error for debugging
                                android.util.Log.e("CHAT_ERROR",
                                        "HTTP " + response.code() + ": " + raw);
                                addAiMessage(
                                        "Connection error (HTTP " + response.code() +
                                                "). Please try again 💜");
                                return;
                            }

                            JSONObject json   = new JSONObject(raw);
                            String     aiText = json
                                    .getJSONArray("content")
                                    .getJSONObject(0)
                                    .getString("text");

                            appendHistory("assistant", aiText);
                            addAiMessage(aiText);

                        } catch (Exception e) {
                            android.util.Log.e("CHAT_ERROR", "Parse error: " + raw);
                            addAiMessage(
                                    "Something went wrong. Please try again 💜");
                        }
                    });
                }
            });

        } catch (Exception e) {
            showTyping(false);
            Toast.makeText(this,
                    "Request error: " + e.getMessage(),
                    Toast.LENGTH_LONG).show();
        }
    }

    // ── UI helpers ────────────────────────────────────────────────────

    private void appendHistory(String role, String content) {
        try {
            JSONObject m = new JSONObject();
            m.put("role",    role);
            m.put("content", content);
            history.put(m);
        } catch (Exception ignored) {}
    }

    private void addAiMessage(String text) {
        messageList.add(new ChatMessage(text, ChatMessage.TYPE_AI, now()));
        adapter.notifyItemInserted(messageList.size() - 1);
        rvMessages.scrollToPosition(messageList.size() - 1);
    }

    private void addUserMessage(String text) {
        messageList.add(new ChatMessage(text, ChatMessage.TYPE_USER, now()));
        adapter.notifyItemInserted(messageList.size() - 1);
        rvMessages.scrollToPosition(messageList.size() - 1);
    }

    private void showTyping(boolean show) {
        if (layoutTyping != null)
            layoutTyping.setVisibility(show ? View.VISIBLE : View.GONE);
        if (btnSend != null) {
            btnSend.setEnabled(!show);
            btnSend.setAlpha(show ? 0.45f : 1f);
        }
    }

    private void hideKeyboard() {
        InputMethodManager imm =
                (InputMethodManager) getSystemService(INPUT_METHOD_SERVICE);
        View focus = getCurrentFocus();
        if (imm != null && focus != null)
            imm.hideSoftInputFromWindow(focus.getWindowToken(), 0);
    }

    private String now() {
        return new SimpleDateFormat("hh:mm a", Locale.getDefault())
                .format(new Date());
    }
}