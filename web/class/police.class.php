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

                GROUP_CONCAT(pu.callsign SEPARATOR ', ')
                as assigned_units

            FROM police_missions pm

            LEFT JOIN police_units pu
            ON pu.current_mission_id = pm.mission_id

            GROUP BY pm.mission_id

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


    public function getTotalAlertsnb()
    {
        $sql = "SELECT COUNT(*) as total
            FROM police_alerts pa
            INNER JOIN organizations o
            ON pa.organization_id = o.id
            WHERE o.type = 'police'";

        $result = $this->getRowSafe($sql);

        return $result['total'];
    }

    public function getSafeZones()
    {
        $sql = "SELECT COUNT(*) as total
            FROM police_zones pz
            INNER JOIN organizations o
            ON pz.organization_id = o.id
            WHERE o.type = 'police'
            AND pz.zone_type = 'safe'";

        $result = $this->getRowSafe($sql);
        return $result['total'];
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

    public function getRecentAlerts()
    {
        $sql = "SELECT
                pa.title,
                pa.severity,
                pa.created_at,
                o.name as organization_name
            FROM police_alerts pa
            INNER JOIN organizations o
            ON pa.organization_id = o.id
            WHERE o.type = 'police'
            ORDER BY pa.created_at DESC ";

        return $this->getdata($sql);
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
    public function addMission($title, $priority, $description, $status, $units = [])
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
                        status = 'on_mission'
                    WHERE unit_id = ?";

                $this->executeSafe($sql2, [
                    $mission_id,
                    $unit_id
                ]);
            }
        }

        return $mission_id;
    }

    public function updateMission($mission_id, $title, $priority, $description, $status, $units = [])
    {
        // update mission
        $sql = "UPDATE police_missions  SET title=?, priority=?, description=?, status=?   WHERE mission_id=?";
        $this->executeSafe($sql, [
            $title,
            $priority,
            $description,
            $status,
            $mission_id
        ]);
        // remove old units
        $sql2 = "UPDATE police_units  SET current_mission_id = NULL,  status = 'available' WHERE current_mission_id = ?";
        $this->executeSafe($sql2, [$mission_id]);
        // assign new units
        if (!empty($units)) {
            foreach ($units as $unit_id) {
                $sql3 = "UPDATE police_units SET current_mission_id = ?, status = 'on_mission' WHERE unit_id = ?";
                $this->executeSafe($sql3, [
                    $mission_id,
                    $unit_id
                ]);
            }
        }
        return true;
    }

    public function getAvailableUnits()
    {

        $sql = "SELECT * FROM police_units
            WHERE status = 'available'";

        return $this->getdata($sql);
    }
}
