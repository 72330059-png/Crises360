package com.example.crises;

import android.os.Bundle;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.List;

public class PublicShelters extends AppCompatActivity {

    RecyclerView recyclerView;
    PublicShelterAdapter adapter;
    List<PublicShelter> list;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_public_shelters);

        recyclerView = findViewById(R.id.recyclerView);

        list = new ArrayList<>();

        // 🏫 SAMPLE DATA (REALISTIC SHELTERS)

        list.add(new PublicShelter(
                "Beirut Public School",
                "Beirut",
                "Hamra Street, Beirut",
                "03123456",
                "School",
                "Available",
                200,
                150,
                10,
                true,   // food
                true,   // water
                true,   // electricity
                true    // medical
        ));

        list.add(new PublicShelter(
                "Lebanese University Campus",
                "Hadath",
                "Hadath Campus, Beirut",
                "01765432",
                "University",
                "Full",
                500,
                500,
                0,
                true,
                true,
                true,
                false
        ));

        list.add(new PublicShelter(
                "UN Relief Center Saida",
                "Saida",
                "Main Road, Saida",
                "76543210",
                "Institution",
                "Limited",
                300,
                260,
                3,
                true,
                true,
                false,
                true
        ));

        list.add(new PublicShelter(
                "Tyre Community School",
                "Tyre",
                "Old City, Tyre",
                "07888999",
                "School",
                "Available",
                180,
                90,
                20,
                true,
                true,
                true,
                true
        ));

        // 🔹 SETUP ADAPTER
        adapter = new PublicShelterAdapter(list);

        recyclerView.setLayoutManager(new LinearLayoutManager(this));
        recyclerView.setAdapter(adapter);
    }
}