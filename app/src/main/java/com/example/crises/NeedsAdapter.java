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
import androidx.cardview.widget.CardView;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.List;

public class NeedsAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_HEADER = 0;
    private static final int TYPE_NEED   = 1;

    private List<Need> needList;
    private List<Need> recommended;

    public NeedsAdapter(List<Need> needList, List<Need> recommended) {
        this.needList    = needList;
        this.recommended = recommended;
    }

    public void updateList(List<Need> newList, List<Need> newRecommended) {
        this.needList    = new ArrayList<>(newList);
        this.recommended = new ArrayList<>(newRecommended);
        notifyDataSetChanged();
    }

    @Override public int getItemCount()           { return needList.size() + 1; }
    @Override public int getItemViewType(int pos) { return pos == 0 ? TYPE_HEADER : TYPE_NEED; }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        LayoutInflater inf = LayoutInflater.from(parent.getContext());
        if (viewType == TYPE_HEADER) {
            return new HeaderViewHolder(
                    inf.inflate(R.layout.item_needs_recommendation_header, parent, false));
        }
        return new NeedViewHolder(inf.inflate(R.layout.item_need, parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        if (getItemViewType(position) == TYPE_HEADER) {
            bindHeader((HeaderViewHolder) holder);
        } else {
            bindNeed((NeedViewHolder) holder, needList.get(position - 1));
        }
    }

    // ── BIND HEADER ───────────────────────────────────────────────────────────

    private void bindHeader(HeaderViewHolder h) {
        h.card1.setVisibility(View.GONE);
        h.card2.setVisibility(View.GONE);
        h.card3.setVisibility(View.GONE);

        if (recommended.size() >= 1) bindChip(h.card1, h.name1, h.info1, h.dist1, recommended.get(0));
        if (recommended.size() >= 2) bindChip(h.card2, h.name2, h.info2, h.dist2, recommended.get(1));
        if (recommended.size() >= 3) bindChip(h.card3, h.name3, h.info3, h.dist3, recommended.get(2));
    }

    private void bindChip(CardView card, TextView name, TextView info, TextView dist, Need need) {
        card.setVisibility(View.VISIBLE);
        name.setText(need.getName());
        info.setText(getCategoryEmoji(need.getCategory())
                + " " + formatCategory(need.getCategory())
                + " · " + need.getLocation());
        if (need.hasDistance()) {
            dist.setVisibility(View.VISIBLE);
            dist.setText(need.getFormattedDistance());
        } else {
            dist.setVisibility(View.GONE);
        }
    }

    // ── BIND NEED CARD ────────────────────────────────────────────────────────

    private void bindNeed(NeedViewHolder h, Need r) {

        if (h.recommendedBadge != null)
            h.recommendedBadge.setVisibility(r.isRecommended() ? View.VISIBLE : View.GONE);

        if (h.distance != null) {
            if (r.hasDistance()) {
                h.distance.setVisibility(View.VISIBLE);
                h.distance.setText(r.getFormattedDistance());
            } else {
                h.distance.setVisibility(View.GONE);
            }
        }

        h.name.setText(r.getName());
        h.location.setText(r.getLocation());
        h.category.setText(getCategoryEmoji(r.getCategory()) + " " + formatCategory(r.getCategory()));
        h.address.setText(r.getAddress());
        h.hours.setText(r.getHours());

        if (h.notes != null) h.notes.setVisibility(View.GONE);

        String phone = r.getContact();
        h.contact.setText(phone);
        h.contact.setTextColor(Color.parseColor("#2E7D32"));
        if (phone != null && !phone.trim().isEmpty()) {
            h.contact.setOnClickListener(v -> {
                Intent intent = new Intent(Intent.ACTION_DIAL);
                intent.setData(Uri.parse("tel:" + phone.trim()));
                v.getContext().startActivity(intent);
            });
        } else {
            h.contact.setOnClickListener(null);
        }

        bindStatusBadge(h.status, r.getStatus());
        h.icon.setImageResource(getCategoryIcon(r.getCategory()));
    }

    private void bindStatusBadge(TextView view, String status) {
        if (view == null || status == null) return;
        int color;
        String label;
        switch (status.toLowerCase().trim()) {
            case "active": case "available": case "open":
                label = "ACTIVE";      color = Color.parseColor("#22C55E"); break;
            case "fulfilled":
                label = "FULFILLED";   color = Color.parseColor("#2196F3"); break;
            case "in_progress": case "in progress":
                label = "IN PROGRESS"; color = Color.parseColor("#F59E0B"); break;
            case "rejected": case "closed":
                label = "CLOSED";      color = Color.parseColor("#EF4444"); break;
            default:
                label = status.toUpperCase(); color = Color.parseColor("#6B7280"); break;
        }
        view.setText(label);
        view.setBackgroundColor(color);
        view.setTextColor(Color.WHITE);
        view.setPadding(20, 6, 20, 6);
    }

    // ── VIEW HOLDERS ──────────────────────────────────────────────────────────

    static class HeaderViewHolder extends RecyclerView.ViewHolder {
        CardView card1, card2, card3;
        TextView name1, info1, dist1;
        TextView name2, info2, dist2;
        TextView name3, info3, dist3;

        HeaderViewHolder(@NonNull View v) {
            super(v);
            card1 = v.findViewById(R.id.Rec1Card);
            card2 = v.findViewById(R.id.Rec2Card);
            card3 = v.findViewById(R.id.Rec3Card);
            name1 = v.findViewById(R.id.name1);
            info1 = v.findViewById(R.id.info1);
            dist1 = v.findViewById(R.id.dist1);
            name2 = v.findViewById(R.id.name2);
            info2 = v.findViewById(R.id.info2);
            dist2 = v.findViewById(R.id.dist2);
            name3 = v.findViewById(R.id.name3);
            info3 = v.findViewById(R.id.info3);
            dist3 = v.findViewById(R.id.dist3);
        }
    }

    static class NeedViewHolder extends RecyclerView.ViewHolder {
        TextView  name, location, category, status;
        TextView  address, contact, hours, notes;
        TextView  recommendedBadge, distance;
        ImageView icon;

        NeedViewHolder(@NonNull View v) {
            super(v);
            name             = v.findViewById(R.id.resource_name);
            location         = v.findViewById(R.id.location);
            category         = v.findViewById(R.id.category);
            status           = v.findViewById(R.id.status);
            address          = v.findViewById(R.id.address);
            contact          = v.findViewById(R.id.contact);
            hours            = v.findViewById(R.id.hours);
            notes            = v.findViewById(R.id.notes);
            icon             = v.findViewById(R.id.category_icon);
            recommendedBadge = v.findViewById(R.id.recommendedBadge);
            distance         = v.findViewById(R.id.distanceBadge);
        }
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    private String getCategoryEmoji(String category) {
        if (category == null) return "📦";
        switch (category.toLowerCase().trim()) {
            case "food": case "bakery": case "restaurant": case "supermarket": return "🍞";
            case "water": case "water_station":  return "💧";
            case "medical": case "pharmacy":     return "💊";
            case "hospital":                     return "🏥";
            case "fuel": case "fuel_station":    return "⛽";
            case "shelter":                      return "🏠";
            case "clothing":                     return "👕";
            case "electricity":                  return "⚡";
            default:                             return "📦";
        }
    }

    private String formatCategory(String category) {
        if (category == null || category.isEmpty()) return "Resource";
        switch (category.toLowerCase().trim()) {
            case "food":          return "Food";
            case "bakery":        return "Bakery";
            case "restaurant":    return "Restaurant";
            case "supermarket":   return "Supermarket";
            case "water":         return "Water";
            case "water_station": return "Water Station";
            case "medical":       return "Medical";
            case "pharmacy":      return "Pharmacy";
            case "hospital":      return "Hospital";
            case "fuel":          return "Fuel";
            case "fuel_station":  return "Fuel Station";
            case "shelter":       return "Shelter";
            case "clothing":      return "Clothing";
            case "electricity":   return "Electricity";
            default:
                String s = category.replace("_", " ").trim();
                return s.substring(0, 1).toUpperCase() + s.substring(1).toLowerCase();
        }
    }

    private int getCategoryIcon(String category) {
        if (category == null) return R.drawable.ic_category_other;
        switch (category.toLowerCase().trim()) {
            case "food": case "bakery": case "restaurant": case "supermarket": return R.drawable.ic_food;
            case "water": case "water_station": return R.drawable.ic_water;
            case "medical": case "pharmacy": case "hospital":                  return R.drawable.ic_medical;
            case "fuel": case "fuel_station":                                  return R.drawable.ic_fuel;
            default:                                                           return R.drawable.ic_category_other;
        }
    }
}