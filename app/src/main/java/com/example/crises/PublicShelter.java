package com.example.crises;

public class PublicShelter {

    private String shelterName;
    private String location;
    private String status;
    private int available;

    public PublicShelter(String shelterName, String location, String status, int available) {
        this.shelterName = shelterName;
        this.location = location;
        this.status = status;
        this.available = available;
    }

    public String getShelterName() {
        return shelterName;
    }

    public String getLocation() {
        return location;
    }

    public String getStatus() {
        return status;
    }

    public int getAvailable() {
        return available;
    }
}