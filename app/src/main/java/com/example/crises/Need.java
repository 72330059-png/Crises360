package com.example.crises;

public class Need {

    String name, location, category, status, quantity, priority;

    public Need(String name, String location, String category,
                String status, String quantity, String priority) {
        this.name = name;
        this.location = location;
        this.category = category;
        this.status = status;
        this.quantity = quantity;
        this.priority = priority;
    }

    public String getName() { return name; }
    public String getLocation() { return location; }
    public String getCategory() { return category; }
    public String getStatus() { return status; }
    public String getQuantity() { return quantity; }
    public String getPriority() { return priority; }
}