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

        TextView need_name, location, category, status, quantity, priority;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);

            need_name = itemView.findViewById(R.id.need_name);
            location = itemView.findViewById(R.id.location);
            category = itemView.findViewById(R.id.category);
            status = itemView.findViewById(R.id.status);
            quantity = itemView.findViewById(R.id.quantity);
            priority = itemView.findViewById(R.id.priority);
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

        holder.need_name.setText(need.getName());
        holder.location.setText(need.getLocation());
        holder.category.setText(need.getCategory());
        holder.status.setText(need.getStatus());
        holder.quantity.setText("Quantity: " + need.getQuantity());
        holder.priority.setText("Priority: " + need.getPriority());

        // STATUS COLOR
        if (need.getStatus() != null &&
                need.getStatus().equalsIgnoreCase("Pending")) {
            holder.status.setTextColor(Color.parseColor("#FF9800"));
        } else {
            holder.status.setTextColor(Color.parseColor("#2E7D32"));
        }

        // PRIORITY COLOR
        if (need.getPriority() != null) {
            switch (need.getPriority().toLowerCase()) {
                case "high":
                    holder.priority.setTextColor(Color.RED);
                    break;
                case "medium":
                    holder.priority.setTextColor(Color.parseColor("#FF9800"));
                    break;
                default:
                    holder.priority.setTextColor(Color.parseColor("#2E7D32"));
                    break;
            }
        }
    }

    @Override
    public int getItemCount() {
        return list.size();
    }
}