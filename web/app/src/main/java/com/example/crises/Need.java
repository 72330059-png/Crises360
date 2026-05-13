package com.example.crises;

public class Need {

    String name, location, type, status, availability;

    public Need(String name, String location, String type,
                String status, String availability) {
        this.name = name;
        this.location = location;
        this.type = type;
        this.status = status;
        this.availability = availability;
    }

    public String getName() { return name; }
    public String getLocation() { return location; }
    public String getType() { return type; }
    public String getStatus() { return status; }
    public String getAvailability() { return availability; }
}