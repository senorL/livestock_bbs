<?php
include 'db.php';

if (isset($_GET['follower_id']) && isset($_GET['following_id'])) {
    $follower_id = $_GET['follower_id'];
    $following_id = $_GET['following_id'];
    
    // 使用预处理语句防止SQL注入
    $stmt = $conn->prepare("SELECT * FROM user_follows WHERE follower_id = ? AND following_id = ?");
    $stmt->bind_param("ii", $follower_id, $following_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // 检查是否已关注
    $is_following = $result->num_rows > 0;
    
    echo json_encode(['is_following' => $is_following]);
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => '缺少必要参数']);
}
?>