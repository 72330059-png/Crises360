package com.example.crises;

import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;

public class HouseAdapter extends RecyclerView.Adapter<HouseAdapter.HouseViewHolder> {

    private final Context context;
    private final ArrayList<House> houseList;

    public HouseAdapter(Context context, ArrayList<House> houseList) {
        this.context = context;
        this.houseList = houseList;
    }

    public static class HouseViewHolder extends RecyclerView.ViewHolder {
        TextView city, description, price, phone;
        Button callBtn, whatsappBtn;

        public HouseViewHolder(@NonNull View itemView) {
            super(itemView);
            city = itemView.findViewById(R.id.txtCity);
            description = itemView.findViewById(R.id.txtDescription);
            price = itemView.findViewById(R.id.txtPrice);
            phone = itemView.findViewById(R.id.txtPhone);

            callBtn = itemView.findViewById(R.id.btnCall);
            whatsappBtn = itemView.findViewById(R.id.btnWhatsApp);
        }
    }

    @NonNull
    @Override
    public HouseViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        // This still uses item_house.xml (Make sure to remove the ImageView from that XML too)
        View view = LayoutInflater.from(context).inflate(R.layout.item_house, parent, false);
        return new HouseViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull HouseViewHolder holder, int position) {
        House house = houseList.get(position);

        // 1. Set Text Data
        holder.city.setText(house.getCity());
        holder.description.setText(house.getDescription());
        holder.price.setText(house.getPrice());
        holder.phone.setText(house.getPhone());

        // 2. Call Button Logic
        holder.callBtn.setOnClickListener(v -> {
            Intent intent = new Intent(Intent.ACTION_DIAL);
            intent.setData(Uri.parse("tel:" + house.getPhone()));
            context.startActivity(intent);
        });

        // 3. WhatsApp Button Logic (Fixes ActivityNotFoundException)
        holder.whatsappBtn.setOnClickListener(v -> {
            String phoneNumber = "961" + house.getPhone();
            String url = "https://api.whatsapp.com/send?phone=" + phoneNumber;

            Intent intent = new Intent(Intent.ACTION_VIEW);
            intent.setData(Uri.parse(url));

            try {
                context.startActivity(intent);
            } catch (android.content.ActivityNotFoundException e) {
                Toast.makeText(context, "WhatsApp not installed. Opening browser...", Toast.LENGTH_SHORT).show();
                intent.setPackage(null);
                context.startActivity(intent);
            }
        });
    }

    @Override
    public int getItemCount() {
        return houseList.size();
    }
}