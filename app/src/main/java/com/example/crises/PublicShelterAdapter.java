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

public class PublicShelterAdapter extends RecyclerView.Adapter<PublicShelterAdapter.ViewHolder> {

    List<PublicShelter> list;

    public PublicShelterAdapter(List<PublicShelter> list) {
        this.list = list;
    }

    // VIEW HOLDER
    public static class ViewHolder extends RecyclerView.ViewHolder {

        TextView shelter_name, location, status, available;
        ImageView icon;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);

            shelter_name = itemView.findViewById(R.id.shelter_name);
            location = itemView.findViewById(R.id.location);
            status = itemView.findViewById(R.id.status);
            available = itemView.findViewById(R.id.available);
            icon = itemView.findViewById(R.id.shelter_icon);
        }
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {

        View v = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_public_shelter, parent, false);

        return new ViewHolder(v);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {

        PublicShelter s = list.get(position);

        // BASIC DATA
        holder.shelter_name.setText(s.getShelterName());
        holder.location.setText("📍 " + s.getLocation());
        holder.available.setText("Free: " + s.getAvailable());

        // SAFE STATUS
        String status = (s.getStatus() == null) ? "" : s.getStatus().toLowerCase().trim();

        // RESET DEFAULT ICON FIRST
        holder.icon.setImageResource(R.drawable.ic_shelter_open);

        if (status.contains("full") && !status.contains("near")) {

            // 🔴 FULL
            holder.status.setText("FULL");
            holder.status.setBackgroundColor(Color.parseColor("#F44336"));
            holder.icon.setImageResource(R.drawable.ic_shelter_full);

        }
        else if (status.contains("near") || status.contains("almost") || status.contains("limited")) {

            // 🟠 NEAR FULL
            holder.status.setText("NEAR-FULL");
            holder.status.setBackgroundColor(Color.parseColor("#FF9800"));
            holder.icon.setImageResource(R.drawable.ic_shelter_near);

        }
        else {

            // 🟢 OPEN
            holder.status.setText("OPEN");
            holder.status.setBackgroundColor(Color.parseColor("#4CAF50"));
            holder.icon.setImageResource(R.drawable.ic_shelter_open);
        }

        holder.status.setTextColor(Color.WHITE);
    }

    @Override
    public int getItemCount() {
        return list.size();
    }
}