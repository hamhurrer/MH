<?php
require_once __DIR__ . '/../../../app/config.php';
// 设置响应头，确保中文正常显示
header('Content-Type: application/json; charset=utf-8');

// 初始化响应数据
$responseData = array("code" => "", "message" => "");

// 检查POST请求参数
if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['create_time'])) {
    $responseData['code'] = 0;
    $responseData['message'] = "用户名、密码和创建时间不能为空";
    echo json_encode($responseData);
    exit;
}

// 获取并过滤用户输入
$username = htmlspecialchars(trim($_POST['username']));
$password = htmlspecialchars(trim($_POST['password']));
$create_time = (int) $_POST['create_time']; // 确保创建时间是整数

// 对密码进行加密处理
$password = md5(md5($password . "aaa") . "bbb");

// 使用mysqli扩展连接数据库
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 检查数据库连接
if ($mysqli->connect_error) {
    $responseData['code'] = 0;
    $responseData['message'] = "数据库连接失败: " . $mysqli->connect_error;
    echo json_encode($responseData);
    exit;
}

// 设置字符集为utf8mb4
$mysqli->set_charset("utf8mb4");

// 检查用户名是否已存在（使用预处理语句）
$sql = "SELECT id FROM users WHERE username = ? LIMIT 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $responseData['code'] = 1;
    $responseData['message'] = "用户名已存在";
    echo json_encode($responseData);
    $stmt->close();
    $mysqli->close();
    exit;
}
$stmt->close();

// 插入新用户（使用预处理语句）
$sql = "INSERT INTO users (username, password, create_time) VALUES (?, ?, ?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ssi", $username, $password, $create_time);

if ($stmt->execute()) {
    $responseData['code'] = 3;
    $responseData['message'] = "注册成功";
} else {
    $responseData['code'] = 2;
    $responseData['message'] = "注册失败: " . $stmt->error;
}

// 输出JSON响应
echo json_encode($responseData);

// 关闭资源
$stmt->close();
$mysqli->close();

?>     