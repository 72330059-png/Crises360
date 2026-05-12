<?php

class DAL
{
    private $servername;
    private $username;
    private $password;
    private $dbname;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/db.php';

        $this->servername = $config['servername'];
        $this->username   = $config['username'];
        $this->password   = $config['password'];
        $this->dbname     = $config['dbname'];
    }

    // ---------------------------
    // DATABASE CONNECTION
    // ---------------------------
    public function ConnectionDatabase()
    {
        return new mysqli($this->servername, $this->username, $this->password, $this->dbname);
    }

    // ---------------------------
    // SELECT (without parameters)
    // ---------------------------
    public function getdata($sql)
    {
        $conn = $this->ConnectionDatabase();
        $result = $conn->query($sql);

        if (!$result) {
            throw new Exception($conn->error);
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $conn->close();

        return $data;
    }


public function execute2($sql)
{
    mysqli_report(MYSQLI_REPORT_OFF);

    $conn = $this->ConnectionDatabase();

    if ($conn->connect_error) {
        return [
            'status' => 'error',
            'message' => 'DB connection error: ' . $conn->connect_error
        ];
    }

    $result = $conn->query($sql);

    if ($result === false) {
        $err = $conn->error;
        $conn->close();
        return [
            'status' => 'error',
            'message' => $err
        ];
    }

    // ✅ If this was an INSERT, return the new ID
    if (stripos(trim($sql), 'insert') === 0) {
        $insertId = $conn->insert_id;
        $conn->close();
        return $insertId; // return numeric ID
    }

    // ✅ If this was a SELECT, return rows
    if (stripos(trim($sql), 'select') === 0) {
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
        $conn->close();
        return $rows; // return array of rows
    }

    // ✅ For UPDATE/DELETE/other queries
    $conn->close();
    return true;
}

public function execute($sql)
{
    mysqli_report(MYSQLI_REPORT_OFF);

    $conn = $this->ConnectionDatabase();

    if ($conn->connect_error) {
        return [
            'status' => 'error',
            'message' => 'DB connection error: ' . $conn->connect_error
        ];
    }

    $result = $conn->query($sql);

    if ($result === false) {
        $err = $conn->error;
        $conn->close();
        return [
            'status' => 'error',
            'message' => $err
        ];
    }

    // ✅ If this was an INSERT, return the new ID
    if (stripos(trim($sql), 'insert') === 0) {
        $insertId = $conn->insert_id;
        $conn->close();
        return $insertId; // return numeric ID
    }

    $conn->close();
    return true; // for UPDATE/DELETE/other queries
}

    // ---------------------------
    // PREPARED STATEMENT (SAFE)
    // ---------------------------
    public function data($sql, $params = [])
    {
        $conn = $this->ConnectionDatabase();

        if (!empty($params)) {
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception($conn->error);
            }

            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();

            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);

            $stmt->close();
        } else {
            $query = $conn->query($sql);

            if (!$query) {
                throw new Exception($conn->error);
            }

            $data = $query->fetch_all(MYSQLI_ASSOC);
        }

        $conn->close();
        return $data;
    }

    // ---------------------------
    // MOVE SINGLE FILE
    // ---------------------------
    public function movefile($file)
    {
        $target_dir = "../img/";
        $ext  = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $base = pathinfo($file["name"], PATHINFO_FILENAME);

        $new = $base . "-" . date("Ymd_His") . "-" . uniqid() . "." . $ext;
        $target = $target_dir . $new;

        move_uploaded_file($file["tmp_name"], $target);
        return $new;
    }

    // ---------------------------
    // ⭐ MOVE MULTIPLE FILES  (RESTORED)
    // ---------------------------
    public function movemultiplefiles($image, $i)
    {
        $target_dir = "../assets/img/";

        $extension = strtolower(pathinfo($image["name"][$i], PATHINFO_EXTENSION));
        $base = pathinfo($image["name"][$i], PATHINFO_FILENAME);

        // unique name
        $new_image = $base . "-" . date("Ymd_His") . "-" . uniqid() . "." . $extension;

        $target_file = $target_dir . $new_image;

        move_uploaded_file($image["tmp_name"][$i], $target_file);

        return $new_image;
    }

    // ---------------------------
    // ESCAPE STRING
    // ---------------------------
    public function escapelike($value)
    {
        $conn = $this->ConnectionDatabase();
        return $conn->real_escape_string($value);
    }

    public function lastInsertedId()
{
    $conn = $this->ConnectionDatabase();
    $id = $conn->insert_id;
    $conn->close();
    return $id;
}

public function getrow(string $sql): ?array
{
    $conn = $this->ConnectionDatabase();
    $result = $conn->query($sql);

    if (!$result || $result->num_rows === 0) {
        $conn->close();
        return null;
    }

    $row = $result->fetch_assoc();
    $conn->close();

    return $row;
}



public function escape($value)
{
    if ($value === null) {
        return null;
    }

    $conn = $this->ConnectionDatabase();
    $escaped = $conn->real_escape_string(trim($value));
    $conn->close();

    return $escaped;
}



}
