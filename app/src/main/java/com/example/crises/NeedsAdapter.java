package com.example.crises;

import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
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

        TextView name, location, category, status, address, contact, hours, notes;
        ImageView icon;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);

            name = itemView.findViewById(R.id.resource_name);
            location = itemView.findViewById(R.id.location);
            category = itemView.findViewById(R.id.category);
            status = itemView.findViewById(R.id.status);
            address = itemView.findViewById(R.id.address);
            contact = itemView.findViewById(R.id.contact);
            hours = itemView.findViewById(R.id.hours);
            notes = itemView.findViewById(R.id.notes);
            icon = itemView.findViewById(R.id.category_icon);
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

        Need r = list.get(position);

        holder.name.setText(r.getName());
        holder.location.setText(r.getLocation());
        holder.category.setText(r.getCategory());

        holder.address.setText("📍 " + r.getAddress());
        holder.contact.setText("📞 " + r.getContact());
        holder.hours.setText("🕒 " + r.getHours());
        holder.notes.setText(r.getNotes());

        String status = r.getStatus();

        if (status != null) {

            switch (status.toLowerCase()) {

                case "fulfilled":
                    holder.status.setText("FULFILLED");
                    holder.status.setBackgroundColor(Color.parseColor("#4CAF50"));
                    break;

                case "in_progress":
                    holder.status.setText("IN PROGRESS");
                    holder.status.setBackgroundColor(Color.parseColor("#FF9800"));
                    break;

                case "rejected":
                    holder.status.setText("REJECTED");
                    holder.status.setBackgroundColor(Color.parseColor("#F44336"));
                    break;

                default:
                    holder.status.setText(status.toUpperCase());
                    holder.status.setBackgroundColor(Color.GRAY);
                    break;
            }

            holder.status.setTextColor(Color.WHITE);
            holder.status.setPadding(20, 6, 20, 6);
        }

        holder.icon.setImageResource(getCategoryIcon(r.getCategory()));
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    private int getCategoryIcon(String category) {

        if (category == null) return R.drawable.ic_category_other;

        switch (category.toLowerCase()) {

            case "food": return R.drawable.ic_food;
            case "water": return R.drawable.ic_water;
            case "medical": return R.drawable.ic_medical;
            case "fuel": return R.drawable.ic_fuel;
            case "transport": return R.drawable.ic_transport;
            case "clothes": return R.drawable.ic_clothes;

            default: return R.drawable.ic_category_other;
        }
    }
}