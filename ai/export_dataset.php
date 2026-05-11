<?php
require_once("../class/AiDataset.class.php");

$ai = new AiDataset();

/* 1️⃣ Build dataset from DB */
$data = $ai->buildTrainingDataset();

/* 2️⃣ Save JSON where Python expects it */
$file = __DIR__ . "/ai_dataset.json";
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

echo "✅ AI dataset created: " . count($data);
