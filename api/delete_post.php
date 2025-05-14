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

if (!$data || !isset($data['post_id'], $data['user_id'])) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => '缺少必要参数']));
}

// 检查用户是否为管理员
$checkAdmin = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$checkAdmin->bind_param("i", $data['user_id']);
$checkAdmin->execute();
$checkAdmin->store_result();
$checkAdmin->bind_result($isAdmin);
$checkAdmin->fetch();
$checkAdmin->close();

// 如果是管理员，跳过帖子所有者检查
if ($isAdmin !== 1) {
    // 检查帖子是否存在且属于该用户
    $checkPost = $conn->prepare("SELECT id FROM posts WHERE id = ? AND user_id = ?");
    $checkPost->bind_param("ii", $data['post_id'], $data['user_id']);
    $checkPost->execute();
    $checkPost->store_result();

    if ($checkPost->num_rows === 0) {
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => '您没有权限删除此帖子或帖子不存在'
        ]));
    }
    $checkPost->close();
}

// 首先删除与帖子相关的所有评论
$deleteComments = $conn->prepare("DELETE FROM comments WHERE post_id = ?");
$deleteComments->bind_param("i", $data['post_id']);
$deleteComments->execute();
$deleteComments->close();

// 然后删除帖子
$deletePost = $conn->prepare("DELETE FROM posts WHERE id = ?");
// 管理员可以删除任意帖子，普通用户只能删除自己的帖子
if ($isAdmin !== 1) {
    $deletePost->bind_param("ii", $data['post_id'], $data['user_id']);
} else {
    $deletePost->bind_param("i", $data['post_id']);
}

if ($deletePost->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '删除帖子失败: ' . htmlspecialchars($conn->error)
    ]);
}

$deletePost->close();
$conn->close();
?>