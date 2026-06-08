package com.example.crises;

public class Hospital {

    private String name, location, region, status;
    private String phone;
    private int    totalBeds, availableBeds, occupiedBeds;
    private double occupancyPct;
    private double lat, lng, distanceKm;
    private boolean isRecommended     = false;
    private int     recommendationRank = 0;
    private String  updatedAt;

    public Hospital(
            String name, String location, String region,
            String phone,
            int    totalBeds, int availableBeds, int occupiedBeds,
            double occupancyPct,
            double lat, double lng, double distanceKm,
            String status, String updatedAt
    ) {
        this.name              = name;
        this.location          = location;
        this.region            = region;
        this.phone             = phone;
        this.totalBeds         = totalBeds;
        this.availableBeds     = availableBeds;
        this.occupiedBeds      = occupiedBeds;
        this.occupancyPct      = occupancyPct;
        this.lat               = lat;
        this.lng               = lng;
        this.distanceKm        = distanceKm;
        this.status            = status;
        this.updatedAt         = updatedAt;
        this.isRecommended     = false;
        this.recommendationRank = 0;
    }

    public String  getName()         { return name; }
    public String  getLocation()     { return location; }
    public String  getRegion()       { return region; }
    public String  getPhone()        { return phone; }
    public int     getTotalBeds()    { return totalBeds; }
    public int     getAvailableBeds(){ return availableBeds; }
    public int     getOccupiedBeds() { return occupiedBeds; }
    public double  getOccupancyPct() { return occupancyPct; }
    public double  getLat()          { return lat; }
    public double  getLng()          { return lng; }
    public double  getDistanceKm()   { return distanceKm; }
    public String  getStatus()       { return status; }
    public String  getUpdatedAt()    { return updatedAt; }

    public boolean isRecommended()               { return isRecommended; }
    public void    setRecommended(boolean val)   { this.isRecommended = val; }
    public int     getRecommendationRank()       { return recommendationRank; }
    public void    setRecommendationRank(int r)  { this.recommendationRank = r; }

    public boolean hasDistance() { return distanceKm >= 0; }

    public String getFormattedDistance() {
        if (distanceKm < 0) return "";
        if (distanceKm < 1) return (int)(distanceKm * 1000) + " m";
        return String.format("%.1f km", distanceKm);
    }
}