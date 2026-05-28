package com.example.crises;

import android.animation.AnimatorSet;
import android.animation.ObjectAnimator;
import android.os.Bundle;
import android.os.CountDownTimer;
import android.view.View;
import android.widget.Button;
import android.widget.FrameLayout;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

public class SelfHelp extends AppCompatActivity {

    // ── Breathing ──────────────────────────────────────────────────
    private Button      btnStartBreathing;
    private FrameLayout breathingCircle;
    private TextView    tvBreathingInstruction;
    private TextView    tvBreathingCount;
    private CountDownTimer breathingTimer;
    private boolean    isBreathingActive = false;
    private int        currentPhase      = 0;

    private static final int[]    DURATIONS = {4, 7, 8};
    private static final String[] LABELS    = {"Inhale", "Hold", "Exhale"};

    // ── Coping card ────────────────────────────────────────────────
    private LinearLayout layoutCopingSteps;
    private Button       btnShowCoping;
    private boolean      copingVisible = false;

    // ── PTSD card ──────────────────────────────────────────────────
    private LinearLayout layoutPTSDSteps;
    private Button       btnShowPTSD;
    private boolean      ptsdVisible = false;

    // ── Children card ──────────────────────────────────────────────
    private LinearLayout layoutChildrenSteps;
    private Button       btnShowChildren;
    private boolean      childrenVisible = false;

    // ──────────────────────────────────────────────────────────────

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_self_help);

        // Back button
        ImageButton btnBack = findViewById(R.id.btnBack);
        if (btnBack != null) {
            btnBack.setOnClickListener(v -> finish());
        }

        setupBreathing();
        setupCoping();
        setupPTSD();
        setupChildren();
    }

    // ──────────────────────────────────────────────────────────────
    // CARD 1 : 4-7-8 Breathing
    // ──────────────────────────────────────────────────────────────

    private void setupBreathing() {
        btnStartBreathing      = findViewById(R.id.btnStartBreathing);
        breathingCircle        = findViewById(R.id.breathingCircle);
        tvBreathingInstruction = findViewById(R.id.tvBreathingInstruction);
        tvBreathingCount       = findViewById(R.id.tvBreathingCount);

        btnStartBreathing.setOnClickListener(v -> {
            if (isBreathingActive) stopBreathing();
            else startBreathing();
        });
    }

    private void startBreathing() {
        isBreathingActive = true;
        currentPhase = 0;
        breathingCircle.setVisibility(View.VISIBLE);
        btnStartBreathing.setText("Stop Exercise");
        runPhase();
    }

    private void runPhase() {
        if (!isBreathingActive) return;
        int secs = DURATIONS[currentPhase];
        tvBreathingInstruction.setText(LABELS[currentPhase]);
        tvBreathingCount.setText(String.valueOf(secs));

        float target = currentPhase == 0 ? 1.35f : currentPhase == 1 ? 1.1f : 0.8f;
        ObjectAnimator sx = ObjectAnimator.ofFloat(breathingCircle, "scaleX",
                breathingCircle.getScaleX(), target);
        ObjectAnimator sy = ObjectAnimator.ofFloat(breathingCircle, "scaleY",
                breathingCircle.getScaleY(), target);
        sx.setDuration(secs * 1000L);
        sy.setDuration(secs * 1000L);
        AnimatorSet anim = new AnimatorSet();
        anim.playTogether(sx, sy);
        anim.start();

        breathingTimer = new CountDownTimer(secs * 1000L, 1000) {
            @Override
            public void onTick(long ms) {
                tvBreathingCount.setText(String.valueOf((int) Math.ceil(ms / 1000.0)));
            }
            @Override
            public void onFinish() {
                currentPhase = (currentPhase + 1) % 3;
                runPhase();
            }
        }.start();
    }

    private void stopBreathing() {
        isBreathingActive = false;
        if (breathingTimer != null) breathingTimer.cancel();
        breathingCircle.setVisibility(View.GONE);
        breathingCircle.setScaleX(1f);
        breathingCircle.setScaleY(1f);
        btnStartBreathing.setText("Start Exercise");
        Toast.makeText(this,
                "Well done. Notice how your body feels now 💜",
                Toast.LENGTH_LONG).show();
    }

    // ──────────────────────────────────────────────────────────────
    // CARD 2 : Crisis Coping Techniques
    // ──────────────────────────────────────────────────────────────

    private void setupCoping() {
        layoutCopingSteps = findViewById(R.id.layoutCopingSteps);
        btnShowCoping     = findViewById(R.id.btnShowCoping);

        btnShowCoping.setOnClickListener(v -> {
            copingVisible = !copingVisible;
            toggleSection(layoutCopingSteps, copingVisible);
            btnShowCoping.setText(copingVisible ? "Hide Techniques" : "Show Techniques");
        });
    }

    // ──────────────────────────────────────────────────────────────
    // CARD 3 : Trauma / PTSD Awareness
    // ──────────────────────────────────────────────────────────────

    private void setupPTSD() {
        layoutPTSDSteps = findViewById(R.id.layoutPTSDSteps);
        btnShowPTSD     = findViewById(R.id.btnShowPTSD);

        btnShowPTSD.setOnClickListener(v -> {
            ptsdVisible = !ptsdVisible;
            toggleSection(layoutPTSDSteps, ptsdVisible);
            btnShowPTSD.setText(ptsdVisible ? "Hide" : "Learn More");
        });
    }

    // ──────────────────────────────────────────────────────────────
    // CARD 4 : Helping Children
    // ──────────────────────────────────────────────────────────────

    private void setupChildren() {
        layoutChildrenSteps = findViewById(R.id.layoutChildrenSteps);
        btnShowChildren     = findViewById(R.id.btnShowChildren);

        btnShowChildren.setOnClickListener(v -> {
            childrenVisible = !childrenVisible;
            toggleSection(layoutChildrenSteps, childrenVisible);
            btnShowChildren.setText(childrenVisible ? "Hide Guidance" : "Show Guidance");
        });
    }

    // ──────────────────────────────────────────────────────────────
    // Shared expand / collapse with fade + slide animation
    // ──────────────────────────────────────────────────────────────

    private void toggleSection(LinearLayout layout, boolean show) {
        if (show) {
            layout.setVisibility(View.VISIBLE);
            layout.setAlpha(0f);
            layout.setTranslationY(-20f);
            layout.animate()
                    .alpha(1f)
                    .translationY(0f)
                    .setDuration(280)
                    .start();
            for (int i = 0; i < layout.getChildCount(); i++) {
                View child = layout.getChildAt(i);
                child.setAlpha(0f);
                child.setTranslationX(-30f);
                child.animate()
                        .alpha(1f)
                        .translationX(0f)
                        .setStartDelay(i * 80L)
                        .setDuration(250)
                        .start();
            }
        } else {
            layout.animate()
                    .alpha(0f)
                    .translationY(-10f)
                    .setDuration(200)
                    .withEndAction(() -> layout.setVisibility(View.GONE))
                    .start();
        }
    }

    // ──────────────────────────────────────────────────────────────

    @Override
    protected void onDestroy() {
        super.onDestroy();
        if (breathingTimer != null) breathingTimer.cancel();
    }
}