<?php
include 'db.php';

// 获取输入数据
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode([
        'status' => 'error',
        'message' => 'JSON解析失败: ' . json_last_error_msg()
    ]));
}

if (!$data || !isset($data['content'], $data['user_id'], $data['post_id'])) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => '缺少必要参数']));
}

// 检查 user_id 是否存在
$checkUser = $conn->prepare("SELECT id FROM users WHERE id = ?");
$checkUser->bind_param("i", $data['user_id']);
$checkUser->execute();
$checkUser->store_result();

if ($checkUser->num_rows === 0) {
    http_response_code(400);
    die(json_encode([
        'status' => 'error',
        'message' => '无效的用户 ID'
    ]));
}
$checkUser->close();

// 检查 post_id 是否存在
$checkPost = $conn->prepare("SELECT id FROM posts WHERE id = ?");
$checkPost->bind_param("i", $data['post_id']);
$checkPost->execute();
$checkPost->store_result();

if ($checkPost->num_rows === 0) {
    http_response_code(400);
    die(json_encode([
        'status' => 'error',
        'message' => '无效的帖子 ID'
    ]));
}
$checkPost->close();

// 准备SQL语句
$sql = "INSERT INTO comments (content, user_id, post_id) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    http_response_code(500);
    die(json_encode([
        'status' => 'error',
        'message' => 'SQL准备失败: ' . htmlspecialchars($conn->error)
    ]));
}

// 绑定参数
$bound = $stmt->bind_param("sii", $data['content'], $data['user_id'], $data['post_id']);
if ($bound === false) {
    http_response_code(500);
    die(json_encode([
        'status' => 'error',
        'message' => '参数绑定失败: ' . htmlspecialchars($stmt->error)
    ]));
}

// 执行查询
$executed = $stmt->execute();
if ($executed === false) {
    http_response_code(500);
    die(json_encode([
        'status' => 'error',
        'message' => '执行失败: ' . htmlspecialchars($stmt->error)
    ]));
}

// 成功响应
echo json_encode([
    'status' => 'success',
    'insert_id' => $stmt->insert_id
]);

// 清理资源
$stmt->close();
$conn->close();
?>