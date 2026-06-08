package com.example.crises;

public class Need {

    private String name, category, status, location;
    private String address, contact, hours, notes;
    private String orgName;
    private double distanceKm;
    private boolean isRecommended     = false;
    private int     recommendationRank = 0;

    public Need(String name, String category, String status,
                String location, String address,
                String contact, String hours, String notes,
                String orgName, double distanceKm) {
        this.name              = name;
        this.category          = category;
        this.status            = status;
        this.location          = location;
        this.address           = address;
        this.contact           = contact;
        this.hours             = hours;
        this.notes             = notes;
        this.orgName           = orgName;
        this.distanceKm        = distanceKm;
        this.isRecommended     = false;
        this.recommendationRank = 0;
    }

    // ── Getters ───────────────────────────────────────────────────────────────
    public String getName()     { return name; }
    public String getCategory() { return category; }
    public String getStatus()   { return status; }
    public String getLocation() { return location; }
    public String getAddress()  { return address; }
    public String getContact()  { return contact; }
    public String getHours()    { return hours; }
    public String getNotes()    { return notes; }
    public String getOrgName()  { return orgName; }
    public double getDistanceKm() { return distanceKm; }

    // ── Recommendation ────────────────────────────────────────────────────────
    public boolean isRecommended()              { return isRecommended; }
    public void    setRecommended(boolean val)  { this.isRecommended = val; }
    public int     getRecommendationRank()      { return recommendationRank; }
    public void    setRecommendationRank(int r) { this.recommendationRank = r; }

    // ── Distance helpers ──────────────────────────────────────────────────────
    public boolean hasDistance() { return distanceKm >= 0; }

    public String getFormattedDistance() {
        if (distanceKm < 0) return "";
        if (distanceKm < 1) return (int)(distanceKm * 1000) + " m";
        return String.format("%.1f km", distanceKm);
    }
}