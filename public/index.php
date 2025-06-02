<?php
session_start();

// 生成CSRF令牌（如果不存在）
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 检查用户是否已登录
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindheaven</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="./files/css/index.css">
    <link rel="stylesheet" href="./files/css/post.css">
     <link rel="stylesheet" href="./files/css/analysis.css">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    

</head>
<body>
    
<!-- header section starts  -->
<header class="header">
    <div class="nav">
        <div class="nav-wrapper">
            <div class="nav-logo">
                <img src="files/image_webp/logo.webp" alt="Logo">
            </div>
            <div class="nav-item">
                <a href="#" class="alink">
                    <span>🪵树洞</span>
                    <img src="./files/asset/drop.png" alt="">
                </a>
                <div class="nav-drop-down-wrapper">
                    <div class="nav-drop-down">
                        <div class="down-item">
                            <div class="down-item-wrapper">
                                <a href="./files/html/mood.html">📒情绪日记</a>
                            </div>
                        </div>
                        <div class="down-item">
                            <div class="down-item-wrapper">
                                <a href="./files/html/PBZS.html">🫂陪伴助手</a>
                            </div>
                        </div>
                        <div class="down-item">
                            <div class="down-item-wrapper">
                                <a href="./files/html/DAZS.html">📖答案之书</a>
                            </div>
                      </div>                
                    </div>
                </div>
            </div>
            <div class="nav-item">
                <a href="#" class="alink">
                    <span>🔅放松</span>
                    <img src="./files/asset/drop.png" alt="">
                </a>
               <div class="nav-drop-down-wrapper">
                    <div class="nav-drop-down">
                        <div class="down-item">
                            <div class="down-item-wrapper">
                                <a href="./files/html/music.html">🧘🏻‍♀️冥想</a>
                            </div>
                        </div>            
                        <div class="down-item">
                            <div class="down-item-wrapper">
                                <a href="./files/html/DY.html">👩🏻‍💻电影</a>
                            </div>
                        </div>                                
                        <div class="down-item">
                            <div class="down-item-wrapper">
                                <a href="./files/html/youxi.html">🤖小游戏</a>
                            </div>
                        </div>  
                    </div>
                </div>                
            </div>
            <div class="nav-item">
                <a href="#" class="alink">
                    <span>💖爱好</span>
                    <img src="./files/asset/drop.png" alt="">
                </a>
                <div class="nav-drop-down-wrapper">
                    <div class="nav-drop-down">
                        <div class="down-item">
                            <div class="down-item-wrapper">
                                <a href="./files/html/sport.html">🏃‍运动</a>
                            </div>
                        </div>
                        <div class="down-item">
                            <div class="down-item-wrapper">
                                <a href="./files/html/cook.html">🍳烹饪</a>
                            </div>
                        </div>
                        <div class="down-item">
                            <div class="down-item-wrapper">
                                <a href="./files/html/art.html">🎨艺术</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="nav-item">
                <a href="#" class="alink">
                    <span>💁帮助</span>
                    <img src="./files/asset/drop.png" alt="">
                </a>
                <div class="nav-drop-down-wrapper">
                    <div class="nav-drop-down">
                        <div class="down-item">
                            <div class="down-item-wrapper">
                                <a href="./files/html/knowle.html" >📕知识科普</a>
                            </div>
                        </div>
                        <div class="down-item">
                            <div class="down-item-wrapper">
                                <a href="./files/html/psych.html" >⛓️心理咨询</a>
                            </div>
                        </div>
                        <div class="down-item">
                            <div class="down-item-wrapper">
                                <a href="./files/html/law.html" >⚖法律援助</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="nav-item">
                <div class="icons">
                    <div id="login-btn" class="fas fa-user"></div>
                </div>
            </div>
            <?php if ($isLoggedIn): ?>
            <div class="nav-item">
                <div action="" >
                    <h1 id="name" >欢迎回来，<?= $_SESSION['username']?></h1>
                </div>
            </div>
            <div class="nav-item">
                <div>
                    <div id="alert1">测试文字</div>
                    <h1 id="logout-btn">退出登录</h1>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="login-form">
        <h3 class="text-xl font-bold mb-4">登录</h3>
        <input type="text" placeholder="输入用户名" class="box">
        <input type="password" placeholder="输入密码" class="box">
        <div class="remember flex items-center mb-4">
            <input type="checkbox" name="" id="remember-me">
            <label for="remember-me" class="ml-2">记住我</label>
        </div>
        <div id="alert0">测试文字</div>
        <input type="submit" value="立即登录" class="btn" id="submit-btn">
        <input type="hidden" id="csrf-token" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <p class="mt-2">没有账号? <a href="#" id="create-btn" class="text-blue-500 hover:underline">注册</a></p>
    </div>

    <div class="create-form">
        <h3 class="text-xl font-bold mb-4">注册</h3>
        <input type="text" placeholder="输入用户名" class="box">
        <input type="password" placeholder="输入密码" class="box">
        <div id="alert">测试文字</div>
        <input type="submit" value="立即注册" class="btn" id="register-btn">
    </div>
</header>

<!-- 心理分析按钮（仅登录用户可见） -->
<?php if ($isLoggedIn): ?>
<button class="analysis-btn" id="analysis-btn">
    <i class="fas fa-brain"></i>
    心理分析
</button>
<?php endif; ?>

<!-- 心理分析弹窗 -->
<div class="analysis-modal" id="analysis-modal">
    <div class="analysis-modal-content">
        <div class="analysis-modal-header">
            <h2 class="analysis-modal-title">🧠 心理状态分析</h2>
            <button class="analysis-modal-close" id="analysis-modal-close">&times;</button>
        </div>
        <div class="analysis-modal-body">
            <!-- 加载状态 -->
            <div class="analysis-loading" id="analysis-loading">
                <div class="spinner"></div>
                <h3>正在分析您的心理状态...</h3>
                <p>AI正在综合分析您的情绪记录、发帖内容和评论数据，请稍候</p>
            </div>
            
            <!-- 分析结果 -->
            <div class="analysis-result" id="analysis-result">
                <div class="analysis-score">
                    <h3>综合评估</h3>
                    <p class="score-value" id="analysis-score-value">智能分析完成</p>
                </div>
                <div class="analysis-content">
                    <h4>📋 详细分析报告</h4>
                    <div id="analysis-content-text">
                        <!-- 分析内容将在这里显示 -->
                    </div>
                </div>
            </div>
            
            <!-- 错误状态 -->
            <div class="analysis-error" id="analysis-error" style="display: none;">
                <div class="error-icon">⚠️</div>
                <h3>分析失败</h3>
                <p id="error-message">抱歉，无法完成心理分析，请稍后重试</p>
                <button class="retry-btn" id="retry-analysis">重新分析</button>
            </div>
        </div>
    </div>
</div>
        <section class="parallax">
            <img src="./files/image_webp/hill11.webp" id="hill1" alt="">
            <img src="./files/image_webp/hill00.webp" id="hill0" alt="">
            <img src="./files/image_webp/hill01.webp"  id="h" alt="">
            <img src="./files/image_webp/star.webp" id="star" alt="">
            <img src="./files/image_webp/hill4.webp" id="hill4" alt="">
            <img src="./files/image_webp/hill5.webp" id="hill5" alt="">       
            <h2 id="text" >MindHeaven Website</h2>
    	</section>
            <div style="padding: 2rem; background: #fff; min-height: 60vh;">
                <section class="comment-container">
            <h1 class="comment-container-title">简介</h1>
            <!-- Card section from card.html -->
                <section class="merged-container">
                <div class="merged-card card1">
                    <div class="merged-content">
                        <h3>🪵树洞</h3>
                        <p>情绪日记支持多形式记录与分类管理;语音AI陪伴具备自然语言理解与情感回应能力;答案之书提供丰富心灵指引</p>
                        <a href="./files/html/mood.html">MORE</a>
                    </div>
                </div>
                <div class="merged-card card2">
                    <div class="merged-content">

                        <h3>🔅放松</h3>
                        <p>冥想模块提供多样主题音频指导;冥想电影模块提供影视推荐 资源丰富;小游戏稳定嵌入且种类多样</p>
                        <a href="./files/html/music.html">MORE</a>
                    </div>
                </div>
                <div class="merged-card card3">
                    <div class="merged-content">

                        <h3>💖爱好</h3>
                        <p>运动、艺术、烹饪板块分别提供详细教程;为爱好培养提供专业化、多样化的教程;让爱好成为你的专属避风港</p>
                        <a href="./files/html/sport.html">MORE</a>
                    </div>
                </div>
                <div class="merged-card card4">
                    <div class="merged-content">

                        <h3>💁帮助</h3>
                        <p>知识科普以多元形式呈现专业知识;心理咨询实现问卷调研、线上预约及专业指导;法律援助引入AI提供法律知识查询与咨询服务</p>
                        <a href="./files/knowle.html">MORE</a>
                    </div>
                </div>
                </section>
                </section>
            </div>
            <div class="combined-container">
            <div class="wave-container">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 1000" width="100%" height="100%">
                    <defs>
                        <linearGradient id="waveGradient1" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#7C93B7" stop-opacity="0.9" />
                            <stop offset="100%" stop-color="#7C93B7" stop-opacity="0.4" />
                        </linearGradient>
                    </defs>
                    
                    <path fill="url(#waveGradient1)" d="M0,600L48,560C96,520,192,540,288,580C384,620,480,660,576,640C672,620,768,580,864,560C960,540,1056,540,1152,560C1248,580,1344,620,1392,640L1440,660L1440,1000L1392,1000C1344,1000,1248,1000,1152,1000C1056,1000,960,1000,864,1000C768,1000,672,1000,576,1000C480,1000,384,1000,288,1000C192,1000,96,1000,48,1000L0,1000Z">
                        <animate attributeName="d" dur="15s" repeatCount="indefinite" values="
                            M0,600L48,560C96,520,192,540,288,580C384,620,480,660,576,640C672,620,768,580,864,560C960,540,1056,540,1152,560C1248,580,1344,620,1392,640L1440,660L1440,1000L1392,1000C1344,1000,1248,1000,1152,1000C1056,1000,960,1000,864,1000C768,1000,672,1000,576,1000C480,1000,384,1000,288,1000C192,1000,96,1000,48,1000L0,1000Z;
                            
                            M0,600L48,640C96,680,192,660,288,620C384,580,480,540,576,560C672,580,768,620,864,640C960,660,1056,660,1152,640C1248,620,1344,580,1392,560L1440,540L1440,1000L1392,1000C1344,1000,1248,1000,1152,1000C1056,1000,960,1000,864,1000C768,1000,672,1000,576,1000C480,1000,384,1000,288,1000C192,1000,96,1000,48,1000L0,1000Z;
                            
                            M0,600L48,580C96,560,192,580,288,620C384,660,480,700,576,680C672,660,768,620,864,600C960,580,1056,580,1152,600C1248,620,1344,660,1392,680L1440,700L1440,1000L1392,1000C1344,1000,1248,1000,1152,1000C1056,1000,960,1000,864,1000C768,1000,672,1000,576,1000C480,1000,384,1000,288,1000C192,1000,96,1000,48,1000L0,1000Z;
                            
                            M0,600L48,620C96,640,192,620,288,580C384,540,480,500,576,520C672,540,768,580,864,600C960,620,1056,620,1152,600C1248,580,1344,540,1392,520L1440,500L1440,1000L1392,1000C1344,1000,1248,1000,1152,1000C1056,1000,960,1000,864,1000C768,1000,672,1000,576,1000C480,1000,384,1000,288,1000C192,1000,96,1000,48,1000L0,1000Z;
                            
                            M0,600L48,560C96,520,192,540,288,580C384,620,480,660,576,640C672,620,768,580,864,560C960,540,1056,540,1152,560C1248,580,1344,620,1392,640L1440,660L1440,1000L1392,1000C1344,1000,1248,1000,1152,1000C1056,1000,960,1000,864,1000C768,1000,672,1000,576,1000C480,1000,384,1000,288,1000C192,1000,96,1000,48,1000L0,1000Z
                        " />
                    </path>
                </svg>
            </div>
                        
                <section class="comment-container">
                    
                    <h1 class="comment-container-title">发帖区</h1>    
                    <?php if ($isLoggedIn): ?>
                        <div class="post-container">
                            <div class="flex space-x-4">
                                <div>
                                    <p class="user-id"><?= $_SESSION['username'] ?></p>
                                </div>
                                <div class="flex space-x-4">
                                    <div class="center-button">
                                        <button id="add-new-post" class="comment-button">
                                            发布帖子
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 帖子列表 -->
                    <div id="posts-container">
                        <?php if (!$isLoggedIn): ?>
                            <div class="post-container">
                                <p>请登录后查看和发表帖子</p>
                            </div>
                        <?php endif; ?>
                    </div>
                                     
                </section>
            
            </div>
            <!-- 发帖弹窗 -->
            <div id="post-modal-overlay" class="modal-overlay">
                <div class="post-modal">
                    <div class="modal-header">
                        <h2 class="modal-title">发表新帖子</h2>
                        <button class="modal-close" id="modal-close-btn">×</button>
                    </div>
                    
                    <form id="new-post-form">
                        <div class="form-group">
                            <label class="form-label">选择您的特征标签</label>
                            <p style="color: #666; font-size: 0.9rem; margin-bottom: 1rem;">请选择一些标签来描述您自己，帮助我们更好地了解您的背景和兴趣。</p>
                            <div class="tags-container" id="tags-container">
                                <!-- 情感状态 -->
                                <div class="tag-group">
                                    <h4 style="width: 100%; color: #4a90e2; font-size: 0.9rem; margin-bottom: 0.5rem;">💙 当前情感状态</h4>
                                    <div class="tag-item" data-tag="感到焦虑">感到焦虑</div>
                                    <div class="tag-item" data-tag="情绪低落">情绪低落</div>
                                    <div class="tag-item" data-tag="内心孤独">内心孤独</div>
                                    <div class="tag-item" data-tag="压力很大">压力很大</div>
                                    <div class="tag-item" data-tag="失眠困扰">失眠困扰</div>
                                    <div class="tag-item" data-tag="情绪不稳">情绪不稳</div>
                                </div>
                                
                                <!-- 生活困扰 -->
                                <div class="tag-group">
                                    <h4 style="width: 100%; color: #4a90e2; font-size: 0.9rem; margin-bottom: 0.5rem;">💔 生活困扰</h4>
                                    <div class="tag-item" data-tag="感情受伤">感情受伤</div>
                                    <div class="tag-item" data-tag="分手痛苦">分手痛苦</div>
                                    <div class="tag-item" data-tag="家庭矛盾">家庭矛盾</div>
                                    <div class="tag-item" data-tag="人际关系">人际关系</div>
                                    <div class="tag-item" data-tag="工作压力">工作压力</div>
                                    <div class="tag-item" data-tag="学业焦虑">学业焦虑</div>
                                    <div class="tag-item" data-tag="经济困难">经济困难</div>
                                </div>
                                
                                <!-- 内心感受 -->
                                <div class="tag-group">
                                    <h4 style="width: 100%; color: #4a90e2; font-size: 0.9rem; margin-bottom: 0.5rem;">🌙 内心感受</h4>
                                    <div class="tag-item" data-tag="缺乏自信">缺乏自信</div>
                                    <div class="tag-item" data-tag="自我怀疑">自我怀疑</div>
                                    <div class="tag-item" data-tag="感到迷茫">感到迷茫</div>
                                    <div class="tag-item" data-tag="内心空虚">内心空虚</div>
                                    <div class="tag-item" data-tag="害怕未来">害怕未来</div>
                                    <div class="tag-item" data-tag="渴望理解">渴望理解</div>
                                </div>
                                
                                <!-- 寻求支持 -->
                                <div class="tag-group">
                                    <h4 style="width: 100%; color: #4a90e2; font-size: 0.9rem; margin-bottom: 0.5rem;">🤝 寻求支持</h4>
                                    <div class="tag-item" data-tag="需要倾听">需要倾听</div>
                                    <div class="tag-item" data-tag="寻找安慰">寻找安慰</div>
                                    <div class="tag-item" data-tag="想要陪伴">想要陪伴</div>
                                    <div class="tag-item" data-tag="需要建议">需要建议</div>
                                    <div class="tag-item" data-tag="寻求鼓励">寻求鼓励</div>
                                    <div class="tag-item" data-tag="想要放松">想要放松</div>
                                </div>
                                
                                <!-- 康复阶段 -->
                                <div class="tag-group">
                                    <h4 style="width: 100%; color: #4a90e2; font-size: 0.9rem; margin-bottom: 0.5rem;">🌱 康复阶段</h4>
                                    <div class="tag-item" data-tag="努力恢复">努力恢复</div>
                                    <div class="tag-item" data-tag="寻找希望">寻找希望</div>
                                    <div class="tag-item" data-tag="学会接纳">学会接纳</div>
                                    <div class="tag-item" data-tag="重建自信">重建自信</div>
                                    <div class="tag-item" data-tag="积极疗愈">积极疗愈</div>
                                    <div class="tag-item" data-tag="感谢成长">感谢成长</div>
                                </div>
                                
                                <!-- 治疗需求 -->
                                <div class="tag-group">
                                    <h4 style="width: 100%; color: #4a90e2; font-size: 0.9rem; margin-bottom: 0.5rem;">🌿 治疗需求</h4>
                                    <div class="tag-item" data-tag="心理咨询">心理咨询</div>
                                    <div class="tag-item" data-tag="情感疏导">情感疏导</div>
                                    <div class="tag-item" data-tag="冥想放松">冥想放松</div>
                                    <div class="tag-item" data-tag="艺术疗法">艺术疗法</div>
                                    <div class="tag-item" data-tag="音乐疗愈">音乐疗愈</div>
                                    <div class="tag-item" data-tag="运动调节">运动调节</div>
                                </div>
                                
                                <!-- 年龄与身份 -->
                                <div class="tag-group">
                                    <h4 style="width: 100%; color: #4a90e2; font-size: 0.9rem; margin-bottom: 0.5rem;">👤 基本信息</h4>
                                    <div class="tag-item" data-tag="青少年">青少年</div>
                                    <div class="tag-item" data-tag="大学生">大学生</div>
                                    <div class="tag-item" data-tag="初入职场">初入职场</div>
                                    <div class="tag-item" data-tag="中年人">中年人</div>
                                    <div class="tag-item" data-tag="单身">单身</div>
                                    <div class="tag-item" data-tag="已婚">已婚</div>
                                    <div class="tag-item" data-tag="父母">父母</div>
                                </div>
                                
                                <!-- 特殊关注 -->
                                <div class="tag-group">
                                    <h4 style="width: 100%; color: #4a90e2; font-size: 0.9rem; margin-bottom: 0.5rem;">⚠️ 特殊关注</h4>
                                    <div class="tag-item" data-tag="抑郁倾向">抑郁倾向</div>
                                    <div class="tag-item" data-tag="焦虑症状">焦虑症状</div>
                                    <div class="tag-item" data-tag="创伤后应激">创伤后应激</div>
                                    <div class="tag-item" data-tag="强迫思维">强迫思维</div>
                                    <div class="tag-item" data-tag="社交恐惧">社交恐惧</div>
                                    <div class="tag-item" data-tag="恐慌发作">恐慌发作</div>
                                </div>
                            </div>
                            <div class="selected-tags" id="selected-tags">
                                <span class="selected-label">已选择标签：</span>
                                <div class="selected-tags-list" id="selected-tags-list"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <span class="required">*</span>内容
                            </label>
                            <textarea id="post-content" class="form-input form-textarea" placeholder="请输入帖子内容..." required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">上传图片</label>
                            <div class="image-upload-area" id="image-upload-area">
                                <div class="upload-icon">📷</div>
                                <div class="upload-text">
                                    <span class="highlight">点击上传</span> 或拖拽图片到此处<br>
                                    支持 JPG、PNG、GIF 格式，大小不超过 5MB
                                </div>
                                <input type="file" id="image-input" class="file-input" accept="image/*">
                            </div>
                            <div class="image-preview" id="image-preview">
                                <img id="preview-image" class="preview-image" alt="预览图片">
                            </div>
                        </div>
                        
                        <div class="modal-actions">
                            <button type="button" class="btn-cancel" id="cancel-btn">取消</button>
                            <button type="submit" class="btn-submit" id="submit-btn">发布</button>
                        </div>
                    </form>
                </div>
            </div>
            
    <script src="./files/js/index.js"></script>
    <script src="./files/js/analysis.js"></script>
    <script src="./files/js/posts.js"></script>
    <script src="./files/js/ajax.js"></script>
        <div class="footer">
            <a href="https://beian.miit.gov.cn"><strong>&copy; 2025 MindHeaven. All rights reserved.     备案号：闽ICP备2025095319号</strong></font></a>
        </div> 
</body>

</html>