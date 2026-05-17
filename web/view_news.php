<?php
require_once("class/news.class.php");

if (!isset($_GET['id'])) {
    die("Invalid ID");
}

$id = $_GET['id'];

$new = new news();

$article = $new->getNewsById($id);

if (!$article) {
    die("News not found");
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title><?= $article['title'] ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fb;
            font-family: Arial, sans-serif;
        }

        .article-box {
            background: white;
            max-width: 900px;
            margin: 50px auto;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
        }

        .title {
            font-size: 42px;
            font-weight: bold;
            color: #1b2559;
            margin-bottom: 20px;
        }

        .meta span {
            background: #eef2ff;
            color: #4318ff;
            padding: 8px 15px;
            border-radius: 30px;
            margin-right: 10px;
            font-size: 14px;
        }

        .date {
            color: gray;
            margin-top: 20px;
        }

        .article-image {
            width: 100%;
            border-radius: 15px;
            margin: 35px 0;
            max-height: 450px;
            object-fit: cover;
        }

        .content {
            font-size: 18px;
            line-height: 2;
            color: #333;
            text-align: justify;
        }

        .print-btn {
            margin-top: 40px;
        }

        @media print {
            .print-btn {
                display: none;
            }

            body {
                background: white;
            }

            .article-box {
                box-shadow: none;
                margin: 0;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="article-box">

    <h1 class="title"><?= $article['title'] ?></h1>

    <div class="meta">
        <span><?= $article['category'] ?></span>
        <span><?= $article['type'] ?></span>
        <span><?= $article['status'] ?></span>
    </div>

    <p class="date">
        Published:
        <?= !empty($article['publish_date'])
            ? date("F d, Y", strtotime($article['publish_date']))
            : 'Draft'
        ?>
    </p>

    <?php if (!empty($article['image'])) { ?>

        <img
            src="uploads/<?= $article['image'] ?>"
            class="article-image"
        >

    <?php } ?>

    <div class="content">
        <?= nl2br($article['content']) ?>
    </div>

    <button
        onclick="window.print()"
        class="btn btn-primary print-btn"
    >
        Download / Print PDF
    </button>

</div>

</body>
</html>