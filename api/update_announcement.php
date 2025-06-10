<?php
include 'db.php';

// 处理更新请求
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

$userId = intval($data['user_id'] ?? 0);

// 验证管理员权限
$checkAdmin = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$checkAdmin->bind_param("i", $userId);
$checkAdmin->execute();
$checkAdmin->store_result();
$checkAdmin->bind_result($isAdmin);
$checkAdmin->fetch();

if ($isAdmin !== 1) {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => '仅管理员可修改公告']));
}

// 处理更新请求
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

$stmt = $conn->prepare("REPLACE INTO announcements (id, text, user_id) VALUES (1, ?, ?)");
$stmt->bind_param("si", $data['text'], $userId);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => '数据库错误: ' . $stmt->error]);
}
$stmt->close();
?>