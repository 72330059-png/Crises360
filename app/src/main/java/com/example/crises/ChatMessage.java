package com.example.crises;

public class ChatMessage {

    public static final int TYPE_USER = 0;
    public static final int TYPE_AI   = 1;

    private String text;
    private int    type;
    private String time;

    public ChatMessage(String text, int type, String time) {
        this.text = text;
        this.type = type;
        this.time = time;
    }

    public String getText() { return text; }
    public int    getType() { return type; }
    public String getTime() { return time; }
}