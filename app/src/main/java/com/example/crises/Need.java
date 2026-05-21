package com.example.crises;

public class Need {

    String name, category, status, location;
    String address, contact, hours, notes;

    public Need(String name, String category, String status,
                String location, String address,
                String contact, String hours, String notes) {

        this.name = name;
        this.category = category;
        this.status = status;
        this.location = location;
        this.address = address;
        this.contact = contact;
        this.hours = hours;
        this.notes = notes;
    }

    public String getName() { return name; }
    public String getCategory() { return category; }
    public String getStatus() { return status; }
    public String getLocation() { return location; }
    public String getAddress() { return address; }
    public String getContact() { return contact; }
    public String getHours() { return hours; }
    public String getNotes() { return notes; }
}