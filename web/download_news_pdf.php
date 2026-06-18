<?php
require 'vendor/autoload.php';
require_once("class/news.class.php");

use Dompdf\Dompdf;

$id = $_GET['id'];

$news = new news();
$article = $news->getNewsById($id);

$dompdf = new Dompdf();

$html = '
<h1>'.$article['title'].'</h1>
<p>Category: '.$article['category'].'</p>
<p>Status: '.$article['status'].'</p>
<p>Date: '.$article['publish_date'].'</p>
<hr>
'.$article['content'].'
';

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$filename = preg_replace('/[^A-Za-z0-9\- ]/', '', $article['title']);

$dompdf->stream(
    $filename . ".pdf",
    ["Attachment" => true]
);
?>