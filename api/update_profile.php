<?php
include 'db.php';

// 获取POST数据
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id'])) {
    $user_id = $data['user_id'];
    
    // 检查用户是否存在
    $check_user = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $check_user->bind_param("i", $user_id);
    $check_user->execute();
    $check_user->store_result();
    
    if ($check_user->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => '用户不存在']);
        $check_user->close();
        exit;
    }
    $check_user->close();
    
    // 准备更新字段
    $fields = [];
    $types = "";
    $values = [];
    
    // 检查并添加各个可选字段
    if (isset($data['bio'])) {
        $fields[] = "bio = ?";
        $types .= "s";
        $values[] = $data['bio'];
    }
    
    if (isset($data['gender'])) {
        $fields[] = "gender = ?";
        $types .= "s";
        $values[] = $data['gender'];
    }
    
    if (isset($data['location'])) {
        $fields[] = "location = ?";
        $types .= "s";
        $values[] = $data['location'];
    }
    
    if (isset($data['avatar'])) {
        $fields[] = "avatar = ?";
        $types .= "s";
        $values[] = $data['avatar'];
    }
    
    // 如果没有要更新的字段，直接返回成功
    if (empty($fields)) {
        echo json_encode(['status' => 'success', 'message' => '没有字段需要更新']);
        exit;
    }
    
    // 构建SQL语句
    $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
    $types .= "i";
    $values[] = $user_id;
    
    // 执行更新
    $stmt = $conn->prepare($sql);
    
    // 动态绑定参数
    $stmt->bind_param($types, ...$values);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => '个人资料已更新']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '更新失败: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => '缺少用户ID参数']);
}
?>