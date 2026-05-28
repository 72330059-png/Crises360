package com.example.crises;

import android.graphics.Color;
import android.graphics.drawable.GradientDrawable;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;

public class NewsDetailActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_news_detail);

        String title    = getIntent().getStringExtra("title");
        String content  = getIntent().getStringExtra("content");
        String category = getIntent().getStringExtra("category");
        String date     = getIntent().getStringExtra("date");
        String type     = getIntent().getStringExtra("type");
        int    views    = getIntent().getIntExtra("views", 0);

        // ── BACK ──────────────────────────────────────────────────────────
        CardView btnBack = findViewById(R.id.btnBack);
        btnBack.setOnClickListener(v -> finish());

        // ── HERO ──────────────────────────────────────────────────────────
        View      hero    = findViewById(R.id.heroBackground);
        ImageView heroImg = findViewById(R.id.heroImage);
        int       color   = getCategoryColor(category);

        // Solid color background
        hero.setBackgroundColor(color);

        // Category icon as hero visual
        heroImg.setImageResource(getCategoryIcon(category));
        heroImg.setColorFilter(Color.argb(140, 255, 255, 255)); // subtle watermark
        heroImg.setScaleType(ImageView.ScaleType.CENTER_INSIDE);
        int pad = dpToPx(52);
        heroImg.setPadding(pad, pad, pad, pad);

        // ── TEXTS ─────────────────────────────────────────────────────────
        ((TextView) findViewById(R.id.tvDetailCategory))
                .setText(category != null ? category.toUpperCase() : "NEWS");
        ((TextView) findViewById(R.id.tvDetailTitle)).setText(title);
        ((TextView) findViewById(R.id.tvDetailContent)).setText(content);
        ((TextView) findViewById(R.id.tvDetailDate))
                .setText(date + "  ·  " + views + " views");

        // ── TAGS ──────────────────────────────────────────────────────────
        LinearLayout tagsRow = findViewById(R.id.tagsRow);
        if (category != null && !category.isEmpty())
            addTag(tagsRow, "#" + category, false);
        if (type != null && !type.isEmpty())
            addTag(tagsRow, "#" + type,
                    type.equalsIgnoreCase("alert") || type.equalsIgnoreCase("breaking"));
    }

    private int getCategoryIcon(String category) {
        if (category == null) return R.drawable.ic_general;
        switch (category.toLowerCase()) {
            case "weather":        return R.drawable.ic_weather;
            case "traffic":        return R.drawable.ic_traffic;
            case "safety":         return R.drawable.ic_safety;
            case "medical":        return R.drawable.ic_medical;
            case "infrastructure": return R.drawable.ic_infrastructure;
            case "tech":           return R.drawable.ic_tech;
            case "sports":         return R.drawable.ic_sports;
            case "politics":       return R.drawable.ic_politics;
            case "economy":        return R.drawable.ic_economy;
            default:               return R.drawable.ic_general;
        }
    }

    private int getCategoryColor(String category) {
        if (category == null) return Color.parseColor("#1E3A5F");
        switch (category.toLowerCase()) {
            case "weather":        return Color.parseColor("#1565A8"); // sky blue
            case "traffic":        return Color.parseColor("#B94C00"); // burnt orange
            case "safety":         return Color.parseColor("#2E6B3E"); // forest green
            case "medical":        return Color.parseColor("#0C447C"); // deep blue
            case "infrastructure": return Color.parseColor("#5C3D8F"); // purple
            case "tech":           return Color.parseColor("#1A6B6B"); // teal
            case "sports":         return Color.parseColor("#A3521D"); // amber brown
            case "politics":       return Color.parseColor("#7B2D2D"); // deep red
            case "economy":        return Color.parseColor("#3B5E1E"); // dark green
            default:               return Color.parseColor("#1E3A5F"); // navy
        }
    }

    private void addTag(LinearLayout row, String text, boolean isAlert) {
        TextView tag = new TextView(this);
        LinearLayout.LayoutParams p = new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.WRAP_CONTENT,
                LinearLayout.LayoutParams.WRAP_CONTENT);
        p.setMarginEnd(8);
        tag.setLayoutParams(p);
        tag.setText(text);
        tag.setTextSize(10f);
        tag.setPadding(24, 8, 24, 8);

        GradientDrawable bg = new GradientDrawable();
        bg.setShape(GradientDrawable.RECTANGLE);
        bg.setCornerRadius(40f);
        bg.setColor(isAlert
                ? Color.parseColor("#FCEBEB")
                : Color.parseColor("#EBF3FB"));
        tag.setBackground(bg);
        tag.setTextColor(isAlert
                ? Color.parseColor("#A32D2D")
                : Color.parseColor("#185FA5"));
        row.addView(tag);
    }

    private int dpToPx(int dp) {
        return Math.round(dp * getResources().getDisplayMetrics().density);
    }
}