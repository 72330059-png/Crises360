package com.example.crises;

public class Newsss {

    String title, description, source, location, type, pubDate, severity;

    public Newsss(String title, String description, String source,
                  String location, String type, String pubDate, String severity) {

        this.title = title;
        this.description = description;
        this.source = source;
        this.location = location;
        this.type = type;
        this.pubDate = pubDate;
        this.severity = severity;
    }

    public String getTitle() { return title; }
    public String getDescription() { return description; }
    public String getSource() { return source; }
    public String getLocation() { return location; }
    public String getType() { return type; }
    public String getPubDate() { return pubDate; }
    public String getSeverity() { return severity; }
}