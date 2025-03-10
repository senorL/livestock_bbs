<?php
include 'db.php';

// 确保请求是POST方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => '只允许POST请求']);
    exit;
}

// 获取POST数据
$data = json_decode(file_get_contents('php://input'), true);

// 验证必要参数
if (!isset($data['user_id']) || !isset($data['content_id']) || !isset($data['content_type'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '缺少必要参数']);
    exit;
}

$user_id = $data['user_id'];
$content_id = $data['content_id'];
$content_type = $data['content_type'];

// 验证内容类型
if ($content_type !== 'post' && $content_type !== 'comment') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '内容类型无效']);
    exit;
}

// 检查用户是否已经点赞过该内容
$stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND content_id = ? AND content_type = ?");
$stmt->bind_param("iis", $user_id, $content_id, $content_type);
$stmt->execute();
$result = $stmt->get_result();

// 如果已经点赞过，则取消点赞
if ($result->num_rows > 0) {
    $like_id = $result->fetch_assoc()['id'];
    $delete_stmt = $conn->prepare("DELETE FROM likes WHERE id = ?");
    $delete_stmt->bind_param("i", $like_id);
    
    if ($delete_stmt->execute()) {
        echo json_encode(['status' => 'success', 'action' => 'unliked']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => '取消点赞失败']);
    }
    
    $delete_stmt->close();
} else {
    // 如果没有点赞过，则添加点赞
    $insert_stmt = $conn->prepare("INSERT INTO likes (user_id, content_id, content_type) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("iis", $user_id, $content_id, $content_type);
    
    if ($insert_stmt->execute()) {
        echo json_encode(['status' => 'success', 'action' => 'liked']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => '点赞失败']);
    }
    
    $insert_stmt->close();
}

$stmt->close();
$conn->close();
?>