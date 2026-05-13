package com.example.crises;

public class PublicShelter {

    private String name;
    private String location;
    private String address;
    private String phone;
    private String type;
    private String status;

    private int capacity;
    private int current;
    private int emptyRooms;

    private boolean food;
    private boolean water;
    private boolean electricity;
    private boolean medical;

    // 🔹 CONSTRUCTOR
    public PublicShelter(String name, String location, String address, String phone,
                         String type, String status,
                         int capacity, int current, int emptyRooms,
                         boolean food, boolean water, boolean electricity, boolean medical) {

        this.name = name;
        this.location = location;
        this.address = address;
        this.phone = phone;
        this.type = type;
        this.status = status;

        this.capacity = capacity;
        this.current = current;
        this.emptyRooms = emptyRooms;

        this.food = food;
        this.water = water;
        this.electricity = electricity;
        this.medical = medical;
    }

    // 🔹 GETTERS
    public String getName() {
        return name;
    }

    public String getLocation() {
        return location;
    }

    public String getAddress() {
        return address;
    }

    public String getPhone() {
        return phone;
    }

    public String getType() {
        return type;
    }

    public String getStatus() {
        return status;
    }

    public int getCapacity() {
        return capacity;
    }

    public int getCurrent() {
        return current;
    }

    public int getEmptyRooms() {
        return emptyRooms;
    }

    public boolean isFood() {
        return food;
    }

    public boolean isWater() {
        return water;
    }

    public boolean isElectricity() {
        return electricity;
    }

    public boolean isMedical() {
        return medical;
    }
}