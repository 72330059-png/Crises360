<?php

require_once("DAL.class.php");

class police extends DAL
{

    public function getTotalUnits()
    {
        $sql = "SELECT COUNT(*) as total
            FROM police_units pu
            INNER JOIN organizations o
            ON pu.organization_id = o.id
            WHERE o.type = 'police'";

        $result = $this->getRowSafe($sql);

        return $result['total'];
    }
    public function getPoliceMissions()
    {
        $sql = "SELECT
                pm.mission_id,
                pm.priority,
                pm.title,
                pm.description,
                pm.status,
                pm.created_at,
                MIN(pu.incident_id) AS incident_id,
                GROUP_CONCAT(pu.callsign SEPARATOR ', ') AS assigned_units,
                GROUP_CONCAT(DISTINCT i.incident_name SEPARATOR ', ') AS incident_name,
                GROUP_CONCAT(DISTINCT i.location SEPARATOR ', ') AS incident_location
            FROM police_missions pm
            LEFT JOIN police_units pu ON pu.current_mission_id = pm.mission_id
            LEFT JOIN incidents i ON pu.incident_id = i.id
            GROUP BY 
                pm.mission_id,
                pm.priority,
                pm.title,
                pm.description,
                pm.status,
                pm.created_at
            ORDER BY pm.created_at DESC";

        return $this->getdata($sql);
    }


    public function getPoliceUnits()
    {
        $sql = "SELECT
                pu.unit_id,
                pu.callsign,
                pu.unit_type,
                pu.status,
                pm.title as mission_title,
                o.name as organization_name,
                o.location ,
                o.id as organization_id
            FROM police_units pu

            INNER JOIN organizations o
            ON pu.organization_id = o.id

            LEFT JOIN police_missions pm
            ON pu.current_mission_id = pm.mission_id

            WHERE o.type = 'police'

            ORDER BY pu.last_updated DESC";

        return $this->getdata($sql);
    }

    public function getBlockedRoads()
    {
        $sql = "SELECT COUNT(*) as total
            FROM police_roads pr
            INNER JOIN organizations o
            ON pr.organization_id = o.id
            WHERE o.type = 'police'
            AND pr.road_type = 'blocked'";

        $result = $this->getRowSafe($sql);
        return $result['total'];
    }

    public function getUnitsOnMission()
    {
        $sql = "SELECT COUNT(*) as total
            FROM police_units
            WHERE status = 'on_mission'";

        $result = $this->getRowSafe($sql);

        return $result['total'];
    }

    public function addPoliceUnit(
        $name,
        $location,
        $email,
        $password,
        $callsign,
        $unit_type
    ) {
        // hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $type = "police";
        // $available_beds = $total_beds;
        $sqlOrg = "INSERT INTO organizations (name, type, location, email, password) VALUES (?, ?, ?, ?, ?)";
        $organization_id = $this->executeSafe($sqlOrg, [
            $name,
            $type,
            $location,
            $email,
            $hashed_password
        ]);
        if (!$organization_id || is_array($organization_id)) {
            return false;
        }
        $sqlpolice = "INSERT INTO police_units
               (organization_id,current_mission_id,callsign,unit_type,status) VALUES (?,?,?,?,?)";
        return $this->executeSafe($sqlpolice, [
            $organization_id,
            NULL,
            $callsign,
            $unit_type,
            'available'
        ]);
    }

    public function countAvailableUnits()
    {
        $sql = "SELECT COUNT(*) as total
            FROM police_units
            WHERE status = 'available'";
        $result = $this->getRowSafe($sql);
        return $result['total'];
    }
    public function deletepolice($id)
    {
        $sql = "DELETE FROM organizations WHERE id = ?";

        return $this->executeSafe($sql, [$id]);
    }

    public function updatepolice($unit_id, $org_id, $name, $location, $callsign, $type, $status)
    {
        $sql1 = "UPDATE  organizations SET  name = ?, location = ?  WHERE id = ?";

        $this->executeSafe($sql1, [
            $name,
            $location,
            $org_id
        ]);

        $sql2 = "UPDATE  police_units SET  callsign = ?, unit_type = ?, status=?  WHERE unit_id = ?";
        return $this->executesafe($sql2, [
            $callsign,
            $type,
            $status,
            $unit_id
        ]);
    }


    public function getUnitsForMission()
    {
        $sql = "SELECT
                unit_id,
                callsign
            FROM police_units
            ORDER BY callsign ASC";

        return $this->getdata($sql);
    }
    public function addMission($title, $priority, $description, $status, $units = [], $incident_id = 0)
    {
        // insert mission
        $sql = "INSERT INTO police_missions
            (title, priority, description, status)
            VALUES (?, ?, ?, ?)";

        $mission_id = $this->executeSafe($sql, [
            $title,
            $priority,
            $description,
            $status
        ]);

        // update selected units
        if ($mission_id && !empty($units)) {

            foreach ($units as $unit_id) {

                $sql2 = "UPDATE police_units
                    SET current_mission_id = ?,
                        status = 'pending',
                        incident_id = ?
                    WHERE unit_id = ?";

                $this->executeSafe($sql2, [
                    $mission_id,
                    (int)$incident_id,
                    $unit_id
                ]);
            }
        }

        return $mission_id;
    }

   
    public function updateMission($mission_id, $title, $priority, $description, $status, $units = null, $incident_id = 0)
    {
        $this->executeSafe(
            "UPDATE police_missions SET title=?, priority=?, description=?, status=? WHERE mission_id=?",
            [
                $this->escape($title),
                $this->escape($priority),
                $this->escape($description),
                $this->escape($status),
                (int)$mission_id
            ]
        );

        if ($units !== null && !empty($units)) {
            // Clear old
            $this->executeSafe(
                "UPDATE police_units SET current_mission_id=NULL, incident_id=NULL, status='available' 
              WHERE current_mission_id=?",
                [(int)$mission_id]
            );
            // Assign new
            foreach ($units as $unit_id) {
                $this->executeSafe(
                    "UPDATE police_units SET current_mission_id=?, status='pending', incident_id=? 
                  WHERE unit_id=?",
                    [(int)$mission_id, (int)$incident_id, (int)$unit_id]
                );
            }
        } elseif ($incident_id > 0) {
            $this->executeSafe(
                "UPDATE police_units SET incident_id=? WHERE current_mission_id=?",
                [(int)$incident_id, (int)$mission_id]
            );
        }

        return true;
    }
    public function getAvailableUnits()
    {

        $sql = "SELECT * FROM police_units
            WHERE status = 'available'";

        return $this->getdata($sql);
    }
    public function getSafeRoadsCount()
    {
        $row = $this->getRowSafe(
            "SELECT COUNT(*) as total FROM police_roads 
         WHERE road_type = 'safe' AND is_active = 1"
        );
        return $row ? (int)$row['total'] : 0;
    }

    public function getEvacRoutesCount()
    {
        $row = $this->getRowSafe(
            "SELECT COUNT(*) as total FROM map_routes"
        );
        return $row ? (int)$row['total'] : 0;
    }

    public function getRecentPoliceUpdates()
    {
        // Get last 5 from police_roads
        $roads = $this->getdata(
            "SELECT 
            pr.road_name AS title,
            pr.road_type AS severity,
            pr.created_at,
            o.name AS organization_name,
            'road' AS update_type
         FROM police_roads pr
         JOIN organizations o ON pr.organization_id = o.id
         WHERE pr.is_active = 1
         ORDER BY pr.created_at DESC LIMIT 5"
        );

        // Get last 5 from map_routes
        $routes = $this->getdata(
            "SELECT 
            CONCAT(mr.from_name, ' → ', mr.to_name) AS title,
            mr.route_status AS severity,
            mr.created_at,
            o.name AS organization_name,
            'route' AS update_type
         FROM map_routes mr
         JOIN organizations o ON mr.organization_id = o.id
         ORDER BY mr.created_at DESC LIMIT 5"
        );

        // Merge and sort by date
        $all = array_merge($roads, $routes);
        usort($all, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return array_slice($all, 0, 3);
    }
}
