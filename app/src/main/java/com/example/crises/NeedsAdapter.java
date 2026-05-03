package com.example.crises;

import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

public class NeedsAdapter extends RecyclerView.Adapter<NeedsAdapter.ViewHolder> {

    List<Need> list;

    public NeedsAdapter(List<Need> list) {
        this.list = list;
    }

    public static class ViewHolder extends RecyclerView.ViewHolder {

        TextView name, location, type, status, availability;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);

            name = itemView.findViewById(R.id.name);
            location = itemView.findViewById(R.id.location);
            type = itemView.findViewById(R.id.type);
            status = itemView.findViewById(R.id.status);
            availability = itemView.findViewById(R.id.availability);
        }
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_need, parent, false);
        return new ViewHolder(v);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {

        Need need = list.get(position);

        holder.name.setText(need.getName());
        holder.location.setText(need.getLocation());
        holder.type.setText(need.getType());
        holder.status.setText(need.getStatus());
        holder.availability.setText(need.getAvailability());

        // 🟢 STATUS COLOR
        if (need.getStatus().equalsIgnoreCase("OPEN")) {
            holder.status.setTextColor(Color.parseColor("#2E7D32"));
        } else {
            holder.status.setTextColor(Color.RED);
        }

        // 📦 AVAILABILITY COLOR
        switch (need.getAvailability().toLowerCase()) {
            case "available":
                holder.availability.setTextColor(Color.parseColor("#2E7D32"));
                break;
            case "limited":
                holder.availability.setTextColor(Color.parseColor("#FF9800"));
                break;
            default:
                holder.availability.setTextColor(Color.RED);
        }
    }

    @Override
    public int getItemCount() {
        return list.size();
    }
}