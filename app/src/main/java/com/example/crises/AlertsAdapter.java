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

        holder.txtType.setText(alert.getType());
        holder.txtMessage.setText(alert.getMessage());
        holder.txtLocation.setText(alert.getLocation());
        holder.txtTime.setText(alert.getTime());

        switch (alert.getType()) {

            case "Danger":
                holder.strip.setBackgroundColor(Color.RED);
                holder.txtType.setText("🚨 Danger");
                break;

            case "Safe":
                holder.strip.setBackgroundColor(Color.GREEN);
                holder.txtType.setText("🟢 Safe Zone");
                break;

            case "Shelter":
                holder.strip.setBackgroundColor(Color.BLUE);
                holder.txtType.setText("🏠 Shelter");
                break;

            default:
                holder.strip.setBackgroundColor(Color.GRAY);
                holder.txtType.setText("ℹ️ Info");
        }
    }

    @Override
    public int getItemCount() {
        return list.size();
    }
}