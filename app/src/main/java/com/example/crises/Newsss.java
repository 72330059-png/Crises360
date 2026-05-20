package com.example.crises;

public class Newsss {

    String title, content, category, type, publishDate;

    public Newsss(String title, String content, String category,
                  String type, String publishDate) {

        this.title = title;
        this.content = content;
        this.category = category;
        this.type = type;
        this.publishDate = publishDate;
    }

    public String getTitle() { return title; }
    public String getContent() { return content; }
    public String getCategory() { return category; }
    public String getType() { return type; }
    public String getPublishDate() { return publishDate; }
}