package com.example.crises;

public class Hospital {

    String name, location, region, status;
    int totalBeds, availableBeds, occupiedBeds;

    public Hospital(String name, String location, String region,
                    int totalBeds, int availableBeds, int occupiedBeds,
                    String status) {

        this.name = name;
        this.location = location;
        this.region = region;
        this.totalBeds = totalBeds;
        this.availableBeds = availableBeds;
        this.occupiedBeds = occupiedBeds;
        this.status = status;
    }

    public String getName() { return name; }
    public String getLocation() { return location; }
    public String getRegion() { return region; }
    public int getTotalBeds() { return totalBeds; }
    public int getAvailableBeds() { return availableBeds; }
    public int getOccupiedBeds() { return occupiedBeds; }
    public String getStatus() { return status; }
}