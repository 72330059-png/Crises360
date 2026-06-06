package com.example.crises;

import android.content.Intent;
import android.graphics.Color;
import android.net.Uri;
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
            name     = itemView.findViewById(R.id.resource_name);
            location = itemView.findViewById(R.id.location);
            category = itemView.findViewById(R.id.category);
            status   = itemView.findViewById(R.id.status);
            address  = itemView.findViewById(R.id.address);
            contact  = itemView.findViewById(R.id.contact);
            hours    = itemView.findViewById(R.id.hours);
            notes    = itemView.findViewById(R.id.notes);
            icon     = itemView.findViewById(R.id.category_icon);
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
        holder.category.setText(formatCategory(r.getCategory()));
        holder.address.setText(r.getAddress());
        holder.hours.setText(r.getHours());

        // ── NOTES — hide if empty ─────────────────────────────────────────
        String notes = r.getNotes();
        if (notes != null && !notes.trim().isEmpty()) {
            holder.notes.setText(notes);
            holder.notes.setVisibility(View.VISIBLE);
        } else {
            holder.notes.setVisibility(View.GONE);
        }

        // ── CONTACT — tappable to open dialer ─────────────────────────────
        String phone = r.getContact();
        holder.contact.setText(phone);
        holder.contact.setTextColor(Color.parseColor("#2E7D32"));
        if (phone != null && !phone.trim().isEmpty()) {
            holder.contact.setOnClickListener(v -> {
                Intent intent = new Intent(Intent.ACTION_DIAL);
                intent.setData(Uri.parse("tel:" + phone.trim()));
                v.getContext().startActivity(intent);
            });
        } else {
            holder.contact.setOnClickListener(null);
        }

        // ── STATUS BADGE ──────────────────────────────────────────────────
        String status = r.getStatus();
        if (status != null) {
            switch (status.toLowerCase()) {
                case "active":
                    holder.status.setText("ACTIVE");
                    holder.status.setBackgroundColor(Color.parseColor("#4CAF50"));
                    break;
                case "fulfilled":
                    holder.status.setText("FULFILLED");
                    holder.status.setBackgroundColor(Color.parseColor("#2196F3"));
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

        // ── ICON ──────────────────────────────────────────────────────────
        holder.icon.setImageResource(getCategoryIcon(r.getCategory()));
    }

    @Override
    public int getItemCount() { return list.size(); }

    private int getCategoryIcon(String category) {
        if (category == null) return R.drawable.ic_category_other;
        switch (category.toLowerCase()) {
            case "food":
            case "bakery":
            case "restaurant":    return R.drawable.ic_food;
            case "water":
            case "water_station": return R.drawable.ic_water;
            case "medical":
            case "pharmacy":
            case "hospital":      return R.drawable.ic_medical;
            case "fuel":
            case "fuel_station":  return R.drawable.ic_fuel;
            case "transport":     return R.drawable.ic_transport;
            case "clothes":       return R.drawable.ic_clothes;
            default:              return R.drawable.ic_category_other;
        }
    }

    private String formatCategory(String category) {
        if (category == null) return "";
        String s = category.replace("_", " ");
        return s.substring(0, 1).toUpperCase() + s.substring(1);
    }
}