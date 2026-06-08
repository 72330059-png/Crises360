package com.example.crises;

public class PublicShelter {

    // ── Core ──────────────────────────────────────────────────────────────────
    private String shelterName, location, status, orgName;

    // ── Capacity ──────────────────────────────────────────────────────────────
    private int capacity, occupied, available;

    // ── Occupancy ─────────────────────────────────────────────────────────────
    private double occupancyPct;

    // ── Location ──────────────────────────────────────────────────────────────
    private double lat, lng, distanceKm;

    // ── Recommendation ────────────────────────────────────────────────────────
    private boolean isRecommended    = false;
    private int     recommendationRank = 0;

    // ── Timestamps ────────────────────────────────────────────────────────────
    private String createdAt;

    // ── Constructor ───────────────────────────────────────────────────────────
    public PublicShelter(
            String shelterName,
            String location,
            String status,
            String orgName,
            int    capacity,
            int    occupied,
            int    available,
            double occupancyPct,
            double lat,
            double lng,
            double distanceKm,
            String createdAt
    ) {
        this.shelterName    = shelterName;
        this.location       = location;
        this.status         = status;
        this.orgName        = orgName;
        this.capacity       = capacity;
        this.occupied       = occupied;
        this.available      = available;
        this.occupancyPct   = occupancyPct;
        this.lat            = lat;
        this.lng            = lng;
        this.distanceKm     = distanceKm;
        this.createdAt      = createdAt;
        this.isRecommended  = false;
        this.recommendationRank = 0;
    }

    // ── Getters ───────────────────────────────────────────────────────────────
    public String getShelterName()  { return shelterName; }
    public String getLocation()     { return location; }
    public String getStatus()       { return status; }
    public String getOrgName()      { return orgName; }
    public int    getCapacity()     { return capacity; }
    public int    getOccupied()     { return occupied; }
    public int    getAvailable()    { return available; }
    public double getOccupancyPct() { return occupancyPct; }
    public double getLat()          { return lat; }
    public double getLng()          { return lng; }
    public double getDistanceKm()   { return distanceKm; }
    public String getCreatedAt()    { return createdAt; }

    // ── Recommendation ────────────────────────────────────────────────────────
    public boolean isRecommended()               { return isRecommended; }
    public void    setRecommended(boolean val)   { this.isRecommended = val; }
    public int     getRecommendationRank()       { return recommendationRank; }
    public void    setRecommendationRank(int r)  { this.recommendationRank = r; }

    // ── Distance helpers ──────────────────────────────────────────────────────
    public boolean hasDistance() { return distanceKm >= 0; }

    public String getFormattedDistance() {
        if (distanceKm < 0) return "";
        if (distanceKm < 1) return (int)(distanceKm * 1000) + " m";
        return String.format("%.1f km", distanceKm);
    }
}