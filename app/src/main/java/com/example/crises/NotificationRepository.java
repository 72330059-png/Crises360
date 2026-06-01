package com.example.crises;

import android.content.Context;
import android.content.SharedPreferences;

import java.util.HashSet;
import java.util.Set;

public class NotificationRepository {

    private static final String PREF_NAME    = "notification_prefs";
    private static final String KEY_SEEN     = "seen_ids_";
    private static final String KEY_NOTIFIED = "notified_ids_";
    private static final String KEY_UNREAD   = "unread_count";
    private static final String KEY_SEEDED   = "initial_seed_done";
    private static final String KEY_LOGIN    = "login_date";        // ← NEW

    private final SharedPreferences prefs;

    public NotificationRepository(Context context) {
        prefs = context.getSharedPreferences(PREF_NAME, Context.MODE_PRIVATE);
    }

    // ── Login date ─────────────────────────────────────────────────────────

    /**
     * Returns the date the user last logged in, formatted as "yyyy-MM-dd".
     * Returns null if never set (should not happen in normal flow).
     */
    public String getLoginDate() {
        return prefs.getString(KEY_LOGIN, null);
    }

    // ── UI "new" tracking (blue dot) ───────────────────────────────────────

    /** Returns true if the user has NOT yet seen this item in the list */
    public boolean isNew(String section, String id) {
        Set<String> seen = prefs.getStringSet(KEY_SEEN + section, new HashSet<>());
        return !seen.contains(id);
    }

    /** Called by the UI after rendering — marks items as seen (removes blue dot next visit) */
    public void markSeen(String section, Set<String> ids) {
        Set<String> existing = new HashSet<>(
                prefs.getStringSet(KEY_SEEN + section, new HashSet<>()));
        existing.addAll(ids);
        prefs.edit().putStringSet(KEY_SEEN + section, existing).apply();
    }

    // ── Worker "notified" tracking (system notification) ──────────────────

    /** Returns true if a system notification has already been sent for this id */
    public boolean isNotified(String section, String id) {
        Set<String> notified = prefs.getStringSet(KEY_NOTIFIED + section, new HashSet<>());
        return notified.contains(id);
    }

    /** Called by the Worker after firing a notification */
    public void markNotified(String section, Set<String> ids) {
        Set<String> existing = new HashSet<>(
                prefs.getStringSet(KEY_NOTIFIED + section, new HashSet<>()));
        existing.addAll(ids);
        prefs.edit().putStringSet(KEY_NOTIFIED + section, existing).apply();
    }

    /**
     * SEED: On first run after login, silently mark all items that were created
     * BEFORE the login date as already-notified, so we never surface old data.
     * Items on or after login date are left unseeded and will fire normally.
     */
    public boolean isSeeded() {
        return prefs.getBoolean(KEY_SEEDED, false);
    }

    public void seedNotified(String section, Set<String> ids) {
        markNotified(section, ids);
    }

    public void markSeeded() {
        prefs.edit().putBoolean(KEY_SEEDED, true).apply();
    }

    // ── Badge count ────────────────────────────────────────────────────────

    public int getUnreadCount() {
        return prefs.getInt(KEY_UNREAD, 0);
    }

    public void addUnread(int count) {
        int current = getUnreadCount();
        prefs.edit().putInt(KEY_UNREAD, current + count).apply();
    }

    public void clearUnread() {
        prefs.edit().putInt(KEY_UNREAD, 0).apply();
    }
}