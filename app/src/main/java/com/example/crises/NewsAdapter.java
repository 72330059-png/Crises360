package com.example.crises;

import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.drawable.ColorDrawable;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.cardview.widget.CardView;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.resource.bitmap.CenterCrop;
import com.bumptech.glide.load.resource.bitmap.RoundedCorners;
import com.bumptech.glide.request.RequestOptions;

import java.util.List;

public class NewsAdapter extends RecyclerView.Adapter<NewsAdapter.ViewHolder> {

    // ── Change this to your server's base URL for images ──────────────────
    private static final String IMAGE_BASE_URL = "http://10.0.2.2/crises_api/uploads/";

    List<Newsss> list;
    Context context;

    public NewsAdapter(Context context, List<Newsss> list) {
        this.context = context;
        this.list    = list;
    }

    public static class ViewHolder extends RecyclerView.ViewHolder {
        // Featured card views
        CardView cardFeatured;
        View featuredHeader, divider;
        ImageView imgFeatured;
        TextView tvFeaturedCategory, tvFeaturedTitle, tvFeaturedDate, tvFeaturedViews;

        // Small card views
        View cardSmall;
        ImageView thumbImage;
        TextView tvSmallCategory, tvSmallTitle, tvSmallDate, tvSmallViews;

        public ViewHolder(@NonNull View v) {
            super(v);
            // Featured
            cardFeatured       = v.findViewById(R.id.cardFeatured);
            featuredHeader     = v.findViewById(R.id.featuredHeader);
            imgFeatured        = v.findViewById(R.id.imgFeatured);
            tvFeaturedCategory = v.findViewById(R.id.tvFeaturedCategory);
            tvFeaturedTitle    = v.findViewById(R.id.tvFeaturedTitle);
            tvFeaturedDate     = v.findViewById(R.id.tvFeaturedDate);
            tvFeaturedViews    = v.findViewById(R.id.tvFeaturedViews);
            divider            = v.findViewById(R.id.divider);

            // Small
            cardSmall       = v.findViewById(R.id.cardSmall);
            thumbImage      = v.findViewById(R.id.thumbImage);   // was thumbColor
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
        Newsss news  = list.get(position);
        int color    = getCategoryColor(news.getCategory());
        boolean isFeatured = (position == 0 || news.getFeatured() == 1);

        if (isFeatured && position == 0) {
            // ── FEATURED CARD ─────────────────────────────────────────────
            h.cardFeatured.setVisibility(View.VISIBLE);
            h.cardSmall.setVisibility(View.GONE);
            h.divider.setVisibility(View.GONE);

            // Always set background color as fallback
            h.featuredHeader.setBackgroundColor(color);

            // Load image into featured header ImageView
            loadImage(news.getImage(), h.imgFeatured, color, 0);

            h.tvFeaturedCategory.setText(news.getCategory() != null
                    ? news.getCategory().toUpperCase() : "NEWS");
            h.tvFeaturedTitle.setText(news.getTitle());
            h.tvFeaturedDate.setText(formatDate(news.getPublishDate()));
            if (h.tvFeaturedViews != null)
                h.tvFeaturedViews.setText(news.getViews() + " views");

            h.cardFeatured.setOnClickListener(v -> openDetail(news));

        } else {
            // ── SMALL CARD ────────────────────────────────────────────────
            h.cardFeatured.setVisibility(View.GONE);
            h.cardSmall.setVisibility(View.VISIBLE);
            h.divider.setVisibility(
                    position < list.size() - 1 ? View.VISIBLE : View.GONE);

            // Load image into small thumbnail ImageView
            loadImage(news.getImage(), h.thumbImage, color, 10);

            h.tvSmallCategory.setText(news.getCategory() != null
                    ? news.getCategory().toUpperCase() : "");
            h.tvSmallTitle.setText(news.getTitle());
            h.tvSmallDate.setText(formatDate(news.getPublishDate()));
            if (h.tvSmallViews != null)
                h.tvSmallViews.setText(news.getViews() + " views");

            h.cardSmall.setOnClickListener(v -> openDetail(news));
        }
    }

    /**
     * Loads an image URL into an ImageView using Glide.
     * Falls back to a solid color if image is null/empty or fails to load.
     *
     * @param imageName  filename from DB (e.g. "photo.jpg") OR full URL
     * @param imageView  target ImageView
     * @param colorFallback color int to show while loading / on error
     * @param roundingRadius corner radius in dp (0 = no rounding)
     */
    private void loadImage(String imageName, ImageView imageView,
                           int colorFallback, int roundingRadius) {
        if (imageName == null || imageName.isEmpty()) {
            imageView.setBackgroundColor(colorFallback);
            imageView.setImageDrawable(null);
            return;
        }

        // If DB already stores a full URL use it directly; otherwise prepend base
        String fullUrl = imageName; // already a full URL from PHP

        RequestOptions options = new RequestOptions()
                .placeholder(new ColorDrawable(colorFallback))
                .error(new ColorDrawable(colorFallback));

        if (roundingRadius > 0) {
            options = options.transform(
                    new CenterCrop(),
                    new RoundedCorners(dpToPx(roundingRadius)));
        } else {
            options = options.centerCrop();
        }

        Glide.with(context)
                .load(fullUrl)
                .apply(options)
                .into(imageView);
    }

    private int dpToPx(int dp) {
        float density = context.getResources().getDisplayMetrics().density;
        return Math.round(dp * density);
    }

    private void openDetail(Newsss news) {
        Intent intent = new Intent(context, NewsDetailActivity.class);
        intent.putExtra("title",    news.getTitle());
        intent.putExtra("content",  news.getContent());
        intent.putExtra("category", news.getCategory());
        intent.putExtra("date",     formatDate(news.getPublishDate()));
        intent.putExtra("type",     news.getType());
        intent.putExtra("views",    news.getViews());
        intent.putExtra("image",    news.getImage());   // pass image to detail too
        context.startActivity(intent);
    }

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