package com.example.crises;

import android.content.Intent;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.net.Uri;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.cardview.widget.CardView;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.List;

public class HospitalAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_HEADER   = 0;
    private static final int TYPE_HOSPITAL = 1;

    private static final int COLOR_SAFE    = Color.parseColor("#22C55E");
    private static final int COLOR_WARNING = Color.parseColor("#F59E0B");
    private static final int COLOR_DANGER  = Color.parseColor("#EF4444");
    private static final int COLOR_UNKNOWN = Color.parseColor("#6B7280");

    private List<Hospital> recommendedList;
    private List<Hospital> hospitalList;

    public HospitalAdapter(List<Hospital> recommendedList,
                           List<Hospital> hospitalList) {
        this.recommendedList = recommendedList;
        this.hospitalList    = hospitalList;
    }

    public void updateLists(List<Hospital> recommended,
                            List<Hospital> hospitals) {
        this.recommendedList = new ArrayList<>(recommended);
        this.hospitalList    = new ArrayList<>(hospitals);
        notifyDataSetChanged();
    }

    @Override public int getItemCount()           { return hospitalList.size() + 1; }
    @Override public int getItemViewType(int pos) { return pos == 0 ? TYPE_HEADER : TYPE_HOSPITAL; }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent,
                                                      int viewType) {
        LayoutInflater inf = LayoutInflater.from(parent.getContext());
        if (viewType == TYPE_HEADER) {
            return new HeaderViewHolder(
                    inf.inflate(R.layout.item_recommendation_header, parent, false));
        }
        return new HospitalViewHolder(
                inf.inflate(R.layout.item_hospitals, parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder,
                                 int position) {
        if (getItemViewType(position) == TYPE_HEADER) {
            bindHeader((HeaderViewHolder) holder);
        } else {
            bindHospital((HospitalViewHolder) holder,
                    hospitalList.get(position - 1));
        }
    }

    // ── BIND HEADER ───────────────────────────────────────────────────────────

    private void bindHeader(HeaderViewHolder h) {
        h.hospRec1Card.setVisibility(View.GONE);
        h.hospRec2Card.setVisibility(View.GONE);
        h.hospRec3Card.setVisibility(View.GONE);

        if (recommendedList.size() >= 1) bindChip(h, 1, recommendedList.get(0));
        if (recommendedList.size() >= 2) bindChip(h, 2, recommendedList.get(1));
        if (recommendedList.size() >= 3) bindChip(h, 3, recommendedList.get(2));
    }

    private void bindChip(HeaderViewHolder h, int rank, Hospital hosp) {
        CardView card;
        TextView name, info, beds;

        switch (rank) {
            case 1:
                card = h.hospRec1Card; name = h.hospRec1Name;
                info = h.hospRec1Info; beds = h.hospRec1Beds; break;
            case 2:
                card = h.hospRec2Card; name = h.hospRec2Name;
                info = h.hospRec2Info; beds = h.hospRec2Beds; break;
            default:
                card = h.hospRec3Card; name = h.hospRec3Name;
                info = h.hospRec3Info; beds = h.hospRec3Beds; break;
        }

        card.setVisibility(View.VISIBLE);
        name.setText(hosp.getName());

        String infoText = "🛏 " + hosp.getAvailableBeds() + " beds available";
        if (hosp.hasDistance()) infoText += " · " + hosp.getFormattedDistance();
        info.setText(infoText);
        beds.setText(hosp.getAvailableBeds() + " free");
    }

    // ── BIND HOSPITAL CARD ────────────────────────────────────────────────────

    private void bindHospital(HospitalViewHolder h, Hospital hosp) {
        String status = hosp.getStatus() == null ? "" :
                hosp.getStatus().toLowerCase().trim();

        // Recommended badge
        if (h.recommendedBadge != null)
            h.recommendedBadge.setVisibility(
                    hosp.isRecommended() ? View.VISIBLE : View.GONE);

        // Letter icon — color matches status
        if (h.letterIcon != null) {
            String n = hosp.getName();
            h.letterIcon.setText((n != null && !n.isEmpty())
                    ? String.valueOf(n.charAt(0)).toUpperCase() : "H");
            switch (status) {
                case "safe":
                    h.letterIcon.setBackgroundResource(R.drawable.circle_green); break;
                case "warning":
                    h.letterIcon.setBackgroundResource(R.drawable.circle_amber); break;
                case "dangerous":
                    h.letterIcon.setBackgroundResource(R.drawable.circle_red);   break;
                default:
                    h.letterIcon.setBackgroundResource(R.drawable.circle_blue);  break;
            }
        }

        // Status pill
        if (h.statusPill != null) {
            h.statusPill.setText(getStatusPillLabel(status));
            switch (status) {
                case "safe":
                    h.statusPill.setBackgroundResource(R.drawable.pill_green); break;
                case "warning":
                    h.statusPill.setBackgroundResource(R.drawable.pill_amber); break;
                case "dangerous":
                    h.statusPill.setBackgroundResource(R.drawable.pill_red);   break;
                default:
                    h.statusPill.setBackgroundColor(COLOR_UNKNOWN);            break;
            }
            h.statusPill.setTextColor(Color.WHITE);
        }

        // Distance
        if (h.distance != null) {
            if (hosp.hasDistance()) {
                h.distance.setVisibility(View.VISIBLE);
                h.distance.setText(hosp.getFormattedDistance());
            } else {
                h.distance.setVisibility(View.GONE);
            }
        }

        // Name + Location + Region
        h.name.setText(hosp.getName());
        h.location.setText(hosp.getLocation());
        if (h.region != null)
            h.region.setText("Region: " + hosp.getRegion());

        // Bed counts
        h.totalBeds.setText(String.valueOf(hosp.getTotalBeds()));
        h.availableBeds.setText(String.valueOf(hosp.getAvailableBeds()));
        h.occupiedBeds.setText(String.valueOf(hosp.getOccupiedBeds()));

        // Bed progress bar
        int bedPct = (int) hosp.getOccupancyPct();
        if (h.bedBar != null) {
            h.bedBar.setProgress(bedPct);
            h.bedBar.setProgressTintList(
                    ColorStateList.valueOf(occupancyColor(bedPct)));
        }
        if (h.occupancyPct != null) {
            h.occupancyPct.setText(bedPct + "% occupied");
            h.occupancyPct.setTextColor(occupancyColor(bedPct));
        }

        // Phone — tappable
        if (h.phone != null) {
            String phoneNum = hosp.getPhone();
            if (phoneNum != null && !phoneNum.trim().isEmpty()) {
                h.phone.setText(phoneNum);
                h.phone.setVisibility(View.VISIBLE);
                h.phone.setOnClickListener(v -> {
                    Intent intent = new Intent(Intent.ACTION_DIAL);
                    intent.setData(Uri.parse("tel:" + phoneNum.trim()));
                    v.getContext().startActivity(intent);
                });
            } else {
                h.phone.setVisibility(View.GONE);
            }
        }

        // Card always white
        if (h.card != null)
            h.card.setCardBackgroundColor(Color.WHITE);
    }

    // ── VIEW HOLDERS ──────────────────────────────────────────────────────────

    static class HeaderViewHolder extends RecyclerView.ViewHolder {
        CardView hospRec1Card, hospRec2Card, hospRec3Card;
        TextView hospRec1Name, hospRec1Info, hospRec1Beds;
        TextView hospRec2Name, hospRec2Info, hospRec2Beds;
        TextView hospRec3Name, hospRec3Info, hospRec3Beds;

        HeaderViewHolder(@NonNull View v) {
            super(v);
            hospRec1Card = v.findViewById(R.id.hospRec1Card);
            hospRec2Card = v.findViewById(R.id.hospRec2Card);
            hospRec3Card = v.findViewById(R.id.hospRec3Card);
            hospRec1Name = v.findViewById(R.id.hospRec1Name);
            hospRec1Info = v.findViewById(R.id.hospRec1Info);
            hospRec1Beds = v.findViewById(R.id.hospRec1Beds);
            hospRec2Name = v.findViewById(R.id.hospRec2Name);
            hospRec2Info = v.findViewById(R.id.hospRec2Info);
            hospRec2Beds = v.findViewById(R.id.hospRec2Beds);
            hospRec3Name = v.findViewById(R.id.hospRec3Name);
            hospRec3Info = v.findViewById(R.id.hospRec3Info);
            hospRec3Beds = v.findViewById(R.id.hospRec3Beds);
        }
    }

    static class HospitalViewHolder extends RecyclerView.ViewHolder {
        CardView    card;
        TextView    letterIcon;
        TextView    statusPill;
        TextView    name, location, region;
        TextView    distance;
        TextView    recommendedBadge;
        TextView    totalBeds, availableBeds, occupiedBeds;
        TextView    occupancyPct;
        TextView    phone;
        ProgressBar bedBar;

        HospitalViewHolder(@NonNull View v) {
            super(v);
            card             = v.findViewById(R.id.hospitalCard);
            letterIcon       = v.findViewById(R.id.hospitalLetterIcon);
            statusPill       = v.findViewById(R.id.hospitalStatus);
            name             = v.findViewById(R.id.hospitalName);
            location         = v.findViewById(R.id.hospitalLocation);
            region           = v.findViewById(R.id.hospitalRegion);
            distance         = v.findViewById(R.id.hospitalDistance);
            recommendedBadge = v.findViewById(R.id.recommendedBadge);
            totalBeds        = v.findViewById(R.id.totalBeds);
            availableBeds    = v.findViewById(R.id.availableBeds);
            occupiedBeds     = v.findViewById(R.id.occupiedBeds);
            bedBar           = v.findViewById(R.id.bedProgressBar);
            occupancyPct     = v.findViewById(R.id.occupancyPct);
            phone            = v.findViewById(R.id.hospitalPhone);
        }
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    private String getStatusPillLabel(String s) {
        switch (s) {
            case "safe":      return "SAFE";
            case "warning":   return "WARNING";
            case "dangerous": return "DANGEROUS";
            default:          return s.toUpperCase();
        }
    }

    private int occupancyColor(int pct) {
        if (pct < 60) return COLOR_SAFE;
        if (pct < 85) return COLOR_WARNING;
        return COLOR_DANGER;
    }
}