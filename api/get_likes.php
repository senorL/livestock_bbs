<?php
include 'db.php';

// 获取内容的点赞数量和用户是否已点赞
if (isset($_GET['content_id']) && isset($_GET['content_type'])) {
    $content_id = $_GET['content_id'];
    $content_type = $_GET['content_type'];
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
    
    // 验证内容类型
    if ($content_type !== 'post' && $content_type !== 'comment') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => '内容类型无效']);
        exit;
    }
    
    // 获取点赞总数
    $count_stmt = $conn->prepare("SELECT COUNT(*) as likes_count FROM likes WHERE content_id = ? AND content_type = ?");
    $count_stmt->bind_param("is", $content_id, $content_type);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $likes_count = $count_result->fetch_assoc()['likes_count'];
    
    // 检查当前用户是否已点赞
    $is_liked = false;
    if ($user_id > 0) {
        $check_stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND content_id = ? AND content_type = ?");
        $check_stmt->bind_param("iis", $user_id, $content_id, $content_type);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $is_liked = $check_result->num_rows > 0;
        $check_stmt->close();
    }
    
    // 返回结果
    echo json_encode([
        'status' => 'success',
        'likes_count' => $likes_count,
        'is_liked' => $is_liked
    ]);
    
    $count_stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '缺少必要参数']);
}

$conn->close();
?>