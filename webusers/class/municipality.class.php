<?php

require_once("DAL.class.php");

class Municipality extends DAL
{
    public function getShelters($org_id)
    {
        $sql = "SELECT * FROM shelters
                WHERE organization_id = ?
                ORDER BY created_at DESC";

        return $this->getdata($sql, [$org_id]);
    }

    public function addShelter($data)
    {
        $status = $this->calculateStatus(
            $data['capacity'],
            $data['occupied']
        );

        $sql = "INSERT INTO shelters
            (
                organization_id,
                shelter_name,
                location,
                capacity,
                occupied,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?)";

        return $this->executeSafe($sql, [

            (int)$data['organization_id'],

            trim($data['shelter_name']),

            trim($data['location']),

            (int)$data['capacity'],

            (int)$data['occupied'],

            $status

        ]);
    }

    public function updateShelter($data)
    {
        $status = $this->calculateStatus(
            $data['capacity'],
            $data['occupied']
        );

        $sql = "UPDATE shelters SET

            shelter_name = ?,
            location = ?,
            capacity = ?,
            occupied = ?,
            status = ?

            WHERE id = ?";

        return $this->executeSafe($sql, [

            trim($data['shelter_name']),

            trim($data['location']),

            (int)$data['capacity'],

            (int)$data['occupied'],

            $status,

            (int)$data['id']

        ]);
    }

    public function deleteShelter($id)
    {
        $sql = "DELETE FROM shelters WHERE id = ?";

        return $this->executeSafe($sql, [
            (int)$id
        ]);
    }


    public function getShelterById($id)
{
    return $this->getRowSafe(
        "SELECT id, capacity, occupied, available
         FROM shelters
         WHERE id = ?",
        [$id]
    );
}

    public function calculateStatus($capacity, $occupied)
    {
        if ($occupied >= $capacity) {
            return "full";
        }

        if ($occupied >= ($capacity * 0.8)) {
            return "near_full";
        }

        return "open";
    }

    public function getNeeds($org_id)
    {
        $sql = "SELECT * FROM needs
                WHERE organization_id=?
                ORDER BY created_at DESC";

        return $this->getdata($sql, [$org_id]);
    }
    public function addNeed($data)
    {
        $sql = "INSERT INTO needs
            (
                organization_id,
                need_name,
                category,
                quantity,
                priority,
                status,
                description
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)";

        return $this->executeSafe($sql, [

            $data['organization_id'],

            $this->clean($data['need_name']),

            $this->clean($data['category']),

            (int)$data['quantity'],

            $this->clean($data['priority']),

            'in_progress',

            $this->clean($data['description'])

        ]);
    }

    public function updateNeed($data)
    {
        $sql = "UPDATE needs SET

            need_name=?,
            category=?,
            quantity=?,
            priority=?,
            status=?,
            description=?

            WHERE id=?";

        return $this->executeSafe($sql, [

            $this->clean($data['need_name']),

            $this->clean($data['category']),

            (int)$data['quantity'],

            $this->clean($data['priority']),

            $this->clean($data['status']),

            $this->clean($data['description']),

            (int)$data['id']

        ]);
    }

    public function deleteNeed($id)
    {
        $sql = "DELETE FROM needs WHERE id=?";

        return $this->executeSafe($sql, [

            (int)$id

        ]);
    }

    public function getEnumValues($table, $column)
    {
        $table = $this->clean($table);

        $column = $this->clean($column);

        $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";

        $result = $this->getRow($sql);

        preg_match("/^enum\(\'(.*)\'\)$/", $result['Type'], $matches);

        return explode("','", $matches[1]);
    }


    public function getResources($org_id)
    {
        $sql = "SELECT * FROM resources
                WHERE organization_id=?
                ORDER BY created_at DESC";

        return $this->getdata($sql, [$org_id]);
    }

  public function addResource($data) {
    $sql = "INSERT INTO resources
        (organization_id, resource_name, category, address,
         contact_number, opening_hours, status, notes, lat, lng)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    return $this->executeSafe($sql, [
        (int)$data['organization_id'],
        $this->clean($data['resource_name']),
        $this->clean($data['category']),
        $this->clean($data['address']),
        $this->clean($data['contact_number']),
        $this->clean($data['opening_hours']),
        $this->clean($data['status']),
        $this->clean($data['notes']),
        $data['lat'],
        $data['lng']
    ]);
}

    public function updateResource($data)
    {
        $sql = "UPDATE resources SET

    resource_name=?,
    category=?,
    address=?,
    contact_number=?,
    opening_hours=?,
    status=?,
    notes=?

    WHERE resource_id=?";

        return $this->executeSafe($sql, [

            $this->clean($data['resource_name']),

            $this->clean($data['category']),

            $this->clean($data['address']),

            $this->clean($data['contact_number']),

            $this->clean($data['opening_hours']),

            $this->clean($data['status']),

            $this->clean($data['notes']),

            (int)$data['resource_id']

        ]);
    }

    public function deleteResource($id)
    {
        $sql = "DELETE FROM resources
            WHERE resource_id=?";

        return $this->executeSafe($sql, [(int)$id]);
    }



    public function getDonations($org_id)
    {
        $sql = "SELECT * FROM donations
                WHERE organization_id=?
                ORDER BY created_at DESC";

        return $this->getdata($sql, [$org_id]);
    }

    public function addDonation($data)
    {
        $sql = "INSERT INTO donations
                (organization_id, total_amount, donation_type)
                VALUES (?, ?, ?)";

        return $this->executeSafe($sql, [
            $data['organization_id'],
            $data['total_amount'],
            $data['donation_type']
        ]);
    }

    public function deleteDonation($id)
    {
        $sql = "DELETE FROM donations WHERE id=?";

        return $this->executeSafe($sql, [$id]);
    }
    public function updateDonation($data)
    {
        $sql = "UPDATE donations SET

            total_amount=?,
            donation_type=?

            WHERE id=?";

        return $this->executeSafe($sql, [

            (int)$data['total_amount'],

            $this->clean($data['donation_type']),

            (int)$data['id']

        ]);
    }

    public function getDonationTypes()
    {
        $sql = "SHOW COLUMNS FROM donations LIKE 'donation_type'";

        $result = $this->getRowSafe($sql);

        preg_match("/^enum\(\'(.*)\'\)$/", $result['Type'], $matches);

        return explode("','", $matches[1]);
    }


    public function getDisplacedPeople($org_id)
    {
        $sql = "SELECT displaced_people.*, shelters.shelter_name
                FROM displaced_people
                INNER JOIN shelters
                ON displaced_people.shelter_id = shelters.id
                WHERE shelters.organization_id=?";

        return $this->getdata($sql, [$org_id]);
    }

    public function activeNeeds($org_id)
    {
        $sql = "SELECT COUNT(*) as total
            FROM needs
            WHERE organization_id=?
            AND status='in_progress'";

        $result = $this->getdata($sql, [$org_id]);

        return $result[0]['total'] ?? 0;
    }

    public function openResources($org_id)
    {
        $sql = "SELECT COUNT(*) as total
            FROM resources
            WHERE organization_id=?
            AND status='open'";

        $result = $this->getdata($sql, [$org_id]);

        return $result[0]['total'] ?? 0;
    }

    public function addDisplacedPerson($data)
    {
        $sql = "INSERT INTO displaced_people
                (shelter_id, full_name, family_members,
                phone, arrival_date)
                VALUES (?, ?, ?, ?, ?)";

        return $this->executeSafe($sql, [
            $data['shelter_id'],
            $data['full_name'],
            $data['family_members'],
            $data['phone'],
            $data['arrival_date']
        ]);
    }

    public function deleteDisplacedPerson($id)
    {
        $sql = "DELETE FROM displaced_people WHERE id=?";

        return $this->executeSafe($sql, [$id]);
    }



    public function totalShelters($org_id)
    {
        $sql = "SELECT COUNT(*) as total
                FROM shelters
                WHERE organization_id=?";

        $result = $this->getdata($sql, [$org_id]);

        return $result[0]['total'] ?? 0;
    }

    public function totalCapacity($org_id)
    {
        $sql = "SELECT SUM(capacity) as total
                FROM shelters
                WHERE organization_id=?";

        $result = $this->getdata($sql, [$org_id]);

        return $result[0]['total'] ?? 0;
    }

    public function totalOccupied($org_id)
    {
        $sql = "SELECT SUM(occupied) as total
                FROM shelters
                WHERE organization_id=?";

        $result = $this->getdata($sql, [$org_id]);

        return $result[0]['total'] ?? 0;
    }

    public function totalAvailable($org_id)
    {
        $sql = "SELECT SUM(capacity - occupied) as total
                FROM shelters
                WHERE organization_id=?";

        $result = $this->getdata($sql, [$org_id]);

        return $result[0]['total'] ?? 0;
    }


    public function getResourceCategories()
    {
        $sql = "SHOW COLUMNS FROM resources LIKE 'category'";

        $result = $this->getdata($sql);

        if (!$result || !isset($result[0]['Type'])) {
            return [];
        }

        $row = $result[0];

        preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);

        $enum = str_replace("'", "", $matches[1]);

        return explode(",", $enum);
    }

  public function getOrganizationById($org_id) {
    return $this->getRowSafe(
        "SELECT * FROM organizations WHERE id = ?",
        [$org_id]
    );
}
}
