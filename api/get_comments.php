<?php
include 'db.php';

if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];
    
    // 使用预处理语句防止SQL注入
    $stmt = $conn->prepare("SELECT comments.id, comments.content, comments.created_at, users.username 
                          FROM comments JOIN users ON comments.user_id = users.id 
                          WHERE comments.post_id = ? 
                          ORDER BY comments.created_at ASC");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
    
    echo json_encode($comments);
    
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '缺少帖子ID参数']);
}
?>