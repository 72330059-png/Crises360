package com.example.crises;

import android.graphics.Color;
import android.os.Bundle;
import android.view.View;
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

        // ── BACK ──────────────────────────────────────────────
        CardView btnBack = findViewById(R.id.btnBack);
        btnBack.setOnClickListener(v -> finish());

        // ── HERO COLOR ────────────────────────────────────────
        View hero = findViewById(R.id.heroBackground);
        hero.setBackgroundColor(getCategoryColor(category));

        // ── TEXTS ─────────────────────────────────────────────
        ((TextView) findViewById(R.id.tvDetailCategory))
                .setText(category != null ? category.toUpperCase() : "NEWS");
        ((TextView) findViewById(R.id.tvDetailTitle)).setText(title);
        ((TextView) findViewById(R.id.tvDetailContent)).setText(content);

        // Date + views
        TextView tvDate = findViewById(R.id.tvDetailDate);
        tvDate.setText(date + "  ·  " + views + " views");

        // ── TAGS ──────────────────────────────────────────────
        LinearLayout tagsRow = findViewById(R.id.tagsRow);
        if (category != null && !category.isEmpty())
            addTag(tagsRow, "#" + category, false);
        if (type != null && !type.isEmpty())
            addTag(tagsRow, "#" + type,
                    type.equalsIgnoreCase("alert")
                            || type.equalsIgnoreCase("breaking"));
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

        android.graphics.drawable.GradientDrawable bg =
                new android.graphics.drawable.GradientDrawable();
        bg.setShape(android.graphics.drawable.GradientDrawable.RECTANGLE);
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

    private int getCategoryColor(String cat) {
        if (cat == null) return Color.parseColor("#1E3A5F");
        switch (cat.toLowerCase()) {
            case "alert":   case "warning":  return Color.parseColor("#A32D2D");
            case "relief":  case "aid":      return Color.parseColor("#3B6D11");
            case "health":  case "medical":  return Color.parseColor("#0C447C");
            case "shelter": case "housing":  return Color.parseColor("#854F0B");
            default:                         return Color.parseColor("#1E3A5F");
        }
    }
}