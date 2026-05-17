package com.example.crises;

import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

public class HospitalAdapter extends RecyclerView.Adapter<HospitalAdapter.ViewHolder> {

    List<Hospital> hospitalList;

    public HospitalAdapter(List<Hospital> hospitalList) {
        this.hospitalList = hospitalList;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_hospitals, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {

        Hospital h = hospitalList.get(position);

        holder.name.setText(h.getName());
        holder.location.setText("📍 " + h.getLocation());
        holder.region.setText("Region: " + h.getRegion());

        holder.total.setText(String.valueOf(h.getTotalBeds()));
        holder.available.setText(String.valueOf(h.getAvailableBeds()));
        holder.occupied.setText(String.valueOf(h.getOccupiedBeds()));

        String status = h.getStatus();
        holder.status.setText("Status: " + status);

        int color;

        if (status.equalsIgnoreCase("Safe")) {
            color = Color.GREEN;
        } else if (status.equalsIgnoreCase("Warning")) {
            color = Color.parseColor("#FFA500");
        } else {
            color = Color.RED; // Dangerous
        }

        holder.status.setTextColor(color);
    }

    @Override
    public int getItemCount() {
        return hospitalList.size();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {

        TextView name, location, region, total, available, occupied, status;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);

            name = itemView.findViewById(R.id.hospitalName);
            location = itemView.findViewById(R.id.hospitalLocation);
            region = itemView.findViewById(R.id.hospitalRegion);

            total = itemView.findViewById(R.id.totalBeds);
            available = itemView.findViewById(R.id.availableBeds);
            occupied = itemView.findViewById(R.id.occupiedBeds);

            status = itemView.findViewById(R.id.hospitalStatus);
        }
    }
}