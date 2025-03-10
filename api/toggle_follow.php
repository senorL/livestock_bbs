<?php
include 'db.php';

// 获取POST数据
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['follower_id']) && isset($data['following_id'])) {
    $follower_id = $data['follower_id'];
    $following_id = $data['following_id'];
    
    // 检查用户是否存在
    $check_users = $conn->prepare("SELECT id FROM users WHERE id IN (?, ?)");
    $check_users->bind_param("ii", $follower_id, $following_id);
    $check_users->execute();
    $check_users->store_result();
    
    if ($check_users->num_rows < 2) {
        echo json_encode(['status' => 'error', 'message' => '用户不存在']);
        $check_users->close();
        exit;
    }
    $check_users->close();
    
    // 检查是否已关注
    $check_follow = $conn->prepare("SELECT * FROM user_follows WHERE follower_id = ? AND following_id = ?");
    $check_follow->bind_param("ii", $follower_id, $following_id);
    $check_follow->execute();
    $check_follow->store_result();
    $is_following = $check_follow->num_rows > 0;
    $check_follow->close();
    
    if ($is_following) {
        // 已关注，取消关注
        $stmt = $conn->prepare("DELETE FROM user_follows WHERE follower_id = ? AND following_id = ?");
        $stmt->bind_param("ii", $follower_id, $following_id);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode(['status' => 'success', 'is_following' => false, 'message' => '已取消关注']);
    } else {
        // 未关注，添加关注
        $stmt = $conn->prepare("INSERT INTO user_follows (follower_id, following_id, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $follower_id, $following_id);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode(['status' => 'success', 'is_following' => true, 'message' => '关注成功']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => '缺少必要参数']);
}
?>