package com.example.crises;

public class House {

    private String city;
    private String description;
    private String price;
    private String phone;

    public House(String city, String description, String price, String phone) {
        this.city = city;
        this.description = description;
        this.price = price;
        this.phone = phone;
    }

    public String getCity() {
        return city;
    }

    public String getDescription() {
        return description;
    }

    public String getPrice() {
        return price;
    }

    public String getPhone() {
        return phone;
    }
}