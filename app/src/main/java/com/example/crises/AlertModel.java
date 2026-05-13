package com.example.crises;

public class AlertModel {

    public int id;
    public String type;
    public String message;
    public String location;
    public String time;
    public String status;

    public AlertModel(int id, String type, String message,
                      String location, String time, String status) {
        this.id = id;
        this.type = type;
        this.message = message;
        this.location = location;
        this.time = time;
        this.status = status;
    }

    public String getType() { return type; }
    public String getMessage() { return message; }
    public String getLocation() { return location; }
    public String getTime() { return time; }
}