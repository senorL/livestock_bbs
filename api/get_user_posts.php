<?php
include 'db.php';

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    
    // 使用预处理语句防止SQL注入
    $stmt = $conn->prepare("SELECT posts.id, posts.title, posts.content, posts.created_at 
                          FROM posts 
                          WHERE posts.user_id = ? 
                          ORDER BY posts.created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        // 如果内容太长，可以截断它
        if (strlen($row['content']) > 500) {
            $row['content'] = substr($row['content'], 0, 500) . '...';
        }
        $posts[] = $row;
    }
    
    echo json_encode($posts);
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => '缺少用户ID参数']);
}
?>