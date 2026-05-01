package com.example.crises;

import android.os.Bundle;
import android.util.Log;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;

public class News extends AppCompatActivity {

    RecyclerView recyclerView;
    ArrayList<Newsss> list;
    NewsAdapter adapter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_news);

        recyclerView = findViewById(R.id.recyclerView);
        recyclerView.setLayoutManager(new LinearLayoutManager(this));

        list = new ArrayList<>();
        adapter = new NewsAdapter(list);
        recyclerView.setAdapter(adapter);

        loadNews();
    }

    private void loadNews() {

        new Thread(() -> {

            try {

                URL url = new URL("http://10.0.2.2/crises_api/get_news.php");
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("GET");
                conn.setConnectTimeout(10000);
                conn.setReadTimeout(10000);

                BufferedReader br = new BufferedReader(
                        new InputStreamReader(conn.getInputStream())
                );

                StringBuilder sb = new StringBuilder();
                String line;

                while ((line = br.readLine()) != null) {
                    sb.append(line);
                }

                JSONArray array = new JSONArray(sb.toString());

                list.clear();

                for (int i = 0; i < array.length(); i++) {

                    JSONObject obj = array.getJSONObject(i);

                    list.add(new Newsss(
                            obj.optString("title"),
                            obj.optString("description"),
                            obj.optString("source"),
                            obj.optString("location"),
                            obj.optString("type"),
                            obj.optString("pubDate"),
                            obj.optString("severity")
                    ));
                }

                runOnUiThread(() -> adapter.notifyDataSetChanged());

            } catch (Exception e) {
                Log.e("NEWS_ERROR", e.getMessage());
            }
        }).start();
    }
}