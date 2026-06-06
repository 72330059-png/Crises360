package com.example.crises;

import android.graphics.Color; // Import Color to parse hex color strings into color integers
import android.view.LayoutInflater; // Import LayoutInflater to inflate item XML layouts
import android.view.View; // Import View as the base class for UI elements
import android.view.ViewGroup; // Import ViewGroup as the parent container passed to onCreateViewHolder
import android.widget.TextView; // Import TextView to display text in each list item

import androidx.annotation.NonNull; // Import NonNull annotation to mark parameters that must not be null
import androidx.recyclerview.widget.RecyclerView; // Import RecyclerView base class for the adapter and ViewHolder

import java.util.ArrayList; // Import ArrayList to hold the list of alert items

public class AlertsAdapter extends RecyclerView.Adapter<AlertsAdapter.ViewHolder> { // Adapter for the alerts RecyclerView; generic type is our own ViewHolder

    ArrayList<AlertModel> list; // The list of alerts this adapter will display

    public AlertsAdapter(ArrayList<AlertModel> list) { // Constructor: receives the list from the activity
        this.list = list; // Store the reference so the adapter always reflects the latest data
    }

    public static class ViewHolder extends RecyclerView.ViewHolder { // Static inner class that holds references to each item's views
        TextView txtType, txtMessage, txtLocation, txtTime; // TextViews for severity badge, message, location, and time
        View     strip; // Colored vertical strip on the left side of each card

        public ViewHolder(View itemView) { // Constructor: receives the inflated item view
            super(itemView); // Pass itemView to RecyclerView.ViewHolder so it can manage it
            txtType     = itemView.findViewById(R.id.txtType); // Find the severity badge TextView
            txtMessage  = itemView.findViewById(R.id.txtMessage); // Find the message TextView
            txtLocation = itemView.findViewById(R.id.txtLocation); // Find the location TextView
            txtTime     = itemView.findViewById(R.id.txtTime); // Find the time TextView
            strip       = itemView.findViewById(R.id.priorityStrip); // Find the colored left-side strip View
        }
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) { // Called when RecyclerView needs a new item view
        View v = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_alert, parent, false); // Inflate item_alert.xml into a View (false = don't attach to parent yet)
        return new ViewHolder(v); // Wrap the inflated view in a ViewHolder and return it
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) { // Called to bind data to an existing ViewHolder at the given position
        AlertModel alert = list.get(position); // Get the alert object at this position

        holder.txtMessage.setText(alert.getMessage()); // Set the alert message text
        holder.txtLocation.setText("📍 " + alert.getLocation()); // Set location with a pin emoji prefix
        holder.txtTime.setText(alert.getTime()); // Set the timestamp text

        String severity = alert.getSeverity() != null
                ? alert.getSeverity() : ""; // Get severity string; use empty string if null to avoid crashes

        switch (severity.toLowerCase()) { // Switch on lowercase severity so "Warning" and "warning" both match
            case "warning":
                applyStyle(holder,
                        "⚠️ Warning", // Badge label with emoji
                        "#F59E0B",    // Amber strip color
                        "#F59E0B",    // Amber text color
                        R.drawable.badge_warning_bg); // Yellow badge background drawable
                break;

            case "info":
                applyStyle(holder,
                        "📢 Info",   // Badge label with emoji
                        "#2F66F6",   // Blue strip color
                        "#2F66F6",   // Blue text color
                        R.drawable.badge_update_bg); // Blue badge background drawable
                break;

            case "critical":
            default: // Default also maps to Critical so unknown severities show as red
                applyStyle(holder,
                        "🚨 Critical", // Badge label with emoji
                        "#EF4444",     // Red strip color
                        "#EF4444",     // Red text color
                        R.drawable.badge_danger_bg); // Red badge background drawable
                break;
        }
    }

    private void applyStyle(ViewHolder holder,
                            String label,
                            String stripColor,
                            String textColor,
                            int badgeBackground) { // Helper method: applies visual style to a ViewHolder based on severity
        holder.txtType.setText(label); // Set the severity label text (e.g. "⚠️ Warning")
        holder.txtType.setTextColor(Color.parseColor(textColor)); // Parse hex string and set badge text color
        holder.txtType.setBackgroundResource(badgeBackground); // Set the badge background drawable
        holder.strip.setBackgroundColor(Color.parseColor(stripColor)); // Parse hex string and set the left strip color
    }

    @Override
    public int getItemCount() { // Tells RecyclerView how many items to display
        return list != null ? list.size() : 0; // Return list size, or 0 if list is null to prevent crashes
    }
}