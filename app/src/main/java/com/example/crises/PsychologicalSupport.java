package com.example.crises;

import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.view.View;
import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;
import com.example.crises.R;

public class PsychologicalSupport extends AppCompatActivity {

    private static final String HOTLINE_NUMBER = "1564";

    private CardView cardCounselor;
    private CardView cardSelfHelp;
    private CardView cardHotline;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_psychological_support);

        cardCounselor = findViewById(R.id.cardCounselor);
        cardSelfHelp  = findViewById(R.id.cardSelfHelp);
        cardHotline   = findViewById(R.id.cardHotline);

        // Back button
        findViewById(R.id.btnBack).setOnClickListener(v -> finish());

        // Open AI Counselor chat
        cardCounselor.setOnClickListener(v -> {
            Intent intent = new Intent(this, CounselorChat.class);
            startActivity(intent);
        });

        // Open Self-Help Tools
        cardSelfHelp.setOnClickListener(v -> {
            Intent intent = new Intent(this, SelfHelp.class);
            startActivity(intent);
        });

        // Dial emergency hotline directly
        cardHotline.setOnClickListener(v -> {
            Intent dialIntent = new Intent(Intent.ACTION_DIAL);
            dialIntent.setData(Uri.parse("tel:" + HOTLINE_NUMBER));
            startActivity(dialIntent);
        });
    }
}