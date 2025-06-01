<?php
require_once __DIR__ . '/../../../app/config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'User not logged in']));
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['date'])) {
    die(json_encode(['error' => 'Invalid data']));
}

// 创建mysqli连接
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

try {
    // 检查是否已有该日期的记录
    $stmt = $conn->prepare("SELECT id FROM mood_records WHERE user_id = ? AND date = ?");
    $stmt->bind_param("is", $_SESSION['user_id'], $data['date']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $type = isset($data['type']) ? $data['type'] : null;
    $description = isset($data['description']) ? $data['description'] : null;
    $completed = isset($data['completed']) ? (bool)$data['completed'] : false;
    $user_id = $_SESSION['user_id'];
    $date = $data['date'];
    
    if ($result->num_rows > 0) {
        // 更新现有记录
        $sql = "UPDATE mood_records SET type = ?, description = ?, completed = ? WHERE user_id = ? AND date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiis", $type, $description, $completed, $user_id, $date);
    } else {
        // 插入新记录
        $sql = "INSERT INTO mood_records (user_id, date, type, description, completed) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $user_id, $date, $type, $description, $completed);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data saved successfully']);
    } else {
        echo json_encode(['error' => 'Failed to save data: ' . $stmt->error]);
    }
    
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>