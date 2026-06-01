package com.example.crises;

import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;

public class AlertsAdapter extends RecyclerView.Adapter<AlertsAdapter.ViewHolder> {

    ArrayList<AlertModel> list;

    public AlertsAdapter(ArrayList<AlertModel> list) {
        this.list = list;
    }

    public static class ViewHolder extends RecyclerView.ViewHolder {
        TextView txtType, txtMessage, txtLocation, txtTime;
        View     strip;

        public ViewHolder(View itemView) {
            super(itemView);
            txtType     = itemView.findViewById(R.id.txtType);
            txtMessage  = itemView.findViewById(R.id.txtMessage);
            txtLocation = itemView.findViewById(R.id.txtLocation);
            txtTime     = itemView.findViewById(R.id.txtTime);
            strip       = itemView.findViewById(R.id.priorityStrip);
        }
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_alert, parent, false);
        return new ViewHolder(v);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        AlertModel alert = list.get(position);

        holder.txtMessage.setText(alert.getMessage());
        holder.txtLocation.setText("📍 " + alert.getLocation());
        holder.txtTime.setText(alert.getTime());

        String severity = alert.getSeverity() != null
                ? alert.getSeverity() : "";

        switch (severity.toLowerCase()) {
            case "warning":
                applyStyle(holder,
                        "⚠️ Warning",
                        "#F59E0B",
                        "#F59E0B",
                        R.drawable.badge_warning_bg);
                break;

            case "info":
                applyStyle(holder,
                        "📢 Info",
                        "#2F66F6",
                        "#2F66F6",
                        R.drawable.badge_update_bg);
                break;

            case "critical":
            default:
                applyStyle(holder,
                        "🚨 Critical",
                        "#EF4444",
                        "#EF4444",
                        R.drawable.badge_danger_bg);
                break;
        }
    }

    private void applyStyle(ViewHolder holder,
                            String label,
                            String stripColor,
                            String textColor,
                            int badgeBackground) {
        holder.txtType.setText(label);
        holder.txtType.setTextColor(Color.parseColor(textColor));
        holder.txtType.setBackgroundResource(badgeBackground);
        holder.strip.setBackgroundColor(Color.parseColor(stripColor));
    }

    @Override
    public int getItemCount() {
        return list != null ? list.size() : 0;
    }
}