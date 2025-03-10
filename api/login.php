<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 使用预处理语句防止SQL注入
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            echo json_encode(['status' => 'success', 'user_id' => $user['id']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => '密码错误，请重试']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => '用户不存在，请检查用户名']);
    }
    
    $stmt->close();
}
?>
