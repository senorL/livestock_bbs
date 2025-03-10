<?php
include 'db.php';

if (isset($_GET['id'])) {
    $post_id = $_GET['id'];
    
    // 使用预处理语句防止SQL注入
    $stmt = $conn->prepare("SELECT posts.id, posts.title, posts.content, posts.created_at, posts.user_id, users.username 
                          FROM posts JOIN users ON posts.user_id = users.id 
                          WHERE posts.id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        echo json_encode($post);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => '帖子不存在']);
    }
    
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '缺少帖子ID参数']);
}
?>