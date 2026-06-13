<?php
require_once("DAL.class.php");

class maps extends DAL
{

    public function getAlerts($region = null, $incident_id = null)
    {
        if ($incident_id) {
            return $this->getdata(
                "SELECT * FROM map_alerts WHERE incident_id = ? AND is_active = 1 ORDER BY created_at DESC",
                [(int)$incident_id]
            );
        }
        return $this->getdata("SELECT * FROM map_alerts WHERE is_active = 1 ORDER BY created_at DESC");
    }

    public function getAlertById($id)
    {
        return $this->getRowSafe(
            "SELECT * FROM map_alerts WHERE id = ?",
            [(int)$id]
        );
    }

    public function addAlert($title, $severity, $description, $lat, $lng, $region, $created_by, $incident_id = 0)
    {
        $title       = $this->escape($title);
        $severity    = $this->escape($severity);
        $description = $this->escape($description);
        $region      = $this->escape($region);

        return $this->executeSafe(
            "INSERT INTO map_alerts (title, severity, description, lat, lng, region, created_by, incident_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$title, $severity, $description, (float)$lat, (float)$lng, $region, (int)$created_by, (int)$incident_id]
        );
    }

    public function updateAlert($id, $title, $severity, $description, $lat, $lng, $region)
    {
        $title       = $this->escape($title);
        $severity    = $this->escape($severity);
        $description = $this->escape($description);
        $region      = $this->escape($region);

        return $this->executeSafe(
            "UPDATE map_alerts SET title=?, severity=?, description=?, lat=?, lng=?, region=? WHERE id=?",
            [$title, $severity, $description, (float)$lat, (float)$lng, $region, (int)$id]
        );
    }

    public function deleteAlert($id)
    {
        return $this->executeSafe(
            "DELETE FROM map_alerts WHERE id = ?",
            [(int)$id]
        );
    }

    public function countAlerts($region = null)
    {
        if ($region && $region !== 'all') {
            $row = $this->getRowSafe(
                "SELECT COUNT(*) AS cnt FROM map_alerts WHERE region = ?",
                [$this->escape($region)]
            );
        } else {
            $row = $this->getRowSafe("SELECT COUNT(*) AS cnt FROM map_alerts");
        }
        return $row ? (int)$row['cnt'] : 0;
    }



    public function getZones($type = null, $incident_id = null)
    {
        if ($type && $incident_id) {
            return $this->getdata(
                "SELECT * FROM map_zones WHERE type = ? AND incident_id = ? AND is_active = 1 ORDER BY created_at DESC",
                [$this->escape($type), (int)$incident_id]
            );
        }
        if ($type) {
            return $this->getdata(
                "SELECT * FROM map_zones WHERE type = ? AND is_active = 1 ORDER BY created_at DESC",
                [$this->escape($type)]
            );
        }
        return $this->getdata("SELECT * FROM map_zones WHERE is_active = 1 ORDER BY created_at DESC");
    }

    public function getWarnZones()
    {
        return $this->getZones('warning');
    }

    public function getSafeZones()
    {
        return $this->getZones('safe');
    }

    public function getDangerZones()
    {
        return $this->getZones('danger');
    }

    public function getZoneById($id)
    {
        return $this->getRowSafe(
            "SELECT * FROM map_zones WHERE id = ?",
            [(int)$id]
        );
    }

  
    public function addRadiusZone($name, $type, $center_lat, $center_lng, $radius_meters, $region, $created_by, $incident_id = 0)
    {
        $name   = $this->escape($name);
        $type   = $this->escape($type);
        $region = $this->escape($region);

        return $this->executeSafe(
            "INSERT INTO map_zones (name, type, center_lat, center_lng, radius_meters, polygon_points, region, created_by, incident_id)
         VALUES (?, ?, ?, ?, ?, NULL, ?, ?, ?)",
            [$name, $type, (float)$center_lat, (float)$center_lng, (int)$radius_meters, $region, (int)$created_by, (int)$incident_id]
        );
    }

   
    public function addPolygonZone($name, $type, $points, $region, $created_by)
    {
        $name   = $this->escape($name);
        $type   = $this->escape($type);
        $region = $this->escape($region);

        $center_lat = isset($points[0][0]) ? (float)$points[0][0] : 0;
        $center_lng = isset($points[0][1]) ? (float)$points[0][1] : 0;

        $json = json_encode($points);

        return $this->executeSafe(
            "INSERT INTO map_zones (name, type, center_lat, center_lng, radius_meters, polygon_points, region, created_by)
             VALUES (?, ?, ?, ?, 0, ?, ?, ?)",
            [$name, $type, $center_lat, $center_lng, $json, $region, (int)$created_by]
        );
    }

    public function updateZone($id, $name, $type, $center_lat, $center_lng, $radius_meters, $region)
    {
        $name   = $this->escape($name);
        $type   = $this->escape($type);
        $region = $this->escape($region);

        return $this->executeSafe(
            "UPDATE map_zones SET name=?, type=?, center_lat=?, center_lng=?, radius_meters=?, region=? WHERE id=?",
            [$name, $type, (float)$center_lat, (float)$center_lng, (int)$radius_meters, $region, (int)$id]
        );
    }

    public function deleteZone($id)
    {
        return $this->executeSafe(
            "DELETE FROM map_zones WHERE id = ?",
            [(int)$id]
        );
    }

    public function countZones($type = null)
    {
        if ($type) {
            $row = $this->getRowSafe(
                "SELECT COUNT(*) AS cnt FROM map_zones WHERE type = ?",
                [$this->escape($type)]
            );
        } else {
            $row = $this->getRowSafe("SELECT COUNT(*) AS cnt FROM map_zones");
        }
        return $row ? (int)$row['cnt'] : 0;
    }

    public function getRoads($status = null, $incident_id = null)
    {
        if ($incident_id) {
            return $this->getdata(
                "SELECT * FROM map_roads WHERE incident_id = ? AND is_active = 1 ORDER BY created_at DESC",
                [(int)$incident_id]
            );
        }
        return $this->getdata("SELECT * FROM map_roads WHERE is_active = 1 ORDER BY created_at DESC");
    }

    public function getRoadById($id)
    {
        return $this->getRowSafe(
            "SELECT * FROM map_roads WHERE id = ?",
            [(int)$id]
        );
    }

    public function addRoad($name, $status, $reason, $points, $created_by, $incident_id = 0)
    {
        $name   = $this->escape($name);
        $status = $this->escape($status);
        $reason = $this->escape($reason);
        $json   = json_encode($points);

        return $this->executeSafe(
            "INSERT INTO map_roads (name, status, reason, route_points, created_by, incident_id)
         VALUES (?, ?, ?, ?, ?, ?)",
            [$name, $status, $reason, $json, (int)$created_by, (int)$incident_id]
        );
    }

    public function getActiveIncidents()
    {
        return $this->getdata("SELECT id, incident_name, location FROM incidents WHERE status != 'Resolved' ORDER BY reported_at DESC");
    }

    public function updateRoad($id, $name, $status, $reason)
    {
        $name   = $this->escape($name);
        $status = $this->escape($status);
        $reason = $this->escape($reason);

        return $this->executeSafe(
            "UPDATE map_roads SET name=?, status=?, reason=? WHERE id=?",
            [$name, $status, $reason, (int)$id]
        );
    }

    public function updateRoadPoints($id, $points)
    {
        $json = json_encode($points);
        return $this->executeSafe(
            "UPDATE map_roads SET route_points=? WHERE id=?",
            [$json, (int)$id]
        );
    }

    public function deleteRoad($id)
    {
        return $this->executeSafe(
            "DELETE FROM map_roads WHERE id = ?",
            [(int)$id]
        );
    }

    public function countRoads($status = null)
    {
        if ($status) {
            $row = $this->getRowSafe(
                "SELECT COUNT(*) AS cnt FROM map_roads WHERE status = ?",
                [$this->escape($status)]
            );
        } else {
            $row = $this->getRowSafe("SELECT COUNT(*) AS cnt FROM map_roads");
        }
        return $row ? (int)$row['cnt'] : 0;
    }


    public function getRoutes()
    {
        return $this->getdata("SELECT * FROM map_routes ORDER BY created_at DESC");
    }

    public function getRouteById($id)
    {
        return $this->getRowSafe(
            "SELECT * FROM map_routes WHERE id = ?",
            [(int)$id]
        );
    }

    public function addRoute($from_name, $to_name, $from_lat, $from_lng, $to_lat, $to_lng, $avoid_mode, $created_by)
    {
        $from_name  = $this->escape($from_name);
        $to_name    = $this->escape($to_name);
        $avoid_mode = $this->escape($avoid_mode);

        return $this->executeSafe(
            "INSERT INTO map_routes (from_name, to_name, from_lat, from_lng, to_lat, to_lng, avoid_mode, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$from_name, $to_name, (float)$from_lat, (float)$from_lng, (float)$to_lat, (float)$to_lng, $avoid_mode, (int)$created_by]
        );
    }

    public function deleteRoute($id)
    {
        return $this->executeSafe(
            "DELETE FROM map_routes WHERE id = ?",
            [(int)$id]
        );
    }

    public function getAllMapData($incident_id = null)
    {
        $alerts = $this->getAlerts(null, $incident_id);
        $warns  = $this->getZones('warning', $incident_id);
        $safes  = $this->getZones('safe', $incident_id);
        $roads  = $this->getRoads(null, $incident_id);

        foreach ($roads as &$r) {
            $r['route_points'] = json_decode($r['route_points'], true);
        }
        foreach ($warns as &$z) {
            if ($z['polygon_points']) $z['polygon_points'] = json_decode($z['polygon_points'], true);
        }
        foreach ($safes as &$z) {
            if ($z['polygon_points']) $z['polygon_points'] = json_decode($z['polygon_points'], true);
        }

        return [
            'alerts'    => $alerts,
            'warnZones' => $warns,
            'safeZones' => $safes,
            'roads'     => $roads,
        ];
    }

    public function getAllMapDataArchive($incident_id)
    {
        $alerts = $this->getdata(
            "SELECT * FROM map_alerts WHERE incident_id = ? ORDER BY created_at DESC",
            [(int)$incident_id]
        );
        $warns = $this->getdata(
            "SELECT * FROM map_zones WHERE type IN ('warning','danger') AND incident_id = ? ORDER BY created_at DESC",
            [(int)$incident_id]
        );
        $safes = $this->getdata(
            "SELECT * FROM map_zones WHERE type = 'safe' AND incident_id = ? ORDER BY created_at DESC",
            [(int)$incident_id]
        );
        $roads = $this->getdata(
            "SELECT * FROM map_roads WHERE incident_id = ? ORDER BY created_at DESC",
            [(int)$incident_id]
        );

        foreach ($roads as &$r) {
            $r['route_points'] = json_decode($r['route_points'], true);
        }
        foreach ($warns as &$z) {
            if ($z['polygon_points']) $z['polygon_points'] = json_decode($z['polygon_points'], true);
        }
        foreach ($safes as &$z) {
            if ($z['polygon_points']) $z['polygon_points'] = json_decode($z['polygon_points'], true);
        }

        return ['alerts' => $alerts, 'warnZones' => $warns, 'safeZones' => $safes, 'roads' => $roads];
    }
  
    public function getMapCounts()
    {
        return [
            'alerts' => $this->countAlerts(),
            'warn'   => $this->countZones('warning'),
            'safe'   => $this->countZones('safe'),
            'roads'  => $this->countRoads(),
        ];
    }

    public function getPoliceRoadsByIncident($incident_id)
{
    $rows = $this->getdata(
        "SELECT road_id AS id, road_name AS name, road_type AS status, 
                reason, route_points, region
         FROM police_roads 
         WHERE incident_id = ? AND is_active = 1
         ORDER BY created_at DESC",
        [(int)$incident_id]
    );
    foreach ($rows as &$r) {
        $r['route_points'] = json_decode($r['route_points'], true);
    }
    return $rows;
}

public function getEvacRoutesByIncident($incident_id)
{
    $rows = $this->getdata(
        "SELECT id, from_name, to_name, route_status, notes, region, route_points
         FROM map_routes 
         WHERE incident_id = ? AND route_points IS NOT NULL
         ORDER BY created_at DESC",
        [(int)$incident_id]
    );
    foreach ($rows as &$r) {
        $r['route_points'] = json_decode($r['route_points'], true);
    }
    return $rows;
}
}
