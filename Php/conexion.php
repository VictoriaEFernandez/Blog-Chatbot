<?php
$servername = "localhost";
$username = "root";
$password = ""; // Deja vacío si estás usando XAMPP
$dbname = "proyectominutri";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
