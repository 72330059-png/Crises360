package com.example.crises;

import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.drawable.GradientDrawable;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
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
        View featuredHeader, divider;
        ImageView imgFeatured;
        TextView tvFeaturedCategory, tvFeaturedTitle, tvFeaturedDate, tvFeaturedViews;

        View cardSmall;
        ImageView thumbImage;
        TextView tvSmallCategory, tvSmallTitle, tvSmallDate, tvSmallViews;

        public ViewHolder(@NonNull View v) {
            super(v);
            cardFeatured       = v.findViewById(R.id.cardFeatured);
            featuredHeader     = v.findViewById(R.id.featuredHeader);
            imgFeatured        = v.findViewById(R.id.imgFeatured);
            tvFeaturedCategory = v.findViewById(R.id.tvFeaturedCategory);
            tvFeaturedTitle    = v.findViewById(R.id.tvFeaturedTitle);
            tvFeaturedDate     = v.findViewById(R.id.tvFeaturedDate);
            tvFeaturedViews    = v.findViewById(R.id.tvFeaturedViews);
            divider            = v.findViewById(R.id.divider);

            cardSmall       = v.findViewById(R.id.cardSmall);
            thumbImage      = v.findViewById(R.id.thumbImage);
            tvSmallCategory = v.findViewById(R.id.tvSmallCategory);
            tvSmallTitle    = v.findViewById(R.id.tvSmallTitle);
            tvSmallDate     = v.findViewById(R.id.tvSmallDate);
            tvSmallViews    = v.findViewById(R.id.tvSmallViews);
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
        Newsss news      = list.get(position);
        int color        = getCategoryColor(news.getCategory());
        boolean featured = (position == 0);

        if (featured) {
            h.cardFeatured.setVisibility(View.VISIBLE);
            h.cardSmall.setVisibility(View.GONE);
            h.divider.setVisibility(View.GONE);

            h.featuredHeader.setBackgroundColor(color);
            setIconPlaceholder(h.imgFeatured, news.getCategory(), false);

            h.tvFeaturedCategory.setText(
                    news.getCategory() != null ? news.getCategory().toUpperCase() : "NEWS");
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

            setIconPlaceholder(h.thumbImage, news.getCategory(), true);

            h.tvSmallCategory.setText(
                    news.getCategory() != null ? news.getCategory().toUpperCase() : "");
            h.tvSmallTitle.setText(news.getTitle());
            h.tvSmallDate.setText(formatDate(news.getPublishDate()));
            if (h.tvSmallViews != null)
                h.tvSmallViews.setText(news.getViews() + " views");

            h.cardSmall.setOnClickListener(v -> openDetail(news));
        }
    }

    /**
     * Sets a colored background + category icon on an ImageView.
     * No Glide, no network — purely local drawables.
     *
     * @param imageView  target view
     * @param category   news category string
     * @param isSmall    true = small thumbnail (more padding, rounded bg)
     */
    private void setIconPlaceholder(ImageView imageView, String category, boolean isSmall) {
        int bgColor = getCategoryColor(category);
        int iconRes = getCategoryIcon(category);

        // Colored background shape
        GradientDrawable bg = new GradientDrawable();
        bg.setShape(GradientDrawable.RECTANGLE);
        bg.setColor(bgColor);
        if (isSmall) bg.setCornerRadius(dpToPx(10));

        imageView.setBackground(bg);
        imageView.setImageResource(iconRes);
        // Soft white tint so the icon feels like a watermark
        imageView.setColorFilter(Color.argb(180, 255, 255, 255));
        imageView.setScaleType(ImageView.ScaleType.CENTER_INSIDE);

        int pad = isSmall ? dpToPx(14) : dpToPx(36);
        imageView.setPadding(pad, pad, pad, pad);
    }

    private int getCategoryIcon(String category) {
        if (category == null) return R.drawable.ic_news;
        switch (category.toLowerCase()) {
            case "alert":   case "warning":  return R.drawable.ic_alert;
            case "health":  case "medical":  return R.drawable.ic_health;
            case "relief":  case "aid":      return R.drawable.ic_relief;
            case "shelter": case "housing":  return R.drawable.ic_shelter;
            default:                         return R.drawable.ic_news;
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

    private int dpToPx(int dp) {
        return Math.round(dp * context.getResources().getDisplayMetrics().density);
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

    private String formatDate(String raw) {
        if (raw == null || raw.isEmpty()) return "";
        try {
            java.text.SimpleDateFormat in  =
                    new java.text.SimpleDateFormat("yyyy-MM-dd HH:mm:ss", java.util.Locale.getDefault());
            java.text.SimpleDateFormat out =
                    new java.text.SimpleDateFormat("MMM dd, yyyy",       java.util.Locale.getDefault());
            return out.format(in.parse(raw));
        } catch (Exception e) { return raw; }
    }

    @Override
    public int getItemCount() { return list.size(); }
}