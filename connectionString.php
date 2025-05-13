<?php
$servername = "localhost";
$root_username = "root";
$password = "";
$dbname = "museum_db";

$conn = new mysqli($servername, $root_username, $password, $dbname);

if ($conn->connect_error) {
    die("Помилка підключення: " . $conn->connect_error);
}
session_start();
?>