<?php
require_once __DIR__ . '/../../../app/config.php';
// 开启session
session_start();

// 设置响应头，确保中文正常显示
header('Content-Type: application/json; charset=utf-8');

// 初始化响应数据
$responseData = array("code" => "", "message" => "","csrf_token"=>"","user"=>"");

// 检查POST请求参数
if (empty($_POST['username']) || empty($_POST['password'])) {
    $responseData['code'] = 0;
    $responseData['message'] = "用户名和密码不能为空";
    echo json_encode($responseData);
    exit;
}
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(["code" => '0', "message" => "CSRF验证失败"]);
    echo json_encode($responseData);
    exit;
}
// 获取并过滤用户输入
$username = htmlspecialchars(trim($_POST['username']));
$password = htmlspecialchars(trim($_POST['password']));

// 对密码进行加密处理
$password = md5(md5($password . "aaa") . "bbb");

// 连接数据库
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 检查数据库连接
if ($mysqli->connect_error) {
    $responseData['code'] = 0;
    $responseData['message'] = "数据库连接失败: " . $mysqli->connect_error;
    echo json_encode($responseData);
    exit;
}

// 设置字符集为utf8
$mysqli->set_charset("utf8");

// 准备SQL语句
$sql = "SELECT id, username FROM users WHERE username = ? AND password = ?";
$stmt = $mysqli->prepare($sql);

// 检查预处理语句
if (!$stmt) {
    $responseData['code'] = 0;
    $responseData['message'] = "预处理语句失败: " . $mysqli->error;
    echo json_encode($responseData);
    exit;
}

// 绑定参数并执行查询
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

// 处理查询结果
if ($result->num_rows === 0) {
    $responseData['code'] = 1;
    $responseData['message'] = "用户名或密码错误";
} else {
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['login_time'] = time();
    // 生成新CSRF令牌
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $responseData['code'] = 2;
    $responseData['message'] = "登录成功";
    $responseData["csrf_token"] = $_SESSION['csrf_token'];
    $responseData['user'] = array(
        "username" => $user['username']);
    
}

// 输出JSON响应
echo json_encode($responseData);

// 关闭资源
$stmt->close();
$mysqli->close();
?>     