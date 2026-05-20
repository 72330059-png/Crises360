package com.example.crises;

import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

public class NewsAdapter extends RecyclerView.Adapter<NewsAdapter.ViewHolder> {

    List<Newsss> list;

    public NewsAdapter(List<Newsss> list) {
        this.list = list;
    }

    public static class ViewHolder extends RecyclerView.ViewHolder {

        TextView title, description, type, date;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);

            title = itemView.findViewById(R.id.title);
            description = itemView.findViewById(R.id.description);
            type = itemView.findViewById(R.id.type);
            date = itemView.findViewById(R.id.date);
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
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {

        Newsss news = list.get(position);

        holder.title.setText(news.getTitle());

        // FIX: description → content
        holder.description.setText(news.getContent());

        holder.type.setText(news.getType());

        // FIX: pubDate → publish_date
        holder.date.setText(news.getPublishDate());
    }

    @Override
    public int getItemCount() {
        return list.size();
    }
}