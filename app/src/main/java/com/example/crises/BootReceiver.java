package com.example.crises;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.util.Log;

import androidx.work.Constraints;
import androidx.work.ExistingPeriodicWorkPolicy;
import androidx.work.NetworkType;
import androidx.work.PeriodicWorkRequest;
import androidx.work.WorkManager;

import java.util.concurrent.TimeUnit;

/**
 * Restarts the NotificationWorker after the device reboots.
 * WorkManager periodic tasks are cancelled when the phone turns off —
 * this receiver re-schedules them automatically on boot.
 *
 * Registered in AndroidManifest.xml with BOOT_COMPLETED intent filter.
 */
public class BootReceiver extends BroadcastReceiver {

    private static final String TAG = "BootReceiver";

    @Override
    public void onReceive(Context context, Intent intent) {
        if (!Intent.ACTION_BOOT_COMPLETED.equals(intent.getAction())) return;

        Log.d(TAG, "Device booted — re-scheduling NotificationWorker");

        PeriodicWorkRequest workRequest =
                new PeriodicWorkRequest.Builder(
                        NotificationWorker.class,
                        15, TimeUnit.MINUTES)
                        .setInitialDelay(30, TimeUnit.SECONDS) // small delay after boot
                        .setConstraints(new Constraints.Builder()
                                .setRequiredNetworkType(NetworkType.CONNECTED)
                                .build())
                        .build();

        WorkManager.getInstance(context).enqueueUniquePeriodicWork(
                "crises_notif_worker",
                ExistingPeriodicWorkPolicy.KEEP,
                workRequest);

        Log.d(TAG, "NotificationWorker re-scheduled successfully");
    }
}