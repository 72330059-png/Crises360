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
        $status,
        $current_location
    ) {

        $sql = "INSERT INTO hospital_teams
                (
                    hospital_id,
                    team_name,
                    status,
                    current_location
                )
                VALUES
                (?, ?, ?, ?)";

        return $this->executeSafe($sql, [
            $hospital_id,
            $team_name,
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


    public function addTransfer($hospital_id, $destination_organization_id, $patients_count)
    {
        $sql = "INSERT INTO hospital_transfers
                (hospital_id, destination_organization_id, request_time, patients_count, status)
            VALUES (?, ?, NOW(), ?, 'pending')";
        return $this->executeSafe($sql, [$hospital_id, $destination_organization_id, $patients_count]);
    }

    public function updateTransferStatus($transfer_id, $status)
    {
        $sql = "UPDATE hospital_transfers SET status = ? WHERE id = ?";
        return $this->executeSafe($sql, [$status, $transfer_id]);
    }


    public function getTransferById($transfer_id)
    {
        $sql = "SELECT * FROM hospital_transfers WHERE id = ?";
        return $this->getRowSafe($sql, [$transfer_id]);
    }


    public function getOrgIdByHospitalId($hospital_id)
    {
        $sql = "SELECT organization_id FROM hospitals WHERE id = ?";
        $row = $this->getRowSafe($sql, [$hospital_id]);
        return $row['organization_id'] ?? null;
    }


    public function getHospitalNameByHospitalId($hospital_id)
    {
        $sql = "SELECT o.name FROM hospitals h
                JOIN organizations o ON o.id = h.organization_id
                WHERE h.id = ?";
        $row = $this->getRowSafe($sql, [$hospital_id]);
        return $row['name'] ?? 'Unknown Hospital';
    }


    public function addHospitalNotification($to_org_id, $from_org_id, $transfer_id, $message, $type)
    {
        $sql = "INSERT INTO hospital_notifications
                    (to_hospital_org_id, from_hospital_org_id, transfer_id, message, type, is_read)
                VALUES (?, ?, ?, ?, ?, 0)";
        return $this->executeSafe($sql, [$to_org_id, $from_org_id, $transfer_id, $message, $type]);
    }


    public function getHospitalNotifications($org_id)
    {
        $sql = "SELECT * FROM hospital_notifications
                WHERE to_hospital_org_id = ? AND is_read = 0
                ORDER BY created_at DESC";
        return $this->getdata($sql, [$org_id]);
    }


    public function getHospitalNotifCount($org_id)
    {
        $sql = "SELECT COUNT(*) as cnt FROM hospital_notifications
                WHERE to_hospital_org_id = ? AND is_read = 0";
        $row = $this->getRowSafe($sql, [$org_id]);
        return (int)($row['cnt'] ?? 0);
    }


    public function markHospitalNotifRead($id)
    {
        $sql = "UPDATE hospital_notifications SET is_read = 1 WHERE id = ?";
        return $this->executeSafe($sql, [$id]);
    }


    public function getTransfers($hospital_id)
    {
        $sql = "SELECT ht.*,
                       o.name AS destination_name
                FROM hospital_transfers ht
                LEFT JOIN organizations o ON o.id = ht.destination_organization_id
                WHERE ht.hospital_id = ?
                ORDER BY ht.request_time DESC";
        return $this->getdata($sql, [$hospital_id]);
    }


    public function getIncomingTransfers($org_id)
    {
        $sql = "SELECT ht.*,
                       o.name AS sender_name
                FROM hospital_transfers ht
                LEFT JOIN organizations o ON o.id = (
                    SELECT organization_id FROM hospitals WHERE id = ht.hospital_id
                )
                WHERE ht.destination_organization_id = ? AND ht.status = 'pending'
                ORDER BY ht.request_time DESC";
        return $this->getdata($sql, [$org_id]);
    }



    public function updateHospitalCards(
        $hospital_id,
        $available_beds,
        $total_beds,
        $available_icu_beds,
        $icu_beds,
        $staff_on_duty,
        $ambulances
    ) {
        $sql = "UPDATE hospitals SET
                    available_beds     = ?,
                    total_beds         = ?,
                    available_icu_beds = ?,
                    icu_beds           = ?,
                    staff_on_duty      = ?,
                    ambulances         = ?
                WHERE id = ?";
        return $this->executeSafe($sql, [
            $available_beds,
            $total_beds,
            $available_icu_beds,
            $icu_beds,
            $staff_on_duty,
            $ambulances,
            $hospital_id
        ]);
    }

    public function updateDailyStatsCards($hospital_id, $total_patients, $critical_cases)
    {
        return $this->executeSafe(
            "UPDATE hospital_daily_stats
         SET total_patients = ?, critical_cases = ?
         WHERE hospital_id = ?",
            [$total_patients, $critical_cases, $hospital_id]
        );
    }
    public function updateTotalPatients($hospital_id, $total_patients)
    {
        return $this->executeSafe(
            "UPDATE hospital_daily_stats
         SET total_patients = ?
         WHERE hospital_id = ?",
            [$total_patients, $hospital_id]
        );
    }
    public function getTotalPatients($hospital_id)
    {
        $row = $this->getRowSafe(
            "SELECT total_patients
         FROM hospital_daily_stats
         WHERE hospital_id = ?",
            [$hospital_id]
        );

        return (int)($row['total_patients'] ?? 0);
    }
}
