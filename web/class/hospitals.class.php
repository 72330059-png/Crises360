<?php

require_once("DAL.class.php");

class hospital extends DAL
{

 

    public function getAllHospitals()
    {
        $sql = "SELECT 
                    h.*,
                    (h.total_beds - h.available_beds) AS occupied_beds,

                    (
                        SELECT COUNT(*)
                        FROM hospital_teams ht
                        WHERE ht.hospital_id = h.id
                    ) AS total_teams

                FROM hospitals h
                ORDER BY h.updated_at DESC";

        return $this->getdata($sql);
    }


    public function getHospitalById($id)
    {
        $sql = "SELECT *,
                    (total_beds - available_beds) AS occupied_beds
                FROM hospitals
                WHERE id = ?";

        $data = $this->getdata($sql, [$id]);

        return $data ? $data[0] : null;
    }


    public function insertHospital(
        $organization_id,
        $region,
        $city,
        $total_beds,
        $available_beds,
        $icu_beds,
        $available_icu,
        $staff_on_duty,
        $ambulances,
        $hospital_status,
        $infrastructure_status,
        $power_status,
        $water_status
    ) {

        $sql = "INSERT INTO hospitals
                (
                    organization_id,
                    region,
                    city,
                    total_beds,
                    available_beds,
                    icu_beds,
                    available_icu,
                    staff_on_duty,
                    ambulances,
                    hospital_status,
                    infrastructure_status,
                    power_status,
                    water_status
                )
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->executeSafe($sql, [
            $organization_id,
            $region,
            $city,
            $total_beds,
            $available_beds,
            $icu_beds,
            $available_icu,
            $staff_on_duty,
            $ambulances,
            $hospital_status,
            $infrastructure_status,
            $power_status,
            $water_status
        ]);
    }


    public function updateHospital(
        $id,
        $region,
        $city,
        $total_beds,
        $available_beds,
        $icu_beds,
        $available_icu,
        $staff_on_duty,
        $ambulances,
        $hospital_status,
        $infrastructure_status,
        $power_status,
        $water_status
    ) {

        $sql = "UPDATE hospitals
                SET
                    region = ?,
                    city = ?,
                    total_beds = ?,
                    available_beds = ?,
                    icu_beds = ?,
                    available_icu = ?,
                    staff_on_duty = ?,
                    ambulances = ?,
                    hospital_status = ?,
                    infrastructure_status = ?,
                    power_status = ?,
                    water_status = ?
                WHERE id = ?";

        return $this->executeSafe($sql, [
            $region,
            $city,
            $total_beds,
            $available_beds,
            $icu_beds,
            $available_icu,
            $staff_on_duty,
            $ambulances,
            $hospital_status,
            $infrastructure_status,
            $power_status,
            $water_status,
            $id
        ]);
    }


    public function deleteHospital($id)
    {
        $sql = "DELETE FROM hospitals WHERE id = ?";

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
        $sql = "SELECT SUM(available_icu) total
                FROM hospitals";

        $data = $this->getdata($sql);

        return $data[0]['total'] ?? 0;
    }


    public function hospitalsAtCapacity()
    {
        $sql = "SELECT COUNT(*) total
                FROM hospitals
                WHERE available_beds <= 10";

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
}