<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取表单数据
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // 加密密码
    
    // 检查用户名是否已存在
    $checkUsername = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $checkUsername->bind_param("s", $username);
    $checkUsername->execute();
    $checkUsername->store_result();
    
    if ($checkUsername->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => '用户名已被使用，请选择其他用户名']);
        $checkUsername->close();
        exit;
    }
    $checkUsername->close();
    
    // 检查邮箱是否已存在
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();
    
    if ($checkEmail->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => '该邮箱已被注册，请使用其他邮箱']);
        $checkEmail->close();
        exit;
    }
    $checkEmail->close();
    
    // 插入新用户
    $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $email);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    
    $stmt->close();
}
?>
