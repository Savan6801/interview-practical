<?php

namespace api;
include __DIR__ . '/../db/conn.php';

class Admin
{
    private $conn;

    public function __construct($dbConn)
    {
        $this->conn = $dbConn;
    }

    public function getAdminDetails()
    {
        $query = "SELECT * FROM `admin`";
        if (mysqli_query($this->conn, $query)) {
            return ["error" => mysqli_error($this->conn)];
        }
        $admins = [];
        while ($row = mysqli_fetch_assoc(mysqli_query($this->conn, $query))) {
            $admins[] = $row;
        }
        return $admins;
    }
}