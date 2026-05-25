<?php
require_once("DAL.class.php");


class Police extends DAL
{
    public function getDashboardCounts($organization_id, $region)
    {
        $org = (int)$organization_id;

        $unitRow = $this->getRowSafe(
            "SELECT pu.incident_id, pu.current_mission_id,
            i.incident_name, i.status AS incident_status,
            pm.title AS mission_title, pm.priority AS mission_priority FROM police_units pu
            LEFT JOIN incidents i  ON pu.incident_id = i.id
            LEFT JOIN police_missions pm ON pu.current_mission_id = pm.mission_id
            WHERE pu.organization_id = ? LIMIT 1",
            [$org]
        );

        $incidentId      = $unitRow ? (int)($unitRow['incident_id'] ?? 0) : 0;
        $incident_status = $unitRow['incident_status'] ?? null;
        $isResolved      = $incidentId && $incident_status === 'Resolved';

        $alerts = $incidentId ? $this->getRowSafe(
            "SELECT COUNT(*) AS cnt FROM map_alerts WHERE incident_id = ? AND is_active = 1",
            [$incidentId]
        ) : null;

        $zones = $incidentId ? $this->getRowSafe(
            "SELECT COUNT(*) AS cnt FROM map_zones WHERE incident_id = ? AND is_active = 1",
            [$incidentId]
        ) : null;
        $blocked = $this->getRowSafe(
            "SELECT COUNT(*) AS cnt FROM police_roads
          WHERE organization_id = ? AND road_type = 'blocked' AND is_active = 1",
            [$org]
        );

        $warning = $this->getRowSafe(
            "SELECT COUNT(*) AS cnt FROM police_roads
          WHERE organization_id = ? AND road_type = 'warning' AND is_active = 1",
            [$org]
        );

        $safe = $this->getRowSafe(
            "SELECT COUNT(*) AS cnt FROM police_roads
          WHERE organization_id = ? AND road_type = 'safe' AND is_active = 1",
            [$org]
        );

        $allroads = $this->getRowSafe(
            "SELECT COUNT(*) AS cnt FROM police_roads
          WHERE organization_id = ?  AND is_active = 1",
            [$org]
        );


        $routes = $this->getRowSafe(
            "SELECT COUNT(*) AS cnt FROM map_routes
          WHERE organization_id = ? AND route_points IS NOT NULL",
            [$org]
        );

        $incident = $this->getRowSafe(
            "SELECT i.incident_name, i.severity, i.location
           FROM police_units pu
           JOIN incidents i ON pu.incident_id = i.id
          WHERE pu.organization_id = ?
          LIMIT 1",
            [$org]
        );
        $mission = $this->getRowSafe(
            "SELECT pm.title, pm.priority, pm.status
       FROM police_units pu
       JOIN police_missions pm ON pu.current_mission_id = pm.mission_id
      WHERE pu.organization_id = ? AND pm.status = 'active'
      LIMIT 1",
            [$org]
        );
        $roadsmap = $incidentId ? $this->getRowSafe(
            "SELECT COUNT(*) AS cnt FROM map_roads WHERE incident_id = ? AND is_active = 1",
            [$incidentId]
        ) : null;
        return [
            'blocked_roads'    => $blocked  ? (int)$blocked['cnt']  : 0,
            'warning_roads'    => $warning  ? (int)$warning['cnt']  : 0,
            'safe_roads'       => $safe     ? (int)$safe['cnt']     : 0,
            'evac_routes'      => $routes   ? (int)$routes['cnt']   : 0,
            'incident_name'    => $incident ? $incident['incident_name'] : null,
            'incident_severity' => $incident ? $incident['severity']      : null,
            'mission_title'    => $mission  ? $mission['title']          : null,
            'mission_priority' => $mission  ? $mission['priority']       : null,
            'mission_status'   => $mission  ? $mission['status']         : null,
            'all_roads'        => $allroads ? $allroads['cnt'] : 0,
            'unit_roads_count' => $roadsmap ? (int)$roadsmap['cnt'] : 0,
            'incident_id'      => $incidentId,
            'danger_alerts'     => $alerts   ? (int)$alerts['cnt']   : 0,  // ← ADD
            'safe_zones'        => $zones    ? (int)$zones['cnt']    : 0,
            'is_resolved'       => $isResolved,                             // ← ADD
        ];
    }

    public function getAllPoliceMapData($organization_id, $region)
    {
        $org = (int)$organization_id;
        $unit = $this->getRowSafe(
            "SELECT incident_id FROM police_units WHERE organization_id = ? LIMIT 1",
            [$org]
        );
        $incidentId = $unit ? (int)$unit['incident_id'] : 0;
        $cityToRegion = [
            'sour' => 'south',
            'tyre' => 'south',
            'sur' => 'south',
            'saida' => 'south',
            'sidon' => 'south',
            'nabatieh' => 'south',
            'bint jbeil' => 'south',
            'marjayoun' => 'south',
            'hasbaya' => 'south',
            'beirut' => 'beirut',
            'tripoli' => 'north',
            'trablous' => 'north',
            'akkar' => 'north',
            'zgharta' => 'north',
            'jounieh' => 'mount',
            'baabda' => 'mount',
            'aley' => 'mount',
            'jbeil' => 'mount',
            'zahle' => 'bekaa',
            'baalbek' => 'bekaa',
            'chtaura' => 'bekaa',
        ];
        $key = strtolower(trim($region));
        $regionCode = $cityToRegion[$key] ?? $key;
        $reg = $this->escape($regionCode);

        $unit_alerts = $this->getdata(
            "SELECT id, title, severity, description, lat, lng, region
               FROM map_alerts WHERE region = ? AND is_active = 1",
            [$reg]
        );

        $unit_warn_zones = $this->getdata(
            "SELECT id, name, type, center_lat, center_lng, radius_meters, region
               FROM map_zones
              WHERE type IN ('warning','danger') AND region = ? AND is_active = 1",
            [$reg]
        );

        $unit_safe_zones = $this->getdata(
            "SELECT id, name, type, center_lat, center_lng, radius_meters, region
               FROM map_zones
              WHERE type = 'safe' AND region = ? AND is_active = 1",
            [$reg]
        );

        $unit_roads = $incidentId ? $this->getdata(
            "SELECT id, name, status, reason, route_points
       FROM map_roads WHERE incident_id = ? AND is_active = 1",
            [$incidentId]
        ) : [];
        foreach ($unit_roads as &$r) {
            $r['route_points'] = json_decode($r['route_points'], true);
        }

        $police_roads = $this->getdata(
            "SELECT road_id AS id, road_name AS name, road_type AS status,
                    reason, route_points, region
               FROM police_roads
              WHERE organization_id = ? AND is_active = 1
              ORDER BY created_at DESC",
            [$org]
        );
        foreach ($police_roads as &$r) {
            $r['route_points'] = json_decode($r['route_points'], true);
        }

        $evac_routes = $this->getdata(
            "SELECT id, from_name, to_name, route_status, notes, region, route_points
               FROM map_routes
              WHERE organization_id = ? AND route_points IS NOT NULL
              ORDER BY created_at DESC",
            [$org]
        );
        foreach ($evac_routes as &$r) {
            $r['route_points'] = json_decode($r['route_points'], true);
        }

        return [
            'unit_alerts'     => $unit_alerts,
            'unit_warn_zones' => $unit_warn_zones,
            'unit_safe_zones' => $unit_safe_zones,
            'unit_roads'      => $unit_roads,
            'police_roads'    => $police_roads,
            'evac_routes'     => $evac_routes,
        ];
    }

    public function getPoliceRoads($organization_id)
    {
        $rows = $this->getdata(
            "SELECT * FROM police_roads
              WHERE organization_id = ? AND is_active = 1
              ORDER BY created_at DESC",
            [(int)$organization_id]
        );
        foreach ($rows as &$r) {
            $r['route_points'] = json_decode($r['route_points'], true);
        }
        return $rows;
    }

    public function getPoliceRoadById($road_id)
    {
        $row = $this->getRowSafe(
            "SELECT * FROM police_roads WHERE road_id = ?",
            [(int)$road_id]
        );
        if ($row) $row['route_points'] = json_decode($row['route_points'], true);
        return $row;
    }

    public function addPoliceRoad($organization_id, $road_name, $road_type, $points, $reason, $region)
    {
        // Auto-fetch incident_id from police_units
        $unit = $this->getRowSafe(
            "SELECT incident_id FROM police_units 
          WHERE organization_id = ? AND incident_id IS NOT NULL 
          LIMIT 1",
            [(int)$organization_id]
        );
        $incident_id = $unit ? (int)$unit['incident_id'] : 0;

        return $this->executeSafe(
            "INSERT INTO police_roads
           (organization_id, road_name, road_type, route_points, reason, region, incident_id)
         VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                (int)$organization_id,
                $this->escape($road_name),
                $this->escape($road_type),
                json_encode($points),
                $this->escape($reason),
                $this->escape($region),
                $incident_id
            ]
        );
    }

    public function updatePoliceRoad($road_id, $road_name, $road_type, $reason)
    {
        return $this->executeSafe(
            "UPDATE police_roads
                SET road_name=?, road_type=?, reason=? 
              WHERE road_id=?",
            [
                $this->escape($road_name),
                $this->escape($road_type),
                $this->escape($reason),
                (int)$road_id
            ]
        );
    }

    public function deletePoliceRoad($road_id)
    {
        return $this->executeSafe(
            "DELETE FROM police_roads WHERE road_id = ?",
            [(int)$road_id]
        );
    }

    public function countPoliceRoads($organization_id, $road_type = null)
    {
        if ($road_type) {
            $row = $this->getRowSafe(
                "SELECT COUNT(*) AS cnt FROM police_roads
                  WHERE organization_id = ? AND road_type = ? AND is_active = 1",
                [(int)$organization_id, $this->escape($road_type)]
            );
        } else {
            $row = $this->getRowSafe(
                "SELECT COUNT(*) AS cnt FROM police_roads
                  WHERE organization_id = ? AND is_active = 1",
                [(int)$organization_id]
            );
        }
        return $row ? (int)$row['cnt'] : 0;
    }



    public function getEvacRoutes($organization_id)
    {
        $rows = $this->getdata(
            "SELECT * FROM map_routes
              WHERE organization_id = ? AND route_points IS NOT NULL
              ORDER BY created_at DESC",
            [(int)$organization_id]
        );
        foreach ($rows as &$r) {
            $r['route_points'] = json_decode($r['route_points'], true);
        }
        return $rows;
    }

    public function getEvacRouteById($id)
    {
        $row = $this->getRowSafe(
            "SELECT * FROM map_routes WHERE id = ?",
            [(int)$id]
        );
        if ($row) $row['route_points'] = json_decode($row['route_points'], true);
        return $row;
    }
    public function addEvacRoute($organization_id, $from_name, $to_name, $route_status, $notes, $region, $points)
    {
        // Auto-fetch incident_id from police_units
        $unit = $this->getRowSafe(
            "SELECT incident_id FROM police_units 
          WHERE organization_id = ? AND incident_id IS NOT NULL 
          LIMIT 1",
            [(int)$organization_id]
        );
        $incident_id = $unit ? (int)$unit['incident_id'] : 0;

        return $this->executeSafe(
            "INSERT INTO map_routes
           (from_name, to_name, organization_id, region, route_status, notes, route_points, incident_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $this->escape($from_name),
                $this->escape($to_name),
                (int)$organization_id,
                $this->escape($region),
                $this->escape($route_status),
                $this->escape($notes),
                json_encode($points),
                $incident_id
            ]
        );
    }

    public function updateEvacRoute($id, $route_status, $notes)
    {
        $route_status = $this->escape($route_status);
        $notes        = $this->escape($notes);

        return $this->executeSafe(
            "UPDATE map_routes SET route_status=?, notes=? WHERE id=?",
            [$route_status, $notes, $id]
        );
    }

    public function deleteEvacRoute($id)
    {
        return $this->executeSafe(
            "DELETE FROM map_routes WHERE id = ?",
            [(int)$id]
        );
    }

    public function countEvacRoutes($organization_id)
    {
        $row = $this->getRowSafe(
            "SELECT COUNT(*) AS cnt FROM map_routes
              WHERE organization_id = ? AND route_points IS NOT NULL",
            [(int)$organization_id]
        );
        return $row ? (int)$row['cnt'] : 0;
    }



    public function getMissions($status = null)
    {
        if ($status) {
            return $this->getdata(
                "SELECT * FROM police_missions
                  WHERE status = ? ORDER BY created_at DESC",
                [$this->escape($status)]
            );
        }
        return $this->getdata("SELECT * FROM police_missions ORDER BY created_at DESC");
    }

    public function getMissionById($mission_id)
    {
        return $this->getRowSafe(
            "SELECT * FROM police_missions WHERE mission_id = ?",
            [(int)$mission_id]
        );
    }

    public function addMission($priority, $title, $description)
    {
        return $this->executeSafe(
            "INSERT INTO police_missions (priority, title, description, status)
             VALUES (?, ?, ?, 'active')",
            [
                $this->escape($priority),
                $this->escape($title),
                $this->escape($description)
            ]
        );
    }

    public function updateMission($mission_id, $priority, $title, $description, $status)
    {
        return $this->executeSafe(
            "UPDATE police_missions
                SET priority=?, title=?, description=?, status=?
              WHERE mission_id=?",
            [
                $this->escape($priority),
                $this->escape($title),
                $this->escape($description),
                $this->escape($status),
                (int)$mission_id
            ]
        );
    }

    public function updateMissionStatus($mission_id, $status)
    {
        return $this->executeSafe(
            "UPDATE police_missions SET status = ? WHERE mission_id = ?",
            [$this->escape($status), (int)$mission_id]
        );
    }

    public function deleteMission($mission_id)
    {
        return $this->executeSafe(
            "DELETE FROM police_missions WHERE mission_id = ?",
            [(int)$mission_id]
        );
    }

    public function countMissions($status = null)
    {
        if ($status) {
            $row = $this->getRowSafe(
                "SELECT COUNT(*) AS cnt FROM police_missions WHERE status = ?",
                [$this->escape($status)]
            );
        } else {
            $row = $this->getRowSafe("SELECT COUNT(*) AS cnt FROM police_missions");
        }
        return $row ? (int)$row['cnt'] : 0;
    }


    public function getUnits($organization_id)
    {
        return $this->getdata(
            "SELECT pu.*, o.name AS org_name
               FROM police_units pu
               JOIN organizations o ON pu.organization_id = o.id
              WHERE pu.organization_id = ?
              ORDER BY pu.last_update DESC",
            [(int)$organization_id]
        );
    }

    public function getUnitById($unit_id)
    {
        return $this->getRowSafe(
            "SELECT * FROM police_units WHERE unit_id = ?",
            [(int)$unit_id]
        );
    }

    public function updateUnitStatus($unit_id, $status)
    {
        return $this->executeSafe(
            "UPDATE police_units SET status = ? WHERE unit_id = ?",
            [$this->escape($status), (int)$unit_id]
        );
    }

    public function countDeployedUnits($organization_id)
    {
        $row = $this->getRowSafe(
            "SELECT COUNT(*) AS cnt FROM police_units
              WHERE organization_id = ? AND status = 'deployed'",
            [(int)$organization_id]
        );
        return $row ? (int)$row['cnt'] : 0;
    }


    public function getSafeZones()
    {
        $row = $this->getRowSafe(
            "SELECT COUNT(*) AS total FROM map_zones WHERE type = 'safe' AND is_active = 1"
        );
        return $row ? (int)$row['total'] : 0;
    }

    public function getBlockedRoads()
    {
        $row = $this->getRowSafe(
            "SELECT COUNT(*) AS total FROM police_roads WHERE road_type = 'blocked' AND is_active = 1"
        );
        return $row ? (int)$row['total'] : 0;
    }
    public function getSentMissions($organization_id)
    {
        return $this->getdata(
            "SELECT pm.mission_id, pm.title, pm.description, pm.priority,
                i.incident_name
         FROM police_units pu
         JOIN police_missions pm ON pu.current_mission_id = pm.mission_id
         LEFT JOIN incidents i ON pu.incident_id = i.id
         WHERE pu.organization_id = ? AND pm.status = 'sent'",
            [(int)$organization_id]
        );
    }
}
