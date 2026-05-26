package com.example.crises;

import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.text.TextUtils;
import android.view.View;
import android.view.inputmethod.InputMethodManager;
import android.widget.EditText;
import android.widget.FrameLayout;
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

import okhttp3.Call;
import okhttp3.Callback;
import okhttp3.MediaType;
import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.RequestBody;
import okhttp3.Response;

public class CounselorChat extends AppCompatActivity {

    // ---------------------------------------------------------------
    // IMPORTANT: Replace with your actual Anthropic API key.
    // For production, store this securely (e.g. backend proxy, not
    // hard-coded in client code).
    // ---------------------------------------------------------------
    private static final String ANTHROPIC_API_KEY = "YOUR_ANTHROPIC_API_KEY";
    private static final String API_URL           = "https://api.anthropic.com/v1/messages";
    private static final String MODEL             = "claude-sonnet-4-20250514";

    // System prompt that shapes the AI counselor persona
    private static final String SYSTEM_PROMPT =
            "You are a compassionate, professional AI psychological counselor. " +
                    "Your role is to provide empathetic emotional support, active listening, " +
                    "and evidence-based coping strategies. Always respond with warmth, " +
                    "without judgment, and in a calm, supportive tone. " +
                    "If someone expresses thoughts of self-harm or suicide, gently encourage " +
                    "them to call the emergency hotline (+961 70 123 456) or seek immediate " +
                    "professional help. Do not provide medical diagnoses. Keep responses " +
                    "concise (2-4 sentences) unless the user needs more detail. " +
                    "Always validate the user's feelings before offering advice.";

    private RecyclerView    rvMessages;
    private EditText        etMessage;
    private FrameLayout     btnSend;
    private LinearLayout    layoutTyping;

    private ChatAdapter         adapter;
    private List<ChatMessage>   messageList;

    // Keeps full conversation history for context (role + content pairs)
    private final JSONArray conversationHistory = new JSONArray();

    private final OkHttpClient httpClient = new OkHttpClient();
    private final Handler      mainHandler = new Handler(Looper.getMainLooper());

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_counselor_chat);

        rvMessages   = findViewById(R.id.rvMessages);
        etMessage    = findViewById(R.id.etMessage);
        btnSend      = findViewById(R.id.btnSend);
        layoutTyping = findViewById(R.id.layoutTyping);

        messageList = new ArrayList<>();
        adapter     = new ChatAdapter(this, messageList);

        LinearLayoutManager layoutManager = new LinearLayoutManager(this);
        layoutManager.setStackFromEnd(true);
        rvMessages.setLayoutManager(layoutManager);
        rvMessages.setAdapter(adapter);

        // Back button
        findViewById(R.id.btnBack).setOnClickListener(v -> finish());

        // Send button
        btnSend.setOnClickListener(v -> sendMessage());

        // Quick suggestion chips
        setSuggestionChip(R.id.suggestionAnxiety, "I'm feeling anxious");
        setSuggestionChip(R.id.suggestionSad,     "I feel sad");
        setSuggestionChip(R.id.suggestionStress,  "I can't sleep");
        setSuggestionChip(R.id.suggestionLonely,  "I feel lonely");

        // Opening greeting from AI
        addAiMessage("Hello 💜 I'm here for you. This is a safe space — feel free to share whatever is on your mind. How are you feeling today?");
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    private void setSuggestionChip(int viewId, String text) {
        TextView chip = findViewById(viewId);
        chip.setOnClickListener(v -> {
            etMessage.setText(text);
            sendMessage();
        });
    }

    private String currentTime() {
        return new SimpleDateFormat("hh:mm a", Locale.getDefault()).format(new Date());
    }

    private void addAiMessage(String text) {
        messageList.add(new ChatMessage(text, ChatMessage.TYPE_AI, currentTime()));
        adapter.notifyItemInserted(messageList.size() - 1);
        rvMessages.scrollToPosition(messageList.size() - 1);
    }

    private void addUserMessage(String text) {
        messageList.add(new ChatMessage(text, ChatMessage.TYPE_USER, currentTime()));
        adapter.notifyItemInserted(messageList.size() - 1);
        rvMessages.scrollToPosition(messageList.size() - 1);
    }

    // ----------------------------------------------------------------
    // Send flow
    // ----------------------------------------------------------------

    private void sendMessage() {
        String userText = etMessage.getText().toString().trim();
        if (TextUtils.isEmpty(userText)) return;

        // Clear input and hide keyboard
        etMessage.setText("");
        hideKeyboard();

        // Show user bubble
        addUserMessage(userText);

        // Add to conversation history
        try {
            JSONObject userMsg = new JSONObject();
            userMsg.put("role", "user");
            userMsg.put("content", userText);
            conversationHistory.put(userMsg);
        } catch (Exception e) {
            e.printStackTrace();
        }

        // Show typing indicator and call API
        showTyping(true);
        callClaudeApi();
    }

    // ----------------------------------------------------------------
    // Claude API call
    // ----------------------------------------------------------------

    private void callClaudeApi() {
        try {
            JSONObject requestBody = new JSONObject();
            requestBody.put("model",      MODEL);
            requestBody.put("max_tokens", 1024);
            requestBody.put("system",     SYSTEM_PROMPT);
            requestBody.put("messages",   conversationHistory);

            RequestBody body = RequestBody.create(
                    requestBody.toString(),
                    MediaType.parse("application/json; charset=utf-8")
            );

            Request request = new Request.Builder()
                    .url(API_URL)
                    .header("x-api-key",         ANTHROPIC_API_KEY)
                    .header("anthropic-version", "2023-06-01")
                    .header("Content-Type",      "application/json")
                    .post(body)
                    .build();

            httpClient.newCall(request).enqueue(new Callback() {
                @Override
                public void onFailure(@NonNull Call call, @NonNull IOException e) {
                    mainHandler.post(() -> {
                        showTyping(false);
                        addAiMessage("I'm sorry, I couldn't connect right now. Please check your internet and try again 💜");
                    });
                }

                @Override
                public void onResponse(@NonNull Call call, @NonNull Response response) throws IOException {
                    String responseBody = response.body() != null ? response.body().string() : "";
                    mainHandler.post(() -> {
                        showTyping(false);
                        try {
                            JSONObject json    = new JSONObject(responseBody);
                            JSONArray  content = json.getJSONArray("content");
                            String     aiText  = content.getJSONObject(0).getString("text");

                            // Add AI reply to conversation history
                            JSONObject assistantMsg = new JSONObject();
                            assistantMsg.put("role",    "assistant");
                            assistantMsg.put("content", aiText);
                            conversationHistory.put(assistantMsg);

                            addAiMessage(aiText);

                        } catch (Exception e) {
                            addAiMessage("I'm having trouble understanding right now. Please try again 💜");
                        }
                    });
                }
            });

        } catch (Exception e) {
            showTyping(false);
            Toast.makeText(this, "Error building request", Toast.LENGTH_SHORT).show();
        }
    }

    // ----------------------------------------------------------------
    // UI helpers
    // ----------------------------------------------------------------

    private void showTyping(boolean show) {
        layoutTyping.setVisibility(show ? View.VISIBLE : View.GONE);
        btnSend.setEnabled(!show);
        btnSend.setAlpha(show ? 0.5f : 1f);
    }

    private void hideKeyboard() {
        InputMethodManager imm = (InputMethodManager) getSystemService(INPUT_METHOD_SERVICE);
        View currentFocus = getCurrentFocus();
        if (imm != null && currentFocus != null) {
            imm.hideSoftInputFromWindow(currentFocus.getWindowToken(), 0);
        }
    }
}