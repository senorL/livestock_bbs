<?php
header('Content-Type: application/json');
include 'db.php';

$sql = "SELECT text FROM announcements ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode(['text' => $row['text']]);
} else {
    echo json_encode(['text' => '']);
}

$conn->close();
?>