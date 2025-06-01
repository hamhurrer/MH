<?php
require_once __DIR__ . '/../../../app/config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'User not logged in']));
}

// 创建mysqli连接
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

try {
    // 获取用户的所有情绪记录
    $stmt = $conn->prepare("SELECT date, type, description, completed FROM mood_records WHERE user_id = ? ORDER BY date");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'id' => $row['date'],
            'type' => $row['type'],
            'description' => $row['description'],
            'completed' => (bool)$row['completed']
        ];
    }
    
    echo json_encode(['success' => true, 'events' => $events]);
    
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>