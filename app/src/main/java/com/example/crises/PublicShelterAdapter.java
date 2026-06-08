package com.example.crises;

import android.content.res.ColorStateList;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.cardview.widget.CardView;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

public class PublicShelterAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_HEADER  = 0;
    private static final int TYPE_SHELTER = 1;

    private static final int COLOR_OPEN     = Color.parseColor("#22C55E");
    private static final int COLOR_NEARFULL = Color.parseColor("#F59E0B");
    private static final int COLOR_FULL     = Color.parseColor("#EF4444");
    private static final int COLOR_UNKNOWN  = Color.parseColor("#6B7280");

    private List<PublicShelter> shelterList;
    private List<PublicShelter> recommended;

    public PublicShelterAdapter(List<PublicShelter> shelterList,
                                List<PublicShelter> recommended) {
        this.shelterList = shelterList;
        this.recommended = recommended;
    }

    public void updateList(List<PublicShelter> newList,
                           List<PublicShelter> newRecommended) {
        this.shelterList = new ArrayList<>(newList);
        this.recommended = new ArrayList<>(newRecommended);
        Collections.sort(this.recommended,
                (a, b) -> Integer.compare(a.getRecommendationRank(), b.getRecommendationRank()));
        notifyDataSetChanged();
    }

    @Override public int getItemCount()           { return shelterList.size() + 1; }
    @Override public int getItemViewType(int pos) { return pos == 0 ? TYPE_HEADER : TYPE_SHELTER; }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        LayoutInflater inf = LayoutInflater.from(parent.getContext());
        if (viewType == TYPE_HEADER) {
            return new HeaderViewHolder(
                    inf.inflate(R.layout.item_shelter_recommendation_header, parent, false));
        }
        return new ShelterViewHolder(
                inf.inflate(R.layout.item_public_shelter, parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        if (getItemViewType(position) == TYPE_HEADER) {
            bindHeader((HeaderViewHolder) holder);
        } else {
            bindShelter((ShelterViewHolder) holder, shelterList.get(position - 1));
        }
    }

    // ── HEADER ────────────────────────────────────────────────────────────────

    private void bindHeader(HeaderViewHolder h) {
        h.shelRec1Card.setVisibility(View.GONE);
        h.shelRec2Card.setVisibility(View.GONE);
        h.shelRec3Card.setVisibility(View.GONE);

        if (recommended.size() >= 1) bindRecChip(h, 1, recommended.get(0));
        if (recommended.size() >= 2) bindRecChip(h, 2, recommended.get(1));
        if (recommended.size() >= 3) bindRecChip(h, 3, recommended.get(2));
    }

    private void bindRecChip(HeaderViewHolder h, int rank, PublicShelter s) {
        CardView card;
        TextView nameView, infoView, spotsView;

        switch (rank) {
            case 1:
                card = h.shelRec1Card; nameView = h.shelRec1Name;
                infoView = h.shelRec1Info; spotsView = h.shelRec1Spots; break;
            case 2:
                card = h.shelRec2Card; nameView = h.shelRec2Name;
                infoView = h.shelRec2Info; spotsView = h.shelRec2Spots; break;
            default:
                card = h.shelRec3Card; nameView = h.shelRec3Name;
                infoView = h.shelRec3Info; spotsView = h.shelRec3Spots; break;
        }

        card.setVisibility(View.VISIBLE);
        nameView.setText(s.getShelterName());

        String info = getStatusEmoji(s.getStatus()) + " " + s.getAvailable() + " spots";
        if (s.hasDistance()) info += " · " + s.getFormattedDistance();
        infoView.setText(info);
        spotsView.setText(s.getAvailable() + " free");
        card.setCardBackgroundColor(chipBgColor(s.getStatus()));
    }

    // ── SHELTER CARD ──────────────────────────────────────────────────────────

    private void bindShelter(ShelterViewHolder h, PublicShelter s) {
        String status = s.getStatus() == null ? "" : s.getStatus().toLowerCase().trim();

        // Recommended badge
        if (h.recommendedBadge != null)
            h.recommendedBadge.setVisibility(s.isRecommended() ? View.VISIBLE : View.GONE);

        // Letter icon — color matches status
        if (h.letterIcon != null) {
            String name = s.getShelterName();
            h.letterIcon.setText((name != null && !name.isEmpty())
                    ? String.valueOf(name.charAt(0)).toUpperCase() : "S");
            if (isFull(status))
                h.letterIcon.setBackgroundResource(R.drawable.circle_red);
            else if (isNearFull(status))
                h.letterIcon.setBackgroundResource(R.drawable.circle_amber);
            else
                h.letterIcon.setBackgroundResource(R.drawable.circle_green);
        }

        // Name + Location
        h.shelterName.setText(s.getShelterName());
        h.location.setText(s.getLocation());

        // Distance
        if (h.distance != null) {
            if (s.hasDistance()) {
                h.distance.setVisibility(View.VISIBLE);
                h.distance.setText(s.getFormattedDistance());
            } else {
                h.distance.setVisibility(View.GONE);
            }
        }

        // Status pill
        if (h.statusView != null) {
            h.statusView.setText(getStatusLabel(status));
            if (isFull(status))
                h.statusView.setBackgroundResource(R.drawable.pill_red);
            else if (isNearFull(status))
                h.statusView.setBackgroundResource(R.drawable.pill_amber);
            else
                h.statusView.setBackgroundResource(R.drawable.pill_green);
            h.statusView.setTextColor(Color.WHITE);
        }

        // Card always white — no border
        if (h.card != null) h.card.setCardBackgroundColor(Color.WHITE);

        // Capacity
        if (h.capacityTotal != null)     h.capacityTotal.setText(String.valueOf(s.getCapacity()));
        if (h.capacityAvailable != null) h.capacityAvailable.setText(String.valueOf(s.getAvailable()));
        if (h.capacityOccupied != null)  h.capacityOccupied.setText(String.valueOf(s.getOccupied()));

        // Progress bar
        int pct = (int) s.getOccupancyPct();
        if (h.occupancyBar != null) {
            h.occupancyBar.setProgress(pct);
            h.occupancyBar.setProgressTintList(
                    ColorStateList.valueOf(occupancyColor(pct)));
        }
        if (h.occupancyPct != null) {
            h.occupancyPct.setText(pct + "% occupied");
            h.occupancyPct.setTextColor(occupancyColor(pct));
        }
    }

    // ── VIEW HOLDERS ──────────────────────────────────────────────────────────

    static class HeaderViewHolder extends RecyclerView.ViewHolder {
        // Unique shelRec* IDs — no clash with hospital or needs headers
        CardView shelRec1Card, shelRec2Card, shelRec3Card;
        TextView shelRec1Name, shelRec1Info, shelRec1Spots;
        TextView shelRec2Name, shelRec2Info, shelRec2Spots;
        TextView shelRec3Name, shelRec3Info, shelRec3Spots;

        HeaderViewHolder(@NonNull View v) {
            super(v);
            shelRec1Card  = v.findViewById(R.id.shelRec1Card);
            shelRec2Card  = v.findViewById(R.id.shelRec2Card);
            shelRec3Card  = v.findViewById(R.id.shelRec3Card);

            shelRec1Name  = v.findViewById(R.id.shelRec1Name);
            shelRec1Info  = v.findViewById(R.id.shelRec1Info);
            shelRec1Spots = v.findViewById(R.id.shelRec1Spots);

            shelRec2Name  = v.findViewById(R.id.shelRec2Name);
            shelRec2Info  = v.findViewById(R.id.shelRec2Info);
            shelRec2Spots = v.findViewById(R.id.shelRec2Spots);

            shelRec3Name  = v.findViewById(R.id.shelRec3Name);
            shelRec3Info  = v.findViewById(R.id.shelRec3Info);
            shelRec3Spots = v.findViewById(R.id.shelRec3Spots);
        }
    }

    static class ShelterViewHolder extends RecyclerView.ViewHolder {
        CardView    card;
        TextView    letterIcon;       // replaces ImageView — circle with first letter
        TextView    shelterName, location, statusView, distance;
        TextView    recommendedBadge;
        TextView    capacityTotal, capacityAvailable, capacityOccupied;
        ProgressBar occupancyBar;
        TextView    occupancyPct;

        ShelterViewHolder(@NonNull View v) {
            super(v);
            card              = v.findViewById(R.id.shelterCard);
            letterIcon        = v.findViewById(R.id.shelter_icon_letter);
            shelterName       = v.findViewById(R.id.shelter_name);
            location          = v.findViewById(R.id.location);
            statusView        = v.findViewById(R.id.status);
            distance          = v.findViewById(R.id.shelterDistance);
            recommendedBadge  = v.findViewById(R.id.recommendedBadge);
            capacityTotal     = v.findViewById(R.id.capacityTotal);
            capacityAvailable = v.findViewById(R.id.capacityAvailable);
            capacityOccupied  = v.findViewById(R.id.capacityOccupied);
            occupancyBar      = v.findViewById(R.id.occupancyBar);
            occupancyPct      = v.findViewById(R.id.occupancyPct);
        }
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    private boolean isFull(String s)     { return s.contains("full") && !s.contains("near"); }
    private boolean isNearFull(String s) { return s.contains("near") || s.contains("limited"); }

    private String getStatusLabel(String s) {
        if (isFull(s))          return "FULL";
        if (isNearFull(s))      return "NEAR-FULL";
        if (s.contains("open")) return "OPEN";
        return s.toUpperCase();
    }

    private String getStatusEmoji(String s) {
        if (s == null) return "🏠";
        String l = s.toLowerCase().trim();
        if (isFull(l))          return "🔴";
        if (isNearFull(l))      return "🟠";
        return "🟢";
    }

    private int chipBgColor(String status) {
        if (status == null) return Color.parseColor("#F0FDF4");
        String l = status.toLowerCase().trim();
        if (isFull(l))     return Color.parseColor("#FFF1F2");
        if (isNearFull(l)) return Color.parseColor("#FFFBEB");
        return Color.parseColor("#F0FDF4");
    }

    private int occupancyColor(int pct) {
        if (pct < 60) return COLOR_OPEN;
        if (pct < 85) return COLOR_NEARFULL;
        return COLOR_FULL;
    }
}