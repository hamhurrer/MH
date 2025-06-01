<?php
require_once __DIR__ . '/../../../app/config.php';
session_start();
header('Content-Type: application/json');
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$mysqli->set_charset("utf8mb4");
// 验证CSRF令牌
// if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
//     die(json_encode(['status' => 'error', 'message' => 'CSRF token验证失败']));
// }

// 验证登录状态
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => '请先登录']);
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'get_current_user':
        // 获取当前用户ID
        echo json_encode(['status' => 'success', 'user_id' => $_SESSION['user_id'],'username'=>$_SESSION['username'] ?? null]);
        break;
        
    case 'get_posts':
            // 获取帖子列表 - 包含post_image字段
            $result = $mysqli->query("SELECT * FROM posts ORDER BY create_time DESC");
            if (!$result) {
                echo json_encode(['status' => 'error', 'message' => '获取帖子失败: ' . $mysqli->error]);
                exit;
            }
            $posts = $result->fetch_all(MYSQLI_ASSOC);
            
            // 获取每个帖子的评论
            foreach ($posts as &$post) {
                $stmt = $mysqli->prepare("SELECT * FROM comment WHERE post_id = ? ORDER BY create_time");
                if (!$stmt) {
                    echo json_encode(['status' => 'error', 'message' => '准备评论查询失败: ' . $mysqli->error]);
                    exit;
                }
                $stmt->bind_param("i", $post['id']);
                if (!$stmt->execute()) {
                    echo json_encode(['status' => 'error', 'message' => '执行评论查询失败: ' . $stmt->error]);
                    exit;
                }
                $commentResult = $stmt->get_result();
                if (!$commentResult) {
                    echo json_encode(['status' => 'error', 'message' => '获取评论结果失败: ' . $stmt->error]);
                    exit;
                }
                $post['comments'] = $commentResult->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            }
            
            echo json_encode(['status' => 'success', 'posts' => $posts]);
            break;        
    case 'add_post':
        $content = htmlspecialchars(trim($_POST['content']));
        $tags = htmlspecialchars(trim($_POST['tags'] ?? ''));
        $create_time = (int) $_POST['create_time'];
        $post_image = null;

        if (empty($content)) {
            echo json_encode(['status' => 'error', 'message' => '内容不能为空']);
            exit;
        }

        // 处理图片上传
        if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['post_image'];
            $uploadDir = 'post_images/';
            
            // 检查目录是否存在
            if (!is_dir('../'.$uploadDir)) {
                echo json_encode(['status' => 'error', 'message' => '上传目录不存在']);
                exit;
            }
            
            // 验证文件类型
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode(['status' => 'error', 'message' => '只支持JPG、PNG、GIF格式']);
                exit;
            }
            
            // 验证文件大小 (5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                echo json_encode(['status' => 'error', 'message' => '图片大小不能超过5MB']);
                exit;
            }
            
            // 生成唯一文件名
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'post_' . $_SESSION['user_id'] . '_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
            $filePath = $uploadDir . $fileName;
            
            // 移动上传的文件
            if (move_uploaded_file($file['tmp_name'], '../'.$filePath)) {
                $post_image = $filePath;
            } else {
                echo json_encode(['status' => 'error', 'message' => '图片上传失败']);
                exit;
            }
        }

        // 插入帖子数据 - 包含post_image和tags字段
        $sql = "INSERT INTO posts (user_id, content, create_time, username, post_image, tags) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => '准备SQL语句失败: ' . $mysqli->error]);
            exit;
        }
        
        $stmt->bind_param("ssisss", $_SESSION['user_id'], $content, $create_time, $_SESSION['username'], $post_image, $tags);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'post_id' => $stmt->insert_id, 'image_path' => $post_image]);
        } else {
            // 如果数据库插入失败且有上传的图片，删除已上传的图片
            if ($post_image && file_exists($post_image)) {
                unlink($post_image);
            }
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        $stmt->close();
        break;
        
    case 'add_comment':
        $post_id = (int)$_POST['post_id'];
        $content = htmlspecialchars(trim($_POST['content']));
        $create_time = (int) $_POST['create_time'];
        
        if (empty($content)) {
            echo json_encode(['status' => 'error', 'message' => '评论内容不能为空']);
            exit;
        }
        
        $stmt = $mysqli->prepare("INSERT INTO comment (post_id, user_id, content, create_time, username) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => '准备SQL语句失败: ' . $mysqli->error]);
            exit;
        }
        
        $stmt->bind_param("iisis", $post_id, $_SESSION['user_id'], $content, $create_time, $_SESSION['username']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'comment_id' => $stmt->insert_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        $stmt->close();
        break;
        
    case 'delete_post':
        $post_id = (int)$_POST['post_id'];
        
        // 验证帖子是否属于当前用户，并获取帖子信息
        $stmt = $mysqli->prepare("SELECT user_id, post_image FROM posts WHERE id = ?");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => '准备SQL语句失败: ' . $mysqli->error]);
            exit;
        }
        
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $post = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$post) {
            echo json_encode(['status' => 'error', 'message' => '帖子不存在']);
            exit;
        }
        
        if ($post['user_id'] != $_SESSION['user_id']) {
            echo json_encode(['status' => 'error', 'message' => '无权限删除此帖子']);
            exit;
        }
        
        $mysqli->autocommit(FALSE);
        try {
            // 删除评论
            $stmt = $mysqli->prepare("DELETE FROM comment WHERE post_id = ?");
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            $stmt->close();
            
            // 删除帖子
            $stmt = $mysqli->prepare("DELETE FROM posts WHERE id = ?");
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            $stmt->close();
            
            $mysqli->commit();
            
            // 如果帖子有图片，删除图片文件
            if ($post['post_image'] && file_exists($post['post_image'])) {
                unlink($post['post_image']);
            }
            
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $mysqli->rollback();
            echo json_encode(['status' => 'error', 'message' => '删除失败: ' . $e->getMessage()]);
        }
        $mysqli->autocommit(TRUE);
        break;
        
    case 'delete_comment':
        $comment_id = (int)$_POST['comment_id'];
        
        // 验证评论是否属于当前用户
        $stmt = $mysqli->prepare("SELECT user_id FROM comment WHERE id = ?");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => '准备SQL语句失败: ' . $mysqli->error]);
            exit;
        }
        
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $comment = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$comment) {
            echo json_encode(['status' => 'error', 'message' => '评论不存在']);
            exit;
        }
        
        if ($comment['user_id'] != $_SESSION['user_id']) {
            echo json_encode(['status' => 'error', 'message' => '无权限删除此评论']);
            exit;
        }
        
        $stmt = $mysqli->prepare("DELETE FROM comment WHERE id = ?");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => '准备SQL语句失败: ' . $mysqli->error]);
            exit;
        }
        
        $stmt->bind_param("i", $comment_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        $stmt->close();
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => '无效操作']);
}

// 关闭数据库连接
$mysqli->close();
?>