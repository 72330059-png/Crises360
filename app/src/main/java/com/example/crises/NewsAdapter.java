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

        TextView title, description, source, location, type, date, severity;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);

            title = itemView.findViewById(R.id.title);
            description = itemView.findViewById(R.id.description);
            source = itemView.findViewById(R.id.source);
            location = itemView.findViewById(R.id.location);
            type = itemView.findViewById(R.id.type);
            date = itemView.findViewById(R.id.date);
            severity = itemView.findViewById(R.id.severity);
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
        holder.description.setText(news.getDescription());
        holder.source.setText("Source: " + news.getSource());
        holder.location.setText("Location: " + news.getLocation());
        holder.type.setText("Type: " + news.getType());
        holder.date.setText(news.getPubDate());

        holder.severity.setText("Severity: " + news.getSeverity());

        // Color severity
        switch (news.getSeverity()) {
            case "HIGH":
                holder.severity.setTextColor(Color.RED);
                break;
            case "MEDIUM":
                holder.severity.setTextColor(Color.parseColor("#FF9800"));
                break;
            case "LOW":
                holder.severity.setTextColor(Color.GREEN);
                break;
            default:
                holder.severity.setTextColor(Color.GRAY);
        }
    }

    @Override
    public int getItemCount() {
        return list.size();
    }
}