<?php

require_once("DAL.class.php");

class alert extends DAL
{

    // =========================
    // GET ALL ALERTS
    // =========================
    public function getRegions()
    {
        $sql = "SELECT DISTINCT region
            FROM alerts
            ORDER BY region ASC";

        return $this->getdata($sql);
    }
    public function getAllAlerts()
    {
        $sql = "SELECT * 
                FROM alerts
                ORDER BY created_at DESC";

        return $this->getdata($sql);
    }

    public function sentTodayAlerts()
    {
        $sql = "SELECT COUNT(*) total
            FROM alerts
            WHERE status = 'Sent'
            AND DATE(created_at) = CURDATE()";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }
    // =========================
    // GET ALERT BY ID
    // =========================
    public function getAlertById($id)
    {
        $sql = "SELECT * 
                FROM alerts
                WHERE id = ?";

        $data = $this->getdata($sql, [$id]);

        return $data ? $data[0] : null;
    }


    // =========================
    // INSERT ALERT
    // =========================
    public function insertAlert($alert_message, $severity, $region, $status)
    {
        $sql = "INSERT INTO alerts
                (alert_message, severity, region, status)
                VALUES (?, ?, ?, ?)";

        return $this->executeSafe($sql, [
            $alert_message,
            $severity,
            $region,
            $status
        ]);
    }


    // =========================
    // UPDATE ALERT
    // =========================
    public function updateAlert($id, $alert_message, $severity, $region, $status)
    {
        $sql = "UPDATE alerts
                SET alert_message = ?,
                    severity = ?,
                    region = ?,
                    status = ?
                WHERE id = ?";

        return $this->executeSafe($sql, [
            $alert_message,
            $severity,
            $region,
            $status,
            $id
        ]);
    }


    // =========================
    // DELETE ALERT
    // =========================
    public function deleteAlert($id)
    {
        $sql = "DELETE FROM alerts
                WHERE id = ?";

        return $this->executeSafe($sql, [$id]);
    }


    // =========================
    // TOTAL ALERTS
    // =========================
    public function totalAlerts()
    {
        $sql = "SELECT COUNT(*) total
                FROM alerts";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }


    // =========================
    // SENT ALERTS
    // =========================
    public function sentAlerts()
    {
        $sql = "SELECT COUNT(*) total
                FROM alerts
                WHERE status = 'Sent'";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }


    // =========================
    // PENDING ALERTS
    // =========================
    public function pendingAlerts()
    {
        $sql = "SELECT COUNT(*) total
                FROM alerts
                WHERE status = 'Pending'";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }


    // =========================
    // CRITICAL ALERTS
    // =========================
    public function criticalAlerts()
    {
        $sql = "SELECT COUNT(*) total
                FROM alerts
                WHERE severity = 'Critical'";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }


    // =========================
    // TOTAL RECIPIENTS
    // =========================
    public function totalRecipients()
    {
        $sql = "SELECT SUM(recepients_count) total
                FROM alerts";

        $data = $this->getdata($sql);

        return $data[0]['total'] ?? 0;
    }


    // =========================
    // TODAY ALERTS
    // =========================
    public function todayAlerts()
    {
        $sql = "SELECT COUNT(*) total
                FROM alerts
                WHERE DATE(created_at) = CURDATE()";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }


    // =========================
    // SEARCH ALERTS
    // =========================
    public function searchAlerts($keyword)
    {
        $keyword = "%" . $keyword . "%";

        $sql = "SELECT *
                FROM alerts
                WHERE alert_message LIKE ?
                OR region LIKE ?
                OR severity LIKE ?
                OR status LIKE ?
                ORDER BY alert_date DESC";

        return $this->getdata($sql, [
            $keyword,
            $keyword,
            $keyword,
            $keyword
        ]);
    }


    // =========================
    // FILTER BY STATUS
    // =========================
    public function filterByStatus($status)
    {
        $sql = "SELECT *
                FROM alerts
                WHERE status = ?
                ORDER BY alert_date DESC";

        return $this->getdata($sql, [$status]);
    }


    // =========================
    // FILTER BY REGION
    // =========================
    public function filterByRegion($region)
    {
        $sql = "SELECT *
                FROM alerts
                WHERE region = ?
                ORDER BY alert_date DESC";

        return $this->getdata($sql, [$region]);
    }


    // =========================
    // FILTER BY DATE
    // =========================
    public function filterByDate($date)
    {
        $sql = "SELECT *
                FROM alerts
                WHERE DATE(alert_date) = ?
                ORDER BY alert_date DESC";

        return $this->getdata($sql, [$date]);
    }

    // =========================
// TOTAL ALERTS LAST WEEK
// =========================
public function totalAlertsLastWeek()
{
    $sql = "SELECT COUNT(*) total
            FROM alerts
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 2 WEEK)
              AND created_at < DATE_SUB(NOW(), INTERVAL 1 WEEK)";

    $data = $this->getdata($sql);

    return $data[0]['total'] ?? 0;
}


// =========================
// GET RECENT ALERTS
// =========================
public function getRecentAlerts($limit = 4)
{
    $sql = "SELECT *
            FROM alerts
            ORDER BY created_at DESC
            LIMIT ?";

    return $this->getdata($sql, [$limit]);
}
}
