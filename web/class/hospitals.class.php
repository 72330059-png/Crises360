<?php

require_once("DAL.class.php");

class hospital extends DAL
{
    public function getAllHospitals()
    {
        $sql = "SELECT
                h.*,
                o.name,o.location,o.id AS org_id,

                (h.total_beds - h.available_beds) AS occupied_beds,

                (
                    SELECT COUNT(*)
                    FROM hospital_teams ht
                    WHERE ht.hospital_id = h.id
                ) AS total_teams

            FROM hospitals h

            INNER JOIN organizations o
            ON h.organization_id = o.id

            WHERE o.type = 'hospital'

            ORDER BY o.created_at DESC";

        return $this->getdata($sql);
    }


    // public function insertHospital(
    //     $name,
    //     $location,
    //     $email,
    //     $password,
    //     $total_beds,
    //     $hospital_status
    // ) {
    //     // hash password
    //     $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    //     $type = "hospital";
    //     $available_beds = $total_beds;
    //     $sqlOrg = "INSERT INTO organizations (name, type, location, email, password) VALUES (?, ?, ?, ?, ?)";
    //     $organization_id = $this->executeSafe($sqlOrg, [
    //         $name,
    //         $type,
    //         $location,
    //         $email,
    //         $hashed_password
    //     ]);
    //     if (!$organization_id || is_array($organization_id)) {
    //         return $organization_id;
    //     }
    //     $sqlHospital = "INSERT INTO hospitals
    //            (organization_id,total_beds,available_beds, hospital_status) VALUES (?, ?, ?, ?)";
    //     return $this->executeSafe($sqlHospital, [
    //         $organization_id,
    //         $total_beds,
    //         $available_beds,
    //         $hospital_status
    //     ]);
    // }
    public function insertHospital(
        $name,
        $location,
        $email,
        $password,
        $total_beds,
        $hospital_status
    ) {

        // HASH PASSWORD
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $type = "hospital";

        // available beds initially = total beds
        $available_beds = $total_beds;

        // INSERT INTO ORGANIZATIONS
        $sqlOrg = "INSERT INTO organizations
    (name, type, location, email, password)
    VALUES (?, ?, ?, ?, ?)";

        $organization_id = $this->executeSafe($sqlOrg, [
            $name,
            $type,
            $location,
            $email,
            $hashed_password
        ]);

        // IF FIRST INSERT FAILED
        if (!$organization_id || is_array($organization_id)) {
            return $organization_id;
        }

        // INSERT INTO HOSPITALS
        $sqlHospital = "INSERT INTO hospitals
    (
        organization_id,
        total_beds,
        available_beds,
        hospital_status
    )
    VALUES (?, ?, ?, ?)";

        $hospital = $this->executeSafe($sqlHospital, [
            $organization_id,
            $total_beds,
            $available_beds,
            $hospital_status
        ]);

        // IF SECOND INSERT FAILED
        if (!$hospital || is_array($hospital)) {

            // DELETE ORGANIZATION TO AVOID BROKEN DATA
            $sqlDelete = "DELETE FROM organizations WHERE id = ?";

            $this->executeSafe($sqlDelete, [$organization_id]);

            return $hospital;
        }

        return true;
    }
    public function deleteHospital($id)
    {
        $sql = "DELETE FROM organizations WHERE id = ?";

        return $this->executeSafe($sql, [$id]);
    }


    public function totalHospitals()
    {
        $sql = "SELECT COUNT(*) total FROM hospitals";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }


    public function totalAvailableBeds()
    {
        $sql = "SELECT SUM(available_beds) total
                FROM hospitals";

        $data = $this->getdata($sql);

        return $data[0]['total'] ?? 0;
    }


    public function totalOccupiedBeds()
    {
        $sql = "SELECT SUM(total_beds - available_beds) total
                FROM hospitals";

        $data = $this->getdata($sql);

        return $data[0]['total'] ?? 0;
    }


    public function occupancyRate()
    {
        $sql = "SELECT 
                    ROUND(
                        (
                            SUM(total_beds - available_beds)
                            /
                            SUM(total_beds)
                        ) * 100
                    ) AS rate
                FROM hospitals";

        $data = $this->getdata($sql);

        return $data[0]['rate'] ?? 0;
    }


    public function totalAvailableICU()
    {
        $sql = "SELECT SUM(available_icu_beds) total
                FROM hospitals";

        $data = $this->getdata($sql);

        return $data[0]['total'] ?? 0;
    }


    public function availableHospitals()
    {
        $sql = "SELECT COUNT(*) total
            FROM hospitals
            WHERE hospital_status = 'Stable'";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }

    // teams

    public function getHospitalTeams($hospital_id)
    {
        $sql = "SELECT *
                FROM hospital_teams
                WHERE hospital_id = ?
                ORDER BY created_at DESC";

        return $this->getdata($sql, [$hospital_id]);
    }


    public function getTeamById($id)
    {
        $sql = "SELECT *
                FROM hospital_teams
                WHERE id = ?";

        $data = $this->getdata($sql, [$id]);

        return $data ? $data[0] : null;
    }


    public function insertTeam(
        $hospital_id,
        $team_name,
        $leader_name,
        $specialization,
        $members_count,
        $status,
        $current_location
    ) {

        $sql = "INSERT INTO hospital_teams
                (
                    hospital_id,
                    team_name,
                    leader_name,
                    specialization,
                    members_count,
                    status,
                    current_location
                )
                VALUES
                (?, ?, ?, ?, ?, ?, ?)";

        return $this->executeSafe($sql, [
            $hospital_id,
            $team_name,
            $leader_name,
            $specialization,
            $members_count,
            $status,
            $current_location
        ]);
    }


    public function updateTeam(
        $id,
        $team_name,
        $leader_name,
        $specialization,
        $members_count,
        $status,
        $current_location
    ) {

        $sql = "UPDATE hospital_teams
                SET
                    team_name = ?,
                    leader_name = ?,
                    specialization = ?,
                    members_count = ?,
                    status = ?,
                    current_location = ?
                WHERE id = ?";

        return $this->executeSafe($sql, [
            $team_name,
            $leader_name,
            $specialization,
            $members_count,
            $status,
            $current_location,
            $id
        ]);
    }


    public function deleteTeam($id)
    {
        $sql = "DELETE FROM hospital_teams
                WHERE id = ?";

        return $this->executeSafe($sql, [$id]);
    }


    //   teams

    public function totalTeams($hospital_id)
    {
        $sql = "SELECT COUNT(*) total
                FROM hospital_teams
                WHERE hospital_id = ?";

        $data = $this->getdata($sql, [$hospital_id]);

        return $data[0]['total'];
    }


    public function availableTeams($hospital_id)
    {
        $sql = "SELECT COUNT(*) total
                FROM hospital_teams
                WHERE hospital_id = ?
                AND status = 'Available'";

        $data = $this->getdata($sql, [$hospital_id]);

        return $data[0]['total'];
    }


    public function onMissionTeams($hospital_id)
    {
        $sql = "SELECT COUNT(*) total
                FROM hospital_teams
                WHERE hospital_id = ?
                AND status = 'On Mission'";

        $data = $this->getdata($sql, [$hospital_id]);

        return $data[0]['total'];
    }


    public function busyTeams($hospital_id)
    {
        $sql = "SELECT COUNT(*) total
                FROM hospital_teams
                WHERE hospital_id = ?
                AND status = 'Busy'";

        $data = $this->getdata($sql, [$hospital_id]);

        return $data[0]['total'];
    }


    public function teamsNeedingSupport($hospital_id)
    {
        $sql = "SELECT COUNT(*) total
                FROM hospital_teams
                WHERE hospital_id = ?
                AND members_count <= 3";

        $data = $this->getdata($sql, [$hospital_id]);

        return $data[0]['total'];
    }

    public function getHospitalById($id)
    {
        $sql = "SELECT hospitals.*, organizations.name
            FROM hospitals
            JOIN organizations
            ON hospitals.organization_id = organizations.id
            WHERE hospitals.id = ?";

        $data = $this->getdata($sql, [$id]);

        return $data[0];
    }
}
