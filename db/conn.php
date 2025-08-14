<?php
$conn = mysqli_connect("localhost", "root", "", "practical");
if (!$conn) {
    echo 'Connection Failed';
    die;
}