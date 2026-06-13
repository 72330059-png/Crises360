<?php
require_once("DAL.class.php");

class Index extends DAL
{

    public function getAdminById($id)
    {
        $sql = "SELECT id, name, email FROM users WHERE id = $id";
        $data = $this->getData($sql);
        return $data ? $data[0] : null;
    }

    public function updateAdmin($id, $name, $email, $password = null)
    {
        $name  = addslashes($name);
        $email = addslashes($email);

        if ($password === null) {

            $sql = "UPDATE users 
                SET name='$name', email='$email'
                WHERE id='$id'";
        } else {

            $password = addslashes($password);
            $sql = "UPDATE users 
                SET name='$name', email='$email', password='$password'
                WHERE id='$id'";
        }

        return $this->execute($sql);
    }


    public function getallusers()
    {
        $sql = "SELECT * FROM `users` ";
        return $this->getdata($sql);
    }

    public function checkDuplicateuserUpdate($name, $email, $role, $id)
    {
        $sql = "SELECT * FROM users 
            WHERE name='$name' 
            AND email='$email' 
            AND role='$role'
            AND id != $id";
        return $this->getData($sql);
    }


    public function getEnumValues($table, $field)
    {
        $sql = "SHOW COLUMNS FROM $table LIKE '$field'";
        $data = $this->getData($sql);

        if ($data) {
            preg_match("/^enum\((.*)\)$/", $data[0]['Type'], $matches);
            $vals = str_getcsv($matches[1], ',', "'", "\\");
            return $vals;
        }
        return [];
    }


    public function checkDuplicateuser($name, $email, $role)
    {

        $query1 = "SELECT `id`, `name`, `email`, `password`, `role`, `created_at`
FROM `users`
WHERE `name`='$name' 
  AND `email`='$email' 
  AND `role`='$role';
";
        $result1 = $this->getdata($query1);
        return $result1;
    }

    public function insertuser($name, $email, $password, $role)
    {
        $query = "INSERT INTO `users`(`name`, `email`, `password`, `role`) VALUES ('$name','$email','$password','$role')";
        $this->execute($query);
    }

    public function deleteuser($userId)
    {
        $userId = (int)$userId;
        $sql = "DELETE FROM users WHERE id = $userId";
        return $this->execute($sql);
    }


    public function updateuser($id, $name, $email, $password, $role)
    {
        if (empty($password)) {
            $sql = "UPDATE users 
                SET name='$name', email='$email', role='$role'
                WHERE id='$id'";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users 
                SET name='$name', email='$email', password='$hashed', role='$role'
                WHERE id='$id'";
        }

        return $this->execute($sql);
    }
}
