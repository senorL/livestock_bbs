<?php
include 'db.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    // 使用预处理语句防止SQL注入
    $stmt = $conn->prepare("SELECT id, username, email, avatar, bio, gender, location, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // 获取用户发布的帖子数量
        $posts_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM posts WHERE user_id = ?");
        $posts_count_stmt->bind_param("i", $user_id);
        $posts_count_stmt->execute();
        $posts_count_result = $posts_count_stmt->get_result();
        $posts_count = $posts_count_result->fetch_assoc()['count'];
        $posts_count_stmt->close();
        
        // 获取粉丝数量
        $followers_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_follows WHERE following_id = ?");
        $followers_count_stmt->bind_param("i", $user_id);
        $followers_count_stmt->execute();
        $followers_count_result = $followers_count_stmt->get_result();
        $followers_count = $followers_count_result->fetch_assoc()['count'];
        $followers_count_stmt->close();
        
        // 获取关注数量
        $following_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_follows WHERE follower_id = ?");
        $following_count_stmt->bind_param("i", $user_id);
        $following_count_stmt->execute();
        $following_count_result = $following_count_stmt->get_result();
        $following_count = $following_count_result->fetch_assoc()['count'];
        $following_count_stmt->close();
        
        // 添加统计数据到用户信息中
        $user['posts_count'] = $posts_count;
        $user['followers_count'] = $followers_count;
        $user['following_count'] = $following_count;
        
        echo json_encode(['status' => 'success', 'user' => $user]);
    } else {
        echo json_encode(['status' => 'error', 'message' => '用户不存在']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => '缺少用户ID参数']);
}
?>