<?php
$host = 'localhost';
$db = 'livestock_forum';
$user = 'root';
$pass = ''; // XAMPP默认密码为空

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
