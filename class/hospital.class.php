<?php

require_once("DAL.class.php");

class hospital_dashboard extends DAL
{
    public function getHospitalData($hospital_id)
    {
        $sql = "SELECT
                    h.*,
                    o.name,
                    o.location,
                    o.email
                FROM hospitals h
                INNER JOIN organizations o
                ON h.organization_id = o.id
                WHERE h.id = ?";

        $data = $this->getdata($sql, [$hospital_id]);

        return $data ? $data[0] : null;
    }

    public function getTodayStats($hospital_id)
    {
        $sql = "SELECT *
                FROM hospital_daily_stats
                WHERE hospital_id = ?";

        $data = $this->getdata($sql, [$hospital_id]);

        return $data ? $data[0] : null;
    }


    public function saveTodayStats(
        $hospital_id,
        $total_patients,
        $critical_cases,
        $discharged,
        $transferred_out,
        $available_beds,
        $occupied_beds,
        $icu_available,
        $icu_occupied,
        $staff_on_duty,
        $ambulances
    ) {

        $check = $this->getTodayStats($hospital_id);

        // UPDATE
        if ($check) {

            $sql = "UPDATE hospital_daily_stats
                    SET
                        total_patients = ?,
                        critical_cases = ?,
                        discharged = ?,
                        transferred_out = ?,
                        available_beds = ?,
                        occupied_beds = ?,
                        icu_available = ?,
                        icu_occupied = ?,
                        staff_on_duty = ?,
                        ambulances = ?
                    WHERE hospital_id = ?
                    AND stat_date = CURDATE()";

            return $this->executeSafe($sql, [
                $total_patients,
                $critical_cases,
                $discharged,
                $transferred_out,
                $available_beds,
                $occupied_beds,
                $icu_available,
                $icu_occupied,
                $staff_on_duty,
                $ambulances,
                $hospital_id
            ]);
        }

        $sql = "INSERT INTO hospital_daily_stats
                (
                    hospital_id,
                    stat_date,
                    total_patients,
                    critical_cases,
                    discharged,
                    transferred_out,
                    available_beds,
                    occupied_beds,
                    icu_available,
                    icu_occupied,
                    staff_on_duty,
                    ambulances
                )
                VALUES
                (?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->executeSafe($sql, [
            $hospital_id,
            $total_patients,
            $critical_cases,
            $discharged,
            $transferred_out,
            $available_beds,
            $occupied_beds,
            $icu_available,
            $icu_occupied,
            $staff_on_duty,
            $ambulances
        ]);
    }

    public function getTeams($hospital_id)
    {
        $sql = "SELECT *
                FROM hospital_teams
                WHERE hospital_id = ?
                ORDER BY id DESC";

        return $this->getdata($sql, [$hospital_id]);
    }


    public function getTeam($id)
    {
        $sql = "SELECT *
                FROM hospital_teams
                WHERE id = ?";

        $data = $this->getdata($sql, [$id]);

        return $data ? $data[0] : null;
    }


    public function addTeam(
        $hospital_id,
        $team_name,
        $members_count,
        $status,
        $current_location
    ) {

        $sql = "INSERT INTO hospital_teams
                (
                    hospital_id,
                    team_name,
                    members_count,
                    status,
                    current_location
                )
                VALUES
                (?, ?, ?, ?, ?)";

        return $this->executeSafe($sql, [
            $hospital_id,
            $team_name,
            $members_count,
            $status,
            $current_location
        ]);
    }


    public function updateTeam(
        $id,
        $team_name,
        $status,
        $current_location
    ) {

        $sql = "UPDATE hospital_teams
                SET
                    team_name = ?,
                 
                    status = ?,
                    current_location = ?
                WHERE id = ?";

        return $this->executeSafe($sql, [
            $team_name,

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


    public function getTeamMembers($team_id)
    {
        $sql = "SELECT *
                FROM hospital_team_members
                WHERE team_id = ?";

        return $this->getdata($sql, [$team_id]);
    }


    public function addTeamMember(
        $team_id,
        $member_name,
        $role
    ) {

        $sql = "INSERT INTO hospital_team_members
                (
                    team_id,
                    member_name,
                    role
                )
                VALUES
                (?, ?, ?)";

        return $this->executeSafe($sql, [
            $team_id,
            $member_name,
            $role
        ]);
    }

    public function getTransfers($hospital_id)
    {
        $sql = "SELECT
                ht.*,
                o.name AS destination_name
            FROM hospital_transfers ht
            INNER JOIN organizations o
            ON ht.destination_organization_id = o.id
            WHERE ht.hospital_id = ?
            ORDER BY ht.request_time DESC";

        return $this->getdata($sql, [$hospital_id]);
    }


    public function addTransfer(
        $hospital_id,
        $destination_organization_id,
        $patients_count
    ) {
        $sql = "INSERT INTO hospital_transfers
                (
                    hospital_id,
                    destination_organization_id,
                    request_time,
                    patients_count
                )
                VALUES
                (?, ?, NOW(), ?)";
        return $this->executeSafe($sql, [
            $hospital_id,
            $destination_organization_id,
            $patients_count,
        ]);
    }


    public function updateHospitalStatus(
        $hospital_id,
        $hospital_status,
        $infrastructure_status,
        $power_status,
        $water_status
    ) {

        $sql = "UPDATE hospitals
                SET
                    hospital_status = ?,
                    infrastructure_status = ?,
                    power_status = ?,
                    water_status = ?
                WHERE id = ?";

        return $this->executeSafe($sql, [
            $hospital_status,
            $infrastructure_status,
            $power_status,
            $water_status,
            $hospital_id
        ]);
    }

    public function getHospitalByOrganization($organization_id)
    {
        $sql = "SELECT *
            FROM hospitals
            WHERE organization_id = ?";

        $data = $this->getdata($sql, [$organization_id]);

        return $data ? $data[0] : null;
    }
    public function getAllHospitals()
    {
        $sql = "SELECT id, name
            FROM organizations
            WHERE type = 'hospital'";

        return $this->getdata($sql);
    }
    public function updateTeamMember(
        $id,
        $member_name,
        $role
    ) {

        $sql = "UPDATE hospital_team_members
            SET
                member_name = ?,
                role = ?
            WHERE id = ?";

        return $this->executeSafe($sql, [
            $member_name,
            $role,
            $id
        ]);
    }

    public function deleteTeamMember($id)
    {
        $sql = "DELETE FROM hospital_team_members
            WHERE id = ?";

        return $this->executeSafe($sql, [$id]);
    }
    public function deletetransfer($id)
    {
        $sql = "DELETE FROM hospital_transfers
            WHERE id = ?";

        return $this->executeSafe($sql, [$id]);
    }

    public function memcount($team_id)
    {
        $sql = "SELECT COUNT(*) AS total
            FROM hospital_team_members
            WHERE team_id = ?";

        $result = $this->getdata($sql, [$team_id]);

        return $result[0];
    }
    public function updateTransfer(
        $id,
        $destination_organization_id,
        $patients_count,
        $status
    ) {

        $sql = "UPDATE hospital_transfers
            SET
                destination_organization_id = ?,
                patients_count = ?,
                status = ?
            WHERE id = ?";

        return $this->executeSafe($sql, [
            $destination_organization_id,
            $patients_count,
            $status,
            $id
        ]);
    }
    public function getDemographics($hospital_id)
    {
        $sql = "SELECT *
            FROM hospital_demographics
            WHERE hospital_id = ?";

        return $this->getRowSafe($sql, [$hospital_id]);
    }

    public function updateDemographics(
        $hospital_id,
        $male_injured,
        $male_martyrs,
        $female_injured,
        $female_martyrs,
        $children_injured,
        $children_martyrs
    ) {

        $sql = "UPDATE hospital_demographics
            SET
                male_injured = ?,
                male_martyrs = ?,
                female_injured = ?,
                female_martyrs = ?,
                children_injured = ?,
                children_martyrs = ?
            WHERE hospital_id = ?";

        return $this->executeSafe($sql, [
            (int)$male_injured,
            (int)$male_martyrs,
            (int)$female_injured,
            (int)$female_martyrs,
            (int)$children_injured,
            (int)$children_martyrs,
            (int)$hospital_id
        ]);
    }
}
