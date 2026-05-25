package com.example.crises;

import android.animation.ObjectAnimator;
import android.content.res.Resources;
import android.graphics.Color;
import android.os.Handler;
import android.os.Looper;
import android.view.View;
import android.view.animation.DecelerateInterpolator;
import android.widget.ProgressBar;
import android.widget.TextView;

public class QuoteTickerManager {

    private static final int INTERVAL_MS       = 8000;   // 8 seconds per quote
    private static final int FADE_DURATION_MS  = 500;    // fade in/out duration

    // Accent colors matching each quote theme
    private static final int[] ACCENT_COLORS = {
            Color.parseColor("#378ADD"),  // Blue   – Resilience
            Color.parseColor("#1D9E75"),  // Teal   – Awareness
            Color.parseColor("#D85A30"),  // Coral  – Preparedness
            Color.parseColor("#7F77DD"),  // Purple – Community
            Color.parseColor("#BA7517"),  // Amber  – Courage
            Color.parseColor("#185FA5"),  // Dark Blue – Communication
            Color.parseColor("#639922"),  // Green  – Mindset
            Color.parseColor("#D4537E"),  // Pink   – Support
    };

    private final String[]      quotes;
    private final String[]      authors;
    private final TextView      tvQuoteText;
    private final TextView      tvQuoteAuthor;
    private final View          accentStrip;
    private final ProgressBar   progressBar;
    private final Handler       handler   = new Handler(Looper.getMainLooper());
    private       int           current   = 0;
    private       ObjectAnimator progressAnimator;
    private       boolean       isRunning = false;

    public QuoteTickerManager(Resources resources,
                              TextView tvQuoteText,
                              TextView tvQuoteAuthor,
                              View accentStrip,
                              ProgressBar progressBar) {

        this.tvQuoteText   = tvQuoteText;
        this.tvQuoteAuthor = tvQuoteAuthor;
        this.accentStrip   = accentStrip;
        this.progressBar   = progressBar;

        quotes  = resources.getStringArray(R.array.quotes_text);
        authors = resources.getStringArray(R.array.quotes_author);
    }

    public void start() {
        if (isRunning) return;
        isRunning = true;
        showQuote(current);
        scheduleNext();
    }

    public void stop() {
        isRunning = false;
        handler.removeCallbacksAndMessages(null);
        if (progressAnimator != null) progressAnimator.cancel();
    }

    private void scheduleNext() {
        handler.postDelayed(() -> {
            if (!isRunning) return;
            current = (current + 1) % quotes.length;
            crossFadeTo(current);
            scheduleNext();
        }, INTERVAL_MS);
    }

    private void showQuote(int index) {
        tvQuoteText.setText(quotes[index]);
        tvQuoteAuthor.setText(authors[index]);
        accentStrip.setBackgroundColor(ACCENT_COLORS[index]);
        progressBar.setProgressTintList(
                android.content.res.ColorStateList.valueOf(ACCENT_COLORS[index]));
        animateProgress();
    }

    private void crossFadeTo(int index) {
        // Fade out
        tvQuoteText.animate().alpha(0f).setDuration(FADE_DURATION_MS).withEndAction(() -> {
            showQuote(index);
            // Fade in
            tvQuoteText.animate().alpha(1f).setDuration(FADE_DURATION_MS).start();
        }).start();

        tvQuoteAuthor.animate().alpha(0f).setDuration(FADE_DURATION_MS).withEndAction(() ->
                tvQuoteAuthor.animate().alpha(1f).setDuration(FADE_DURATION_MS).start()
        ).start();
    }

    private void animateProgress() {
        if (progressAnimator != null) progressAnimator.cancel();
        progressBar.setProgress(0);
        progressAnimator = ObjectAnimator.ofInt(progressBar, "progress", 0, 100);
        progressAnimator.setDuration(INTERVAL_MS);
        progressAnimator.setInterpolator(new DecelerateInterpolator());
        progressAnimator.start();
    }
}