package com.example.crises;

public class AlertModel {

    public int    id;
    public String severity;
    public String message;
    public String location;
    public String time;

    public AlertModel(int id, String severity, String message,
                      String location, String time) {
        this.id       = id;
        this.severity = severity;
        this.message  = message;
        this.location = location;
        this.time     = time;
    }

    public String getSeverity() { return severity; }
    public String getMessage()  { return message;  }
    public String getLocation() { return location; }
    public String getTime()     { return time;     }
}