<?php
include 'db.php';

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    
    // 使用预处理语句防止SQL注入
    $stmt = $conn->prepare("SELECT u.id, u.username, u.avatar 
                          FROM user_follows f 
                          JOIN users u ON f.following_id = u.id 
                          WHERE f.follower_id = ? 
                          ORDER BY f.created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $following = [];
    while ($row = $result->fetch_assoc()) {
        $following[] = $row;
    }
    
    echo json_encode($following);
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => '缺少用户ID参数']);
}
?>