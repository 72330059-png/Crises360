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
        $sql = "UPDATE incidents
                SET incident_name = ?,
                    location = ?,
                    severity = ?,
                    status = ?
                 
                WHERE id = ?";

        return $this->executeSafe($sql, [
            $name,
            $location,
            $severity,
            $status,
        
            $id
        ]);
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
}
