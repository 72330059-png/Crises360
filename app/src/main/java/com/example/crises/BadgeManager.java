package com.example.crises;

import android.content.Context;
import android.content.SharedPreferences;

import java.util.HashSet;
import java.util.List;
import java.util.Set;

public class BadgeManager {

    private static final String PREF_NAME    = "alert_prefs";
    private static final String KEY_READ_IDS = "read_alert_ids";

    public static void markAllAsRead(Context context, List<Integer> ids) {
        SharedPreferences prefs = context.getSharedPreferences(
                PREF_NAME, Context.MODE_PRIVATE);
        Set<String> readIds = new HashSet<>();
        for (int id : ids) readIds.add(String.valueOf(id));
        prefs.edit().putStringSet(KEY_READ_IDS, readIds).apply();
    }

    public static int getUnreadCount(Context context, List<Integer> allIds) {
        SharedPreferences prefs = context.getSharedPreferences(
                PREF_NAME, Context.MODE_PRIVATE);
        Set<String> readIds = prefs.getStringSet(KEY_READ_IDS, new HashSet<>());
        int unread = 0;
        for (int id : allIds) {
            if (!readIds.contains(String.valueOf(id))) unread++;
        }
        return unread;
    }
}