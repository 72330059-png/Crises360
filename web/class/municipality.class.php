<?php

require_once("DAL.class.php");

class municipality extends DAL
{

    public function getAllShelters()
    {
        $sql = "SELECT * FROM shelters ORDER BY created_at DESC";

        return $this->getdata($sql);
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
                ORDER BY created_at DESC";

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
                WHERE status != 'closed'";

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

    public function totalAidShipments()
    {
        $sql = "SELECT COUNT(*) total
                FROM donations
                WHERE donation_type = 'food'
                   OR donation_type = 'medical'
                   OR donation_type = 'fuel'";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }

        public function totalDisplacedPeople()
    {
        $sql = "SELECT COUNT(*) total FROM displaced_people";

        $data = $this->getdata($sql);

        return $data[0]['total'];
    }
}
