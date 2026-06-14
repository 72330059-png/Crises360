<?php
require_once("DAL.class.php");

class users extends DAL
{
    public function getAdminById($id)
    {
        $sql = "SELECT id, name, email FROM users WHERE id = ?";
        $data = $this->getdata($sql, [$id]);

        return $data ? $data[0] : null;
    }

    public function updateAdmin($id, $name, $email, $password = null)
    {
        if ($password === null) {

            $sql = "UPDATE users
                SET name = ?, email = ?
                WHERE id = ?";

            return $this->executeSafe($sql, [
                $name,
                $email,
                (int)$id
            ]);
        } else {

            $sql = "UPDATE users
                SET name = ?, email = ?, password = ?
                WHERE id = ?";

            return $this->executeSafe($sql, [
                $name,
                $email,
                $password,
                (int)$id
            ]);
        }
    }


    public function getallusers()
    {
        $sql = "SELECT * FROM `users` ";
        return $this->getdata($sql);
    }

    public function checkDuplicateuserinadd($name, $email, $role)
{
    $sql = "SELECT * FROM users
            WHERE name = ?
            AND email = ?
            AND role = ?";

    return $this->getdata($sql, [$name, $email, $role]);
}
    public function checkDuplicateuser($name, $email, $role, $id)
    {
        $sql = "SELECT * FROM users
            WHERE name = ?
            AND email = ?
            AND role = ?
            AND id != ?";

        return $this->getdata($sql, [$name, $email, $role, $id]);
    }

    public function insertuser($name, $email, $password, $role)
    {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users(name,email,password,role)
            VALUES(?,?,?,?)";

        return $this->executeSafe($sql, [
            $name,
            $email,
            $hashed,
            $role
        ]);
    }

    public function deleteuser($userId)
    {
        $sql = "DELETE FROM users WHERE id = ?";

        return $this->executeSafe($sql, [$userId]);
    }

    public function updateuser($id, $name, $email, $password, $role)
    {
        if (empty($password)) {

            $sql = "UPDATE users
                SET name=?, email=?, role=?
                WHERE id=?";

            return $this->executeSafe($sql, [
                $name,
                $email,
                $role,
                $id
            ]);
        } else {

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $sql = "UPDATE users
                SET name=?, email=?, password=?, role=?
                WHERE id=?";

            return $this->executeSafe($sql, [
                $name,
                $email,
                $hashed,
                $role,
                $id
            ]);
        }
    }

    public function getEnumValues($table, $field)
    {
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field);

        $sql = "SHOW COLUMNS FROM `$table` LIKE '$field'";

        $data = $this->getdata($sql);

        if ($data) {

            preg_match("/^enum\((.*)\)$/", $data[0]['Type'], $matches);

            return str_getcsv($matches[1], ',', "'", "\\");
        }

        return [];
    }

    public function getTeamActivity() {
    $sql = "SELECT * FROM users 
            ORDER BY FIELD(ustatus,'online','offline') 
            LIMIT 3";
    return $this->getdata($sql);
}


}
