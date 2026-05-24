package com.example.crises;

public class Newsss {

    String title, content, category, type, status, image, publishDate, createdAt;
    int id, featured, views;

    public Newsss(int id, String title, String content, String category,
                  String type, String status, int featured, String image,
                  int views, String publishDate, String createdAt) {
        this.id          = id;
        this.title       = title;
        this.content     = content;
        this.category    = category;
        this.type        = type;
        this.status      = status;
        this.featured    = featured;
        this.image       = image;
        this.views       = views;
        this.publishDate = publishDate;
        this.createdAt   = createdAt;
    }

    public int    getId()          { return id; }
    public String getTitle()       { return title; }
    public String getContent()     { return content; }
    public String getCategory()    { return category; }
    public String getType()        { return type; }
    public String getStatus()      { return status; }
    public int    getFeatured()    { return featured; }
    public String getImage()       { return image; }
    public int    getViews()       { return views; }
    public String getPublishDate() { return publishDate; }
    public String getCreatedAt()   { return createdAt; }
}