<?php
include 'db.php';

$sql = "SELECT posts.id, posts.title, posts.content, posts.created_at, posts.user_id, users.username 
        FROM posts JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC";
$result = $conn->query($sql);
$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}
echo json_encode($posts);
?>
