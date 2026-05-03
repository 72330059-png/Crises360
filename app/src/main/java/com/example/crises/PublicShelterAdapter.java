package com.example.crises;

import android.content.Intent;
import android.graphics.Color;
import android.net.Uri;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

public class PublicShelterAdapter extends RecyclerView.Adapter<PublicShelterAdapter.ViewHolder> {

    List<PublicShelter> list;

    public PublicShelterAdapter(List<PublicShelter> list) {
        this.list = list;
    }

    public static class ViewHolder extends RecyclerView.ViewHolder {

        TextView name, location, status, capacity, rooms, call, details;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);

            name = itemView.findViewById(R.id.name);
            location = itemView.findViewById(R.id.location);
            status = itemView.findViewById(R.id.status);
            capacity = itemView.findViewById(R.id.capacity);
            rooms = itemView.findViewById(R.id.rooms);
            call = itemView.findViewById(R.id.call);
            details = itemView.findViewById(R.id.details);
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


        holder.name.setText(s.getName());
        holder.location.setText("📍 " + s.getLocation());
        holder.capacity.setText("👥 " + s.getCurrent() + " / " + s.getCapacity());
        holder.rooms.setText("🛏 " + s.getEmptyRooms() + " empty rooms");
        holder.status.setText(s.getStatus());


        if (s.getStatus().equalsIgnoreCase("Available")) {
            holder.status.setBackgroundColor(Color.parseColor("#4CAF50")); // Green
        } else if (s.getStatus().equalsIgnoreCase("Full")) {
            holder.status.setBackgroundColor(Color.RED);
        } else {
            holder.status.setBackgroundColor(Color.parseColor("#FFA000")); // Orange
        }


        holder.call.setOnClickListener(v -> {
            Intent intent = new Intent(Intent.ACTION_DIAL);
            intent.setData(Uri.parse("tel:" + s.getPhone()));
            v.getContext().startActivity(intent);
        });

        // 🔹 INFO BUTTON (OPEN DETAILS PAGE)
        holder.details.setOnClickListener(v -> {

            Intent i = new Intent(v.getContext(), ShelterDetails.class);

            i.putExtra("name", s.getName());
            i.putExtra("location", s.getLocation());
            i.putExtra("status", s.getStatus());

            i.putExtra("address", s.getAddress());
            i.putExtra("phone", s.getPhone());
            i.putExtra("type", s.getType());

            i.putExtra("capacity", s.getCapacity());
            i.putExtra("current", s.getCurrent());
            i.putExtra("empty", s.getEmptyRooms());

            i.putExtra("food", s.isFood());
            i.putExtra("water", s.isWater());
            i.putExtra("electricity", s.isElectricity());
            i.putExtra("medical", s.isMedical());

            v.getContext().startActivity(i);
        });
    }

    @Override
    public int getItemCount() {
        return list.size();
    }
}