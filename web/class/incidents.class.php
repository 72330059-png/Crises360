<?php

require_once("DAL.class.php");

class incident extends DAL
{

    public function getAllIncidents()
    {
        $sql = "SELECT * FROM incidents ORDER BY reported_at DESC";

        return $this->getdata($sql);
    }

    public function getIncidentById($id)
    {
        $sql = "SELECT * FROM incidents WHERE id = ?";

        $data = $this->getdata($sql, [$id]);

        return $data ? $data[0] : null;
    }

    public function insertIncident($name, $location, $severity, $status, $description)
    {
        $sql = "INSERT INTO incidents
                (incident_name, location, severity, status, description)
                VALUES (?, ?, ?, ?, ?)";

        return $this->executeSafe($sql, [
            $name,
            $location,
            $severity,
            $status,
            $description
        ]);
    }


    public function updateDescription($id, $description)
    {
        $sql = "UPDATE incidents
            SET description = ?
            WHERE id = ?";

        return $this->executeSafe($sql, [
            $description,
            $id
        ]);
    }


    public function updateIncident($id, $name, $location, $severity, $status)
    {
        $current = $this->getdata(
            "SELECT status, resolved_at FROM incidents WHERE id=?",
            [$id]
        );

        if ($status === 'Resolved' && empty($current['resolved_at'])) {

            $sql = "UPDATE incidents
            SET incident_name=?,
                location=?,
                severity=?,
                status=?,
                resolved_at=NOW()
            WHERE id=?";
        } else {

            $sql = "UPDATE incidents
            SET incident_name=?,
                location=?,
                severity=?,
                status=?
            WHERE id=?";
        }

        $result = $this->executeSafe(
            $sql,
            [$name, $location, $severity, $status, $id]
        );
        if ($status === 'Resolved') {
            $this->executeSafe("UPDATE map_alerts SET is_active=0 WHERE incident_id=?", [(int)$id]);
            $this->executeSafe("UPDATE map_zones  SET is_active=0 WHERE incident_id=?", [(int)$id]);
            $this->executeSafe("UPDATE map_roads  SET is_active=0 WHERE incident_id=?", [(int)$id]);
            $this->executeSafe("UPDATE police_roads SET is_active=0 WHERE incident_id=?", [(int)$id]);
            $this->executeSafe("UPDATE map_routes  SET is_active=0 WHERE incident_id=?", [(int)$id]);
            $this->executeSafe(
                "UPDATE police_units SET incident_id=NULL, current_mission_id=NULL, status='available' WHERE incident_id=?",
                [(int)$id]
            );
        }
        if ($status === 'Resolved') {

            $this->executeSafe(
                "UPDATE police_missions pm
         JOIN police_units pu ON pu.current_mission_id = pm.mission_id
         SET pm.status = 'completed'
         WHERE pu.incident_id = ?",
                [(int)$id]
            );
        }
        return $result;
    }


    public function deleteIncident($id)
    {
        $sql = "DELETE FROM incidents WHERE id = ?";

        return $this->executeSafe($sql, [$id]);
    }


    public function totalIncidents()
    {
        $sql = "SELECT COUNT(*) total FROM incidents";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }


    public function activeIncidents()
    {
        $sql = "SELECT COUNT(*) total
                FROM incidents
                WHERE status != 'Resolved'";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }


    public function inProgressIncidents()
    {
        $sql = "SELECT COUNT(*) total
                FROM incidents
                WHERE status = 'In Progress'";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }


    public function resolvedIncidents()
    {
        $sql = "SELECT COUNT(*) total
                FROM incidents
                WHERE status = 'Resolved'";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }


    public function criticalIncidents()
    {
        $sql = "SELECT COUNT(*) total
                FROM incidents
                WHERE severity = 'High'";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }
    public function incidentsThisWeek()
    {
        $sql = "SELECT DAYNAME(reported_at) as day_name,
                   DAYOFWEEK(reported_at) as day_num,
                   COUNT(*) as total
            FROM incidents
            WHERE reported_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DAYNAME(reported_at), DAYOFWEEK(reported_at)
            ORDER BY DAYOFWEEK(reported_at)";

        return $this->getdata($sql);
    }

    public function totalIncidentsThisWeek()
    {
        $sql = "SELECT COUNT(*) total FROM incidents
            WHERE reported_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $data = $this->getdata($sql);
        return $data[0]['total'];
    }

    public function resolvedIncidentsThisWeek()
    {
        $sql = "SELECT COUNT(*) total FROM incidents
            WHERE reported_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND status = 'Resolved'";
        $data = $this->getdata($sql);
        return $data[0]['total'];
    }
    public function incidentsLastMonth()
    {
        $sql = "SELECT DATE(reported_at) as day_date,
                   COUNT(*) as total
            FROM incidents
            WHERE reported_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(reported_at)
            ORDER BY day_date ASC";

        return $this->getdata($sql);
    }

    public function totalIncidentsLastMonth()
    {
        $sql = "SELECT COUNT(*) total FROM incidents
            WHERE reported_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $data = $this->getdata($sql);
        return $data[0]['total'];
    }

    public function resolvedIncidentsLastMonth()
    {
        $sql = "SELECT COUNT(*) total FROM incidents
            WHERE reported_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND status = 'Resolved'";
        $data = $this->getdata($sql);
        return $data[0]['total'];
    }
    public function activeIncidentsLastWeek()
    {
        $sql = "SELECT COUNT(*) total FROM incidents
            WHERE status != 'Resolved'
            AND reported_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
            AND reported_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $data = $this->getdata($sql);
        return $data[0]['total'];
    }
}
