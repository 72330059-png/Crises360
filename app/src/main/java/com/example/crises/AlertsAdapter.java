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
        View strip;

        public ViewHolder(View itemView) {
            super(itemView);

            txtType = itemView.findViewById(R.id.txtType);
            txtMessage = itemView.findViewById(R.id.txtMessage);
            txtLocation = itemView.findViewById(R.id.txtLocation);
            txtTime = itemView.findViewById(R.id.txtTime);
            strip = itemView.findViewById(R.id.priorityStrip);
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
        holder.txtLocation.setText(alert.getLocation());
        holder.txtTime.setText(alert.getTime());

        String type = alert.getType();

        if (type != null) {
            type = type.trim().toLowerCase();

            switch (type) {

                case "danger":
                    setStyle(holder, Color.RED, "🚨 Danger");
                    break;

                case "safe":
                    setStyle(holder, Color.GREEN, "🟢 Safe Zone");
                    break;

                case "shelter":
                    setStyle(holder, Color.BLUE, "🏠 Shelter");
                    break;

                default:
                    setStyle(holder, Color.GRAY, "ℹ️ Info");
                    break;
            }
        } else {
            setStyle(holder, Color.GRAY, "ℹ️ Info");
        }
    }

    private void setStyle(ViewHolder holder, int color, String label) {
        holder.strip.setBackgroundColor(color);
        holder.txtType.setText(label);
    }

    @Override
    public int getItemCount() {
        return list != null ? list.size() : 0;
    }
}