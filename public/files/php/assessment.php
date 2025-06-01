<?php
require_once __DIR__ . '/../../../app/config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => '用户未登录']);
    exit;
}

// 检查请求方法和action参数
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'get_assessment') {
    echo json_encode(['status' => 'error', 'message' => '无效的请求']);
    exit;
}

function getMentalAssessment($user_id) {
    // 创建 mysqli 连接，参考save_mood.php的连接方式
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8mb4");
    
    if ($conn->connect_error) {
        return ['status' => 'error', 'message' => '数据库连接失败: ' . $conn->connect_error];
    }

    try {
        // 提取用户的评论内容
        $comment_sql = "SELECT content FROM comment WHERE user_id = ?";
        $comment_stmt = $conn->prepare($comment_sql);
        if (!$comment_stmt) {
            throw new Exception('准备评论查询失败: ' . $conn->error);
        }
        $comment_stmt->bind_param("i", $user_id);
        $comment_stmt->execute();
        $comment_result = $comment_stmt->get_result();
        $comments = [];
        while ($comment_row = $comment_result->fetch_assoc()) {
            $comments[] = $comment_row['content'];
        }
        $comment_stmt->close();

        // 提取用户的情绪日记内容和type
        $mood_sql = "SELECT description, type FROM mood_records WHERE user_id = ?";
        $mood_stmt = $conn->prepare($mood_sql);
        if (!$mood_stmt) {
            throw new Exception('准备情绪查询失败: ' . $conn->error);
        }
        $mood_stmt->bind_param("i", $user_id);
        $mood_stmt->execute();
        $mood_result = $mood_stmt->get_result();
        $moods = [];
        while ($mood_row = $mood_result->fetch_assoc()) {
            $moods[] = [
                'description' => $mood_row['description'],
                'type' => $mood_row['type']
            ];
        }
        $mood_stmt->close();

        // 提取用户帖子内容和tags
        $post_sql = "SELECT content, tags FROM posts WHERE user_id = ?";
        $post_stmt = $conn->prepare($post_sql);
        if (!$post_stmt) {
            throw new Exception('准备帖子查询失败: ' . $conn->error);
        }
        $post_stmt->bind_param("i", $user_id);
        $post_stmt->execute();
        $post_result = $post_stmt->get_result();
        $posts = [];
        while ($post_row = $post_result->fetch_assoc()) {
            $posts[] = [
                'content' => $post_row['content'],
                'tags' => $post_row['tags']
            ];
        }
        $post_stmt->close();

        // 关闭数据库连接
        $conn->close();

        // 检查是否有数据
        if (empty($comments) && empty($moods) && empty($posts)) {
            return [
                'status' => 'error',
                'message' => '目前没有足够的数据进行心理评估，建议多使用平台功能记录心情和发表内容。'
            ];
        }

        // API配置
        $api_url = 'https://api.coze.cn/v3/chat';
        $bot_id = '7510586171891089427';
        $api_key = 'pat_lLCLYI0yYq4zQKtP3FfSCuWMbvL0zkbsi8iRTXJiDgwqnB0mWg8coEjvulcSSxic';

        // 构建心理评估prompt
        $prompt = "请作为专业心理咨询师，基于以下用户数据进行心理状态评估：\n\n";
        
        if (!empty($comments)) {
            $prompt .= "用户评论内容：\n" . implode("\n", array_slice($comments, -5)) . "\n\n";
        }
        
        if (!empty($moods)) {
            $prompt .= "情绪记录：\n";
            foreach (array_slice($moods, -5) as $mood) {
                $prompt .= "类型：{$mood['type']}，描述：{$mood['description']}\n";
            }
            $prompt .= "\n";
        }
        
        if (!empty($posts)) {
            $prompt .= "发帖内容：\n";
            foreach (array_slice($posts, -3) as $post) {
                $prompt .= "内容：{$post['content']}";
                if (!empty($post['tags'])) {
                    $prompt .= "，标签：{$post['tags']}";
                }
                $prompt .= "\n";
            }
        }
        
        $prompt .= "\n请提供详细的心理状态分析和建议。请用中文回复。";

        // 构建API请求数据 - 使用流式响应
        $api_data = [
            'bot_id' => $bot_id,
            'user_id' => (string)$user_id,
            'stream' => true, // 使用流式响应
            'auto_save_history' => true,
            'additional_messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                    'content_type' => 'text'
                ]
            ]
        ];

        $debug_info = [
            'step' => 'initial',
            'api_url' => $api_url,
            'request_data' => $api_data,
            'prompt_sent' => $prompt,
            'user_data_summary' => [
                'comments_count' => count($comments),
                'moods_count' => count($moods),
                'posts_count' => count($posts),
                'comments' => array_slice($comments, -3),
                'moods' => array_slice($moods, -3),
                'posts' => array_slice($posts, -2)
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];

        try {
            // 发送API请求
            $ch = curl_init($api_url);
            
            if (!$ch) {
                throw new Exception('无法初始化CURL');
            }
            
            curl_setopt_array($ch, [
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => json_encode($api_data),
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $api_key,
                    'Content-Type: application/json',
                    'User-Agent: Mozilla/5.0 (compatible; PHP Assessment Bot)'
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 45,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3
            ]);

            $debug_info['step'] = 'curl_configured';

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_errno = curl_errno($ch);
            $curl_error = curl_error($ch);
            $curl_info = curl_getinfo($ch);

            curl_close($ch);

            $debug_info = array_merge($debug_info, [
                'step' => 'curl_executed',
                'curl_errno' => $curl_errno,
                'curl_error' => $curl_error,
                'http_code' => $http_code,
                'response_raw' => $response,
                'response_length' => strlen($response),
                'curl_info' => $curl_info
            ]);

            // 检查CURL错误
            if ($curl_errno !== 0) {
                throw new Exception("CURL错误 #{$curl_errno}: {$curl_error}");
            }

            // 检查HTTP状态码
            if ($http_code !== 200) {
                throw new Exception("HTTP错误码: {$http_code}，响应: " . substr($response, 0, 200));
            }

            // 检查响应是否为空
            if (empty($response)) {
                throw new Exception('API返回空响应');
            }

            $debug_info['step'] = 'parsing_sse_response';

            // 解析SSE流式响应 - 参考pbzs.html的处理方式
            $extracted_content = '';
            $sse_events = [];
            $last_answer_content = '';
            
            // 按行分割响应
            $lines = explode("\n", $response);
            $current_event = '';
            $current_data = '';
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                if (empty($line)) {
                    // 空行表示一个SSE事件结束
                    if (!empty($current_event) && !empty($current_data)) {
                        $sse_events[] = [
                            'event' => $current_event,
                            'data' => $current_data
                        ];
                        
                        // 处理事件
                        if ($current_event === 'conversation.message.delta') {
                            // 解析delta数据
                            $delta_data = json_decode($current_data, true);
                            if ($delta_data && isset($delta_data['type']) && $delta_data['type'] === 'answer') {
                                if (isset($delta_data['content'])) {
                                    $extracted_content .= $delta_data['content'];
                                }
                            }
                        } elseif ($current_event === 'conversation.message.completed') {
                            // 解析completed数据
                            $completed_data = json_decode($current_data, true);
                            if ($completed_data && isset($completed_data['type']) && $completed_data['type'] === 'answer') {
                                if (isset($completed_data['content'])) {
                                    $last_answer_content = $completed_data['content'];
                                }
                            }
                        }
                    }
                    
                    // 重置当前事件
                    $current_event = '';
                    $current_data = '';
                } elseif (strpos($line, 'event:') === 0) {
                    $current_event = trim(substr($line, 6));
                } elseif (strpos($line, 'data:') === 0) {
                    $current_data = trim(substr($line, 5));
                }
            }
            
            // 如果有完整的answer内容，使用它替代拼接的内容
            if (!empty($last_answer_content)) {
                $extracted_content = $last_answer_content;
            }
            
            $debug_info['sse_events_count'] = count($sse_events);
            $debug_info['extracted_content_length'] = strlen($extracted_content);
            $debug_info['sse_events'] = array_slice($sse_events, 0, 10); // 只保留前10个事件用于调试
            $debug_info['last_answer_content'] = $last_answer_content;

            $debug_info['step'] = 'sse_parsed';

            // 返回提取的内容
            return [
                'status' => 'success',
                'score' => !empty($extracted_content) ? '智能分析完成' : 'API响应但未提取到内容',
                'analysis' => !empty($extracted_content) ? $extracted_content : '未找到有效内容',
                'extracted_content' => $extracted_content,
                'debug_info' => $debug_info,
                'sse_events_sample' => array_slice($sse_events, -5) // 最后5个事件用于前端显示
            ];

        } catch (Exception $e) {
            $debug_info['step'] = 'error';
            $debug_info['exception'] = $e->getMessage();
            $debug_info['trace'] = $e->getTraceAsString();

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'debug_info' => $debug_info
            ];
        }

    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->close();
        }
        return [
            'status' => 'error', 
            'message' => '系统错误: ' . $e->getMessage(),
            'debug_info' => [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ];
    }
}

// 执行评估
$user_id = $_SESSION['user_id'];
$result = getMentalAssessment($user_id);

echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>