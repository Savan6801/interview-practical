<?php
// db.php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // change
$DB_NAME = 'practical';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("DB Connect failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
