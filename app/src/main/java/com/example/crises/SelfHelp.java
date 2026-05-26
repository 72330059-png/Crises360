package com.example.crises;

import android.animation.AnimatorSet;
import android.animation.ObjectAnimator;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.os.CountDownTimer;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.FrameLayout;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;

import com.example.crises.R;

public class SelfHelp extends AppCompatActivity {

    // ---- Breathing ----
    private CardView    cardBreathing;
    private Button      btnStartBreathing;
    private FrameLayout breathingCircle;
    private TextView    tvBreathingInstruction;
    private TextView    tvBreathingCount;

    // ---- Grounding ----
    private CardView    cardGrounding;
    private Button      btnStartGrounding;
    private LinearLayout layoutGroundingSteps;

    // ---- Journal ----
    private Button   btnSaveJournal;
    private EditText etJournalEntry;

    // Mood selection
    private TextView selectedMoodView = null;
    private String   selectedMood     = "";

    // Breathing state
    private CountDownTimer breathingTimer;
    private boolean        isBreathingActive = false;

    // 4-7-8 pattern: inhale 4s, hold 7s, exhale 8s
    private static final int[] PHASE_DURATIONS    = {4, 7, 8};
    private static final String[] PHASE_LABELS    = {"Inhale", "Hold", "Exhale"};
    private int currentPhase = 0;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_self_help);

        // Back
        findViewById(R.id.btnBack).setOnClickListener(v -> finish());

        setupBreathing();
        setupGrounding();
        setupJournal();
    }

    // ----------------------------------------------------------------
    // 4-7-8 Breathing Exercise
    // ----------------------------------------------------------------

    private void setupBreathing() {
        cardBreathing          = findViewById(R.id.cardBreathing);
        btnStartBreathing      = findViewById(R.id.btnStartBreathing);
        breathingCircle        = findViewById(R.id.breathingCircle);
        tvBreathingInstruction = findViewById(R.id.tvBreathingInstruction);
        tvBreathingCount       = findViewById(R.id.tvBreathingCount);

        btnStartBreathing.setOnClickListener(v -> {
            if (!isBreathingActive) {
                startBreathingExercise();
            } else {
                stopBreathingExercise();
            }
        });
    }

    private void startBreathingExercise() {
        isBreathingActive = true;
        currentPhase      = 0;
        breathingCircle.setVisibility(View.VISIBLE);
        btnStartBreathing.setText("Stop Exercise");
        runBreathingPhase();
    }

    private void runBreathingPhase() {
        if (!isBreathingActive) return;

        int    duration = PHASE_DURATIONS[currentPhase];
        String label    = PHASE_LABELS[currentPhase];

        tvBreathingInstruction.setText(label);
        tvBreathingCount.setText(String.valueOf(duration));

        // Animate circle scale: inhale = expand, exhale = shrink, hold = pulse
        float targetScale = (currentPhase == 0) ? 1.3f : (currentPhase == 2) ? 0.85f : 1.1f;
        ObjectAnimator scaleX = ObjectAnimator.ofFloat(breathingCircle, "scaleX", breathingCircle.getScaleX(), targetScale);
        ObjectAnimator scaleY = ObjectAnimator.ofFloat(breathingCircle, "scaleY", breathingCircle.getScaleY(), targetScale);
        scaleX.setDuration(duration * 1000L);
        scaleY.setDuration(duration * 1000L);
        AnimatorSet animSet = new AnimatorSet();
        animSet.playTogether(scaleX, scaleY);
        animSet.start();

        // Countdown timer for this phase
        breathingTimer = new CountDownTimer(duration * 1000L, 1000) {
            @Override
            public void onTick(long millisUntilFinished) {
                tvBreathingCount.setText(String.valueOf((int) Math.ceil(millisUntilFinished / 1000.0)));
            }

            @Override
            public void onFinish() {
                currentPhase = (currentPhase + 1) % 3;
                runBreathingPhase();
            }
        }.start();
    }

    private void stopBreathingExercise() {
        isBreathingActive = false;
        if (breathingTimer != null) breathingTimer.cancel();
        breathingCircle.setVisibility(View.GONE);
        breathingCircle.setScaleX(1f);
        breathingCircle.setScaleY(1f);
        btnStartBreathing.setText("Start Exercise");
        Toast.makeText(this, "Great job! Take a moment to notice how you feel 💜", Toast.LENGTH_LONG).show();
    }

    // ----------------------------------------------------------------
    // 5-4-3-2-1 Grounding Exercise
    // ----------------------------------------------------------------

    private void setupGrounding() {
        cardGrounding        = findViewById(R.id.cardGrounding);
        btnStartGrounding    = findViewById(R.id.btnStartGrounding);
        layoutGroundingSteps = findViewById(R.id.layoutGroundingSteps);

        btnStartGrounding.setOnClickListener(v -> {
            boolean isVisible = layoutGroundingSteps.getVisibility() == View.VISIBLE;
            if (isVisible) {
                layoutGroundingSteps.setVisibility(View.GONE);
                btnStartGrounding.setText("Start Exercise");
            } else {
                layoutGroundingSteps.setVisibility(View.VISIBLE);
                btnStartGrounding.setText("Hide Exercise");
                // Animate steps in with stagger
                animateSteps();
            }
        });
    }

    private void animateSteps() {
        int childCount = layoutGroundingSteps.getChildCount();
        for (int i = 0; i < childCount; i++) {
            View step = layoutGroundingSteps.getChildAt(i);
            step.setAlpha(0f);
            step.setTranslationX(-40f);
            step.animate()
                    .alpha(1f)
                    .translationX(0f)
                    .setStartDelay(i * 150L)
                    .setDuration(300)
                    .start();
        }
    }

    // ----------------------------------------------------------------
    // Mood Journal
    // ----------------------------------------------------------------

    private void setupJournal() {
        btnSaveJournal = findViewById(R.id.btnSaveJournal);
        etJournalEntry = findViewById(R.id.etJournalEntry);

        // Mood emoji selection
        int[] moodIds = {R.id.mood1, R.id.mood2, R.id.mood3, R.id.mood4, R.id.mood5};
        String[] moods = {"Very Sad", "Sad", "Neutral", "Happy", "Very Happy"};

        for (int i = 0; i < moodIds.length; i++) {
            final String moodLabel = moods[i];
            TextView moodView = findViewById(moodIds[i]);
            moodView.setOnClickListener(v -> {
                // Deselect previous
                if (selectedMoodView != null) {
                    selectedMoodView.animate().scaleX(1f).scaleY(1f).setDuration(150).start();
                    selectedMoodView.setAlpha(0.6f);
                }
                // Select current
                selectedMoodView = moodView;
                selectedMood     = moodLabel;
                moodView.setAlpha(1f);
                moodView.animate().scaleX(1.3f).scaleY(1.3f).setDuration(150).start();
            });
            moodView.setAlpha(0.6f);
        }

        btnSaveJournal.setOnClickListener(v -> saveJournalEntry());
    }

    private void saveJournalEntry() {
        String entry = etJournalEntry.getText().toString().trim();

        if (selectedMood.isEmpty()) {
            Toast.makeText(this, "Please select how you're feeling today", Toast.LENGTH_SHORT).show();
            return;
        }

        // Save to SharedPreferences (simple local storage)
        SharedPreferences prefs    = getSharedPreferences("MoodJournal", MODE_PRIVATE);
        String            dateKey  = new java.text.SimpleDateFormat("yyyy-MM-dd", java.util.Locale.getDefault()).format(new java.util.Date());
        String            record   = selectedMood + "|" + entry;

        prefs.edit().putString(dateKey, record).apply();

        Toast.makeText(this, "Journal entry saved 💜 Keep checking in with yourself!", Toast.LENGTH_LONG).show();

        // Reset
        etJournalEntry.setText("");
        if (selectedMoodView != null) {
            selectedMoodView.animate().scaleX(1f).scaleY(1f).setDuration(150).start();
            selectedMoodView.setAlpha(0.6f);
            selectedMoodView = null;
        }
        selectedMood = "";
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        if (breathingTimer != null) breathingTimer.cancel();
    }
}