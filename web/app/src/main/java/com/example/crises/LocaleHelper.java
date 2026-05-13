package com.example.crises;

import android.content.Context;
import android.os.Build;

import java.util.Locale;

public class LocaleHelper {

    public static Context setLocale(Context context, String lang) {

        Locale locale = new Locale(lang);
        Locale.setDefault(locale);

        android.content.res.Configuration config = new android.content.res.Configuration();

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.N) {
            config.setLocale(locale);
            config.setLayoutDirection(locale);
            return context.createConfigurationContext(config);
        } else {
            config.locale = locale;
            config.setLayoutDirection(locale);
            context.getResources().updateConfiguration(config,
                    context.getResources().getDisplayMetrics());
            return context;
        }
    }
}