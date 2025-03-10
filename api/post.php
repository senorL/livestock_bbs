<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); // 临时开启显示错误
ini_set('log_errors', 1);
ini_set('error_log', '/Applications/XAMPP/xamppfiles/logs/php_errors.log');

include 'db.php';

// 检查数据库连接
if (!$conn) {
    http_response_code(500);
    die(json_encode([
        'status' => 'error',
        'message' => '数据库连接失败: ' . mysqli_connect_error()
    ]));
}

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

if (!$data || !isset($data['title'], $data['content'], $data['user_id'])) {
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

// 准备SQL语句
$sql = "INSERT INTO posts (title, content, user_id) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    http_response_code(500);
    die(json_encode([
        'status' => 'error',
        'message' => 'SQL准备失败: ' . htmlspecialchars($conn->error)
    ]));
}

// 绑定参数
$bound = $stmt->bind_param("ssi", $data['title'], $data['content'], $data['user_id']);
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