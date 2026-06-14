<?php

require_once("DAL.class.php");

class muni extends DAL
{
    public function getNeedById($id)
    {
        $sql = "SELECT id, need_name, organization_id FROM needs WHERE id = ?";
        return $this->getRowSafe($sql, [(int)$id]);
    }

public function insertNeedNotification($org_id, $message) {
    $sql = "INSERT INTO notifications (target_org_id, message, type, is_read, created_at) 
            VALUES (?, ?, 'need_response', 0, NOW())";
    return $this->executeSafe($sql, [$org_id, $message]);
}
    public function fulfillNeed($id)
    {
        $sql = "UPDATE needs 
            SET status = 'fulfilled' 
            WHERE id = $id";

        return $this->execute($sql);
    }

    public function rejectNeed($id)
    {
        $sql = "UPDATE needs 
            SET status = 'rejected' 
            WHERE id = $id";

        return $this->execute($sql);
    }
    public function getAllShelters()
    {
        $sql = "
        SELECT shelters.*, o.name AS organization_name
        FROM shelters
        INNER JOIN organizations o
        ON shelters.organization_id = o.id
        ORDER BY shelters.created_at DESC
    ";

        return $this->getdata($sql);
    }

    public function getAllmuni()
    {
        $sql = " SELECT *
        FROM organizations
        WHERE type='municipality'
        ORDER BY name ASC";

        return $this->getdata($sql);
    }

    public function deleteShelter($id)
    {
        $sql = "DELETE FROM shelters WHERE id = ?";

        return $this->executeSafe($sql, [$id]);
    }
    public function totalShelters()
    {
        $sql = "SELECT COUNT(*) total FROM shelters";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }

    public function totalCapacity()
    {
        $sql = "SELECT SUM(capacity) total FROM shelters";

        $data = $this->getdata($sql);

        return $data[0]['total'] ?? 0;
    }

    public function totalOccupied()
    {
        $sql = "SELECT SUM(occupied) total FROM shelters";

        $data = $this->getdata($sql);

        return $data[0]['total'] ?? 0;
    }

    public function getOccupancyRate()
    {
        $capacity = $this->totalCapacity();
        $occupied = $this->totalOccupied();

        if ($capacity == 0) {
            return 0;
        }

        return round(($occupied / $capacity) * 100);
    }
    // NEEDS

    public function getAllNeeds()
    {
        $sql = "SELECT needs.*, organizations.name AS municipality_name
            FROM needs
            LEFT JOIN organizations
            ON needs.organization_id = organizations.id
            WHERE organizations.type = 'municipality'
            ORDER BY needs.created_at DESC";

        return $this->getdata($sql);
    }

    public function totalNeeds()
    {
        $sql = "SELECT COUNT(*) total FROM needs";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }

    public function activeNeeds()
    {
        $sql = "SELECT COUNT(*) total
            FROM needs
            WHERE status = 'in_progress'";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }
    public function totalMunicipalitiesWithNeeds()
    {
        $sql = "SELECT COUNT(DISTINCT organization_id) total
            FROM needs";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }
    public function highPriorityNeeds()
    {
        $sql = "SELECT COUNT(*) total
            FROM needs
            WHERE priority = 'high'";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }

    public function fulfilledNeeds()
    {
        $sql = "SELECT COUNT(*) total
                FROM needs
                WHERE status = 'fulfilled'";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }

    //donations

    public function totalDonations()
    {
        $sql = "SELECT SUM(total_amount) total FROM donations";

        $data = $this->getdata($sql);

        return $data[0]['total'] ?? 0;
    }

    public function totalAidEntries()
    {
        $sql = "SELECT COUNT(*) total
            FROM donations
            WHERE donation_type != 'money'";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }

    public function totalDisplacedPeople()
    {
        $sql = "SELECT COUNT(*) total FROM displaced_people";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }

    public function donationSummary()
    {
        $sql = "SELECT donation_type, SUM(total_amount) total
            FROM donations
            GROUP BY donation_type";

        return $this->getdata($sql);
    }
    public function topNeeds()
    {
        $sql = "SELECT need_name,
                   SUM(quantity) total_quantity
            FROM needs
            WHERE status != 'fulfilled'
            GROUP BY need_name
            ORDER BY total_quantity DESC
            LIMIT 3";

        return $this->getdata($sql);
    }
    public function availableCapacity()
    {
        $sql = "SELECT SUM(available) total FROM shelters";

        $data = $this->getdata($sql);

        return $data[0]['total'] ?? 0;
    }
    public function donationChartData()
    {
        $sql = "SELECT donation_type,
                   SUM(total_amount) total
            FROM donations
            GROUP BY donation_type";

        return $this->getdata($sql);
    }

public function insertShelter(
    $organization_id,
    $organization_name,
    $organization_location,
    $organization_email,
    $organization_password,
    $shelter_name,
    $location,
    $capacity,
    $shelter_lat = null, $shelter_lng = null,
    $org_lat = null,     $org_lng = null
) {
    if (!empty($organization_id)) {
        $final_organization_id = $organization_id;
    } else {
        $type = "municipality";
        $hashed_password = password_hash($organization_password, PASSWORD_DEFAULT);

        $sqlOrg = "INSERT INTO organizations
            (name, type, location, email, password, lat, lng)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

        $final_organization_id = $this->executeSafe($sqlOrg, [
            $organization_name, $type, $organization_location,
            $organization_email, $hashed_password,
            $org_lat, $org_lng
        ]);

        if (!$final_organization_id || is_array($final_organization_id)) return false;
    }

    $occupied = 0;
    $status = "open";

    $sqlShelter = "INSERT INTO shelters
        (organization_id, shelter_name, location, capacity, occupied, status, lat, lng)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    return $this->executeSafe($sqlShelter, [
        $final_organization_id, $shelter_name, $location,
        $capacity, $occupied, $status,
        $shelter_lat, $shelter_lng
    ]);
}

}
