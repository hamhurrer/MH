<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 验证CSRF令牌
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(["code" => 0, "message" => "CSRF验证失败"]);
    exit;
}

// 销毁session
if (isset($_SESSION['user_id'])) {
    $_SESSION = array();
    
    // 如果需要彻底销毁session，也删除session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    session_destroy();
    echo json_encode(["code" => 2, "message" => "登出成功"]);
} else {
    echo json_encode(["code" => 1, "message" => "当前未登录"]);
}