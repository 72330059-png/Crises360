package com.example.crises;

import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.cardview.widget.CardView;
import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

public class NewsAdapter extends RecyclerView.Adapter<NewsAdapter.ViewHolder> {

    List<Newsss> list;
    Context context;

    public NewsAdapter(Context context, List<Newsss> list) {
        this.context = context;
        this.list    = list;
    }

    public static class ViewHolder extends RecyclerView.ViewHolder {
        CardView cardFeatured;
        View featuredHeader, cardSmall, thumbColor, divider;
        TextView tvFeaturedCategory, tvFeaturedTitle, tvFeaturedDate;
        TextView tvSmallCategory, tvSmallTitle, tvSmallDate;
        // Views counter
        TextView tvFeaturedViews, tvSmallViews;

        public ViewHolder(@NonNull View v) {
            super(v);
            cardFeatured       = v.findViewById(R.id.cardFeatured);
            featuredHeader     = v.findViewById(R.id.featuredHeader);
            tvFeaturedCategory = v.findViewById(R.id.tvFeaturedCategory);
            tvFeaturedTitle    = v.findViewById(R.id.tvFeaturedTitle);
            tvFeaturedDate     = v.findViewById(R.id.tvFeaturedDate);
            tvFeaturedViews    = v.findViewById(R.id.tvFeaturedViews);
            cardSmall          = v.findViewById(R.id.cardSmall);
            thumbColor         = v.findViewById(R.id.thumbColor);
            tvSmallCategory    = v.findViewById(R.id.tvSmallCategory);
            tvSmallTitle       = v.findViewById(R.id.tvSmallTitle);
            tvSmallDate        = v.findViewById(R.id.tvSmallDate);
            tvSmallViews       = v.findViewById(R.id.tvSmallViews);
            divider            = v.findViewById(R.id.divider);
        }
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_news, parent, false);
        return new ViewHolder(v);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder h, int position) {
        Newsss news = list.get(position);
        int color   = getCategoryColor(news.getCategory());

        // Featured card = first item OR items with featured=1
        boolean isFeatured = (position == 0 || news.getFeatured() == 1);

        if (isFeatured && position == 0) {
            h.cardFeatured.setVisibility(View.VISIBLE);
            h.cardSmall.setVisibility(View.GONE);
            h.divider.setVisibility(View.GONE);

            h.featuredHeader.setBackgroundColor(color);
            h.tvFeaturedCategory.setText(news.getCategory().toUpperCase());
            h.tvFeaturedTitle.setText(news.getTitle());
            h.tvFeaturedDate.setText(formatDate(news.getPublishDate()));
            if (h.tvFeaturedViews != null)
                h.tvFeaturedViews.setText(news.getViews() + " views");

            h.cardFeatured.setOnClickListener(v -> openDetail(news));

        } else {
            h.cardFeatured.setVisibility(View.GONE);
            h.cardSmall.setVisibility(View.VISIBLE);
            h.divider.setVisibility(
                    position < list.size() - 1 ? View.VISIBLE : View.GONE);

            h.thumbColor.setBackgroundColor(color);
            h.tvSmallCategory.setText(news.getCategory().toUpperCase());
            h.tvSmallTitle.setText(news.getTitle());
            h.tvSmallDate.setText(formatDate(news.getPublishDate()));
            if (h.tvSmallViews != null)
                h.tvSmallViews.setText(news.getViews() + " views");

            h.cardSmall.setOnClickListener(v -> openDetail(news));
        }
    }

    private void openDetail(Newsss news) {
        Intent intent = new Intent(context, NewsDetailActivity.class);
        intent.putExtra("title",    news.getTitle());
        intent.putExtra("content",  news.getContent());
        intent.putExtra("category", news.getCategory());
        intent.putExtra("date",     formatDate(news.getPublishDate()));
        intent.putExtra("type",     news.getType());
        intent.putExtra("views",    news.getViews());
        context.startActivity(intent);
    }

    // Format "2026-05-24 17:00:00" → "May 24, 2026"
    private String formatDate(String raw) {
        if (raw == null || raw.isEmpty()) return "";
        try {
            java.text.SimpleDateFormat input  =
                    new java.text.SimpleDateFormat("yyyy-MM-dd HH:mm:ss",
                            java.util.Locale.getDefault());
            java.text.SimpleDateFormat output =
                    new java.text.SimpleDateFormat("MMM dd, yyyy",
                            java.util.Locale.getDefault());
            return output.format(input.parse(raw));
        } catch (Exception e) {
            return raw;
        }
    }

    private int getCategoryColor(String category) {
        if (category == null) return Color.parseColor("#1E3A5F");
        switch (category.toLowerCase()) {
            case "alert":   case "warning":  return Color.parseColor("#A32D2D");
            case "relief":  case "aid":      return Color.parseColor("#3B6D11");
            case "health":  case "medical":  return Color.parseColor("#0C447C");
            case "shelter": case "housing":  return Color.parseColor("#854F0B");
            default:                         return Color.parseColor("#1E3A5F");
        }
    }

    @Override
    public int getItemCount() { return list.size(); }
}