<?php

require_once("DAL.class.php");

class news extends DAL
{

  
    public function getAllNews()
    {
        $sql = "SELECT *
                FROM news
                ORDER BY created_at DESC";

        return $this->getdata($sql);
    }


    public function getNewsById($id)
    {
        $sql = "SELECT *
                FROM news
                WHERE id = ?";

        $data = $this->getdata($sql, [$id]);

        return $data ? $data[0] : null;
    }

    public function insertNews(
        $title,
        $content,
        $category,
        $status,
        $featured,
        $image,
        $publish_date
    ) {

        $sql = "INSERT INTO news
                (
                    title,
                    content,
                    category,
                    status,
                    featured,
                    image,
                    publish_date
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        return $this->executeSafe($sql, [
            $title,
            $content,
            $category,
            $status,
            $featured,
            $image,
            $publish_date
        ]);
    }

    public function updateNews(
        $id,
        $title,
        $content,
        $category,
        $status,
        $featured,
        $publish_date
    ) {

        $sql = "UPDATE news
                SET title = ?,
                    content = ?,
                    category = ?,
                    status = ?,
                    featured = ?,
                    publish_date = ?
                WHERE id = ?";

        return $this->executeSafe($sql, [
            $title,
            $content,
            $category,
            $status,
            $featured,
            $publish_date,
            $id
        ]);
    }


    public function updateNewsImage($id, $image)
    {
        $sql = "UPDATE news
                SET image = ?
                WHERE id = ?";

        return $this->executeSafe($sql, [
            $image,
            $id
        ]);
    }


    public function deleteNews($id)
    {
        $sql = "DELETE FROM news
                WHERE id = ?";

        return $this->executeSafe($sql, [$id]);
    }

    public function totalNews()
    {
        $sql = "SELECT COUNT(*) total
                FROM news";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }

    public function publishedNews()
    {
        $sql = "SELECT COUNT(*) total
                FROM news
                WHERE status = 'Published'";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }

    public function draftNews()
    {
        $sql = "SELECT COUNT(*) total
                FROM news
                WHERE status = 'Draft'";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }


    public function featuredNews()
    {
        $sql = "SELECT COUNT(*) total
                FROM news
                WHERE featured = 1";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }


    public function totalViews()
    {
        $sql = "SELECT SUM(views) total
                FROM news";

        $data = $this->getdata($sql);

        return $data[0]['total'] ?? 0;
    }


    public function searchNews($keyword)
    {
        $keyword = "%" . $keyword . "%";

        $sql = "SELECT *
                FROM news
                WHERE title LIKE ?
                OR category LIKE ?
                OR status LIKE ?
                ORDER BY created_at DESC";

        return $this->getdata($sql, [
            $keyword,
            $keyword,
            $keyword
        ]);
    }

    public function filterByStatus($status)
    {
        $sql = "SELECT *
                FROM news
                WHERE status = ?
                ORDER BY created_at DESC";

        return $this->getdata($sql, [$status]);
    }


    public function filterByCategory($category)
    {
        $sql = "SELECT *
                FROM news
                WHERE category = ?
                ORDER BY created_at DESC";

        return $this->getdata($sql, [$category]);
    }


    public function getFeaturedNews()
    {
        $sql = "SELECT *
                FROM news
                WHERE featured = 1
                ORDER BY publish_date DESC";

        return $this->getdata($sql);
    }
}
