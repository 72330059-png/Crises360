<?php

class DAL
{
    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $port;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/db.php';

        $this->servername = $config['servername'];
        $this->username   = $config['username'];
        $this->password   = $config['password'];
        $this->dbname     = $config['dbname'];
        $this->port = $config['port'];
    }


    // public function ConnectionDatabase()
    // {
    //     return new mysqli($this->servername, $this->username, $this->password, $this->dbname, $this->port);
    // }

    public function ConnectionDatabase()
    {
        $conn = new mysqli(
            $this->servername,
            $this->username,
            $this->password,
            $this->dbname,
            $this->port
        );

        $conn->query("SET time_zone = '+03:00'");

        return $conn;
    }

    public function getdata($sql, $params = [])
    {
        $conn = $this->ConnectionDatabase();

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception($conn->error);
        }

        if (!empty($params)) {

            $types = '';

            foreach ($params as $param) {

                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_double($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }

            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        $result = $stmt->get_result();

        $data = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
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


        if (stripos(trim($sql), 'insert') === 0) {
            $insertId = $conn->insert_id;
            $conn->close();
            return $insertId;
        }


        if (stripos(trim($sql), 'select') === 0) {
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $result->free();
            $conn->close();
            return $rows;
        }


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

        if (stripos(trim($sql), 'insert') === 0) {
            $insertId = $conn->insert_id;
            $conn->close();
            return $insertId;
        }

        $conn->close();
        return true;
    }


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

    public function movemultiplefiles($image, $i)
    {
        $target_dir = "../assets/img/";

        $extension = strtolower(pathinfo($image["name"][$i], PATHINFO_EXTENSION));
        $base = pathinfo($image["name"][$i], PATHINFO_FILENAME);

        $new_image = $base . "-" . date("Ymd_His") . "-" . uniqid() . "." . $extension;

        $target_file = $target_dir . $new_image;

        move_uploaded_file($image["tmp_name"][$i], $target_file);

        return $new_image;
    }

    public function escapelike($value)
    {
        return addcslashes(trim($value), '%_');
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

    public function validatePhone($phone)
    {
        $phone = trim($phone);

        return preg_match('/^\+?[0-9]{8,15}$/', $phone);
    }
    public function clean($value)
    {
        return trim($value);
    }
    public function e($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    public function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    public function validateInt($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT);
    }
    public function validateURL($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
    public function validateImage($file)
    {
        $allowed = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $type = finfo_file($finfo, $file['tmp_name']);

        finfo_close($finfo);

        if (!in_array($type, $allowed)) {
            return false;
        }

        return true;
    }
    public function executeSafe($sql, $params = [])
    {
        $conn = $this->ConnectionDatabase();

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            return [
                'status' => 'error',
                'message' => $conn->error
            ];
        }

        if (!empty($params)) {

            $types = '';

            foreach ($params as $param) {

                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_double($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }

            $stmt->bind_param($types, ...$params);
        }

        $result = $stmt->execute();

        if (!$result) {

            $error = $stmt->error;

            $stmt->close();
            $conn->close();

            return [
                'status' => 'error',
                'message' => $error
            ];
        }


        $insertId = $conn->insert_id;

        $stmt->close();
        $conn->close();

        return $insertId ?: true;
    }
    public function getRowSafe($sql, $params = [])
    {
        $conn = $this->ConnectionDatabase();

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception($conn->error);
        }

        if (!empty($params)) {

            $types = '';

            foreach ($params as $param) {

                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_double($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }

            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        $result = $stmt->get_result();

        $row = $result->fetch_assoc();

        $stmt->close();
        $conn->close();

        return $row ?: null;
    }
}
