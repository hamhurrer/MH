// 心理分析功能 - 在index.php中的<script>标签内使用
document.addEventListener('DOMContentLoaded', function() {
    const analysisBtn = document.getElementById('analysis-btn');
    const analysisModal = document.getElementById('analysis-modal');
    const analysisModalClose = document.getElementById('analysis-modal-close');
    const analysisLoading = document.getElementById('analysis-loading');
    const analysisResult = document.getElementById('analysis-result');
    const analysisError = document.getElementById('analysis-error');
    const retryAnalysisBtn = document.getElementById('retry-analysis');
    const analysisScoreValue = document.getElementById('analysis-score-value');
    const analysisContentText = document.getElementById('analysis-content-text');
    const errorMessage = document.getElementById('error-message');

    // 打开心理分析弹窗
    if (analysisBtn) {
        analysisBtn.addEventListener('click', function() {
            analysisModal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // 禁止背景滚动
            startAnalysis();
        });
    }

    // 关闭心理分析弹窗
    function closeAnalysisModal() {
        analysisModal.style.display = 'none';
        document.body.style.overflow = 'auto'; // 恢复背景滚动
        resetAnalysisModal();
    }

    // 重置弹窗状态
    function resetAnalysisModal() {
        analysisLoading.style.display = 'block';
        analysisResult.style.display = 'none';
        analysisError.style.display = 'none';
        if (analysisBtn) {
            analysisBtn.disabled = false;
            analysisBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    // 关闭按钮事件
    if (analysisModalClose) {
        analysisModalClose.addEventListener('click', closeAnalysisModal);
    }

    // 点击背景关闭弹窗
    analysisModal.addEventListener('click', function(e) {
        if (e.target === analysisModal) {
            closeAnalysisModal();
        }
    });

    // ESC键关闭弹窗
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && analysisModal.style.display === 'block') {
            closeAnalysisModal();
        }
    });

    // 重试按钮事件
    if (retryAnalysisBtn) {
        retryAnalysisBtn.addEventListener('click', function() {
            resetAnalysisModal();
            startAnalysis();
        });
    }

    // 开始心理分析
    function startAnalysis() {
        // 重置状态
        analysisLoading.style.display = 'block';
        analysisResult.style.display = 'none';
        analysisError.style.display = 'none';
        
        if (analysisBtn) {
            analysisBtn.disabled = true;
            analysisBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        // 创建表单数据
        const formData = new FormData();
        formData.append('action', 'get_assessment');

        // 发送请求到 assessment.php
        fetch('./files/php/assessment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('心理分析响应状态:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP错误，状态码: ${response.status}`);
            }
            
            return response.text().then(text => {
                try {
                    if (text.trim() === '') {
                        throw new Error('服务器返回空内容');
                    }
                    
                    const data = JSON.parse(text);
                    return data;
                } catch (err) {
                    console.error('服务器返回非JSON格式:', text);
                    
                    // 尝试从HTML错误页面中提取错误信息
                    const errorMatch = text.match(/<title>(.*?)<\/title>/i);
                    const errorMsg = errorMatch ? errorMatch[1] : '服务器返回格式错误';
                    
                    throw new Error(errorMsg);
                }
            });
        })
        .then(data => {
            console.log('心理分析数据:', data);
            
            analysisLoading.style.display = 'none';
            
            if (analysisBtn) {
                analysisBtn.disabled = false;
                analysisBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
            
            if (data.status === 'error') {
                showAnalysisError(data.message, data.debug_info);
            } else if (data.status === 'success') {
                showAnalysisResult(data);
            } else {
                showAnalysisError('系统返回格式异常', data);
            }
        })
        .catch(error => {
            console.error('心理分析错误:', error);
            
            analysisLoading.style.display = 'none';
            
            if (analysisBtn) {
                analysisBtn.disabled = false;
                analysisBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
            
            showAnalysisError('请求失败: ' + error.message);
        });
    }

    // 显示分析结果
    function showAnalysisResult(data) {
        // 设置评分
        if (analysisScoreValue) {
            analysisScoreValue.textContent = data.score || 'AI分析完成';
        }

        // 构建分析内容
        let contentHtml = '';

        // 显示用户数据统计
        if (data.debug_info && data.debug_info.user_data_summary) {
            const userStats = data.debug_info.user_data_summary;
            contentHtml += `
                <div class="mb-6 p-4 bg-purple-50 border border-purple-200 rounded-xl">
                    <h5 class="font-semibold text-purple-800 mb-3 flex items-center">
                        <i class="fas fa-chart-bar mr-2"></i>数据统计
                    </h5>
                    <div class="grid grid-cols-3 gap-4 text-sm mb-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">${userStats.comments_count}</div>
                            <div class="text-purple-700">评论数</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">${userStats.moods_count}</div>
                            <div class="text-purple-700">情绪记录</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">${userStats.posts_count}</div>
                            <div class="text-purple-700">帖子数</div>
                        </div>
                    </div>
                    
                    <!-- 评论内容展开 -->
                    ${userStats.comments && userStats.comments.length > 0 ? `
                        <details class="mb-3">
                            <summary class="cursor-pointer font-medium text-purple-800 hover:text-purple-900 flex items-center py-2 px-3 bg-white rounded-lg border border-purple-200 hover:bg-purple-25 transition-colors">
                                <i class="fas fa-comments mr-2"></i>
                                查看评论内容 (${userStats.comments.length}条)
                                <i class="fas fa-chevron-down ml-auto transform transition-transform"></i>
                            </summary>
                            <div class="mt-3 space-y-2 max-h-60 overflow-y-auto">
                                ${userStats.comments.map((comment, index) => `
                                    <div class="bg-white p-3 rounded-lg border border-purple-150 shadow-sm">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-xs text-purple-600 font-medium">评论 #${index + 1}</span>
                                        </div>
                                        <div class="text-gray-700 text-sm leading-relaxed">${escapeHtml(comment)}</div>
                                    </div>
                                `).join('')}
                            </div>
                        </details>
                    ` : ''}
                    
                    <!-- 情绪记录展开 -->
                    ${userStats.moods && userStats.moods.length > 0 ? `
                        <details class="mb-3">
                            <summary class="cursor-pointer font-medium text-purple-800 hover:text-purple-900 flex items-center py-2 px-3 bg-white rounded-lg border border-purple-200 hover:bg-purple-25 transition-colors">
                                <i class="fas fa-heart mr-2"></i>
                                查看情绪记录 (${userStats.moods.length}条)
                                <i class="fas fa-chevron-down ml-auto transform transition-transform"></i>
                            </summary>
                            <div class="mt-3 space-y-2 max-h-60 overflow-y-auto">
                                ${userStats.moods.map((mood, index) => `
                                    <div class="bg-white p-3 rounded-lg border border-purple-150 shadow-sm">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-xs text-purple-600 font-medium">记录 #${index + 1}</span>
                                            <span class="text-xs px-2 py-1 rounded-full ${getMoodColor(mood.type)} font-medium">${mood.type}</span>
                                        </div>
                                        <div class="text-gray-700 text-sm leading-relaxed">${escapeHtml(mood.description)}</div>
                                    </div>
                                `).join('')}
                            </div>
                        </details>
                    ` : ''}
                    
                    <!-- 帖子内容展开 -->
                    ${userStats.posts && userStats.posts.length > 0 ? `
                        <details class="mb-3">
                            <summary class="cursor-pointer font-medium text-purple-800 hover:text-purple-900 flex items-center py-2 px-3 bg-white rounded-lg border border-purple-200 hover:bg-purple-25 transition-colors">
                                <i class="fas fa-file-alt mr-2"></i>
                                查看帖子内容 (${userStats.posts.length}条)
                                <i class="fas fa-chevron-down ml-auto transform transition-transform"></i>
                            </summary>
                            <div class="mt-3 space-y-2 max-h-60 overflow-y-auto">
                                ${userStats.posts.map((post, index) => `
                                    <div class="bg-white p-3 rounded-lg border border-purple-150 shadow-sm">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-xs text-purple-600 font-medium">帖子 #${index + 1}</span>
                                            ${post.tags ? `<span class="text-xs text-gray-500">标签: ${post.tags}</span>` : ''}
                                        </div>
                                        <div class="text-gray-700 text-sm leading-relaxed">${escapeHtml(post.content)}</div>
                                        ${post.tags ? `
                                            <div class="mt-2 flex flex-wrap gap-1">
                                                ${post.tags.split(',').map(tag => `
                                                    <span class="text-xs px-2 py-1 bg-purple-100 text-purple-700 rounded-full">${tag.trim()}</span>
                                                `).join('')}
                                            </div>
                                        ` : ''}
                                    </div>
                                `).join('')}
                            </div>
                        </details>
                    ` : ''}
                </div>
            `;
        }

        // 显示AI分析结果
        if (data.analysis && data.analysis !== '未找到有效内容') {
            contentHtml += `
                <div class="mb-6">
                    <h5 class="font-semibold text-blue-800 mb-3 flex items-center">
                        <i class="fas fa-brain mr-2"></i>AI心理分析
                    </h5>
                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-200">
                        <div class="text-gray-700 leading-relaxed whitespace-pre-wrap">${formatAnalysisText(data.analysis)}</div>
                    </div>
                </div>
            `;
        } else {
            contentHtml += `
                <div class="mb-6">
                    <h5 class="font-semibold text-orange-800 mb-3 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>分析状态
                    </h5>
                    <div class="bg-orange-50 p-4 rounded-xl border border-orange-200">
                        <p class="text-orange-700">AI已响应，但未能提取到有效的分析内容。这可能是由于API响应格式的问题。</p>
                    </div>
                </div>
            `;
        }

        // 显示建议
        contentHtml += `
            <div class="mb-6">
                <h5 class="font-semibold text-green-800 mb-3 flex items-center">
                    <i class="fas fa-lightbulb mr-2"></i>使用建议
                </h5>
                <div class="bg-green-50 p-4 rounded-xl border border-green-200">
                    <ul class="text-green-700 space-y-2">
                        <li>• 定期记录情绪日记，帮助更好地了解自己的心理状态</li>
                        <li>• 积极参与社区讨论，分享和交流有助于心理健康</li>
                        <li>• 如需专业帮助，建议咨询专业心理咨询师</li>
                        <li>• 保持规律的作息和适度的运动有助于改善心情</li>
                    </ul>
                </div>
            </div>
        `;

        if (analysisContentText) {
            analysisContentText.innerHTML = contentHtml;
        }

        analysisResult.style.display = 'block';
    }

    // 显示分析错误
    function showAnalysisError(message, debugInfo = null) {
        if (errorMessage) {
            let errorHtml = `<div class="text-red-600 font-medium mb-3">${message}</div>`;
            
            // 如果有调试信息，显示简化的错误详情
            if (debugInfo) {
                errorHtml += `
                    <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <h5 class="font-medium text-red-800 mb-2">错误详情:</h5>
                        <div class="text-sm text-red-700 space-y-1">
                `;
                
                if (debugInfo.http_code) {
                    errorHtml += `<div>• HTTP状态码: ${debugInfo.http_code}</div>`;
                }
                
                if (debugInfo.curl_error) {
                    errorHtml += `<div>• 网络错误: ${debugInfo.curl_error}</div>`;
                }
                
                if (debugInfo.user_data_summary) {
                    const stats = debugInfo.user_data_summary;
                    errorHtml += `
                        <div>• 数据统计: 评论${stats.comments_count}条，情绪记录${stats.moods_count}条，帖子${stats.posts_count}条</div>
                    `;
                }
                
                errorHtml += `
                        </div>
                    </div>
                `;
            }
            
            errorMessage.innerHTML = errorHtml;
        }
        
        analysisError.style.display = 'block';
    }

    // 格式化分析文本
    function formatAnalysisText(text) {
        // 简单的文本格式化，保持换行和基本结构
        return text
            .replace(/\n\n/g, '</p><p>')
            .replace(/\n/g, '<br>')
            .replace(/^/, '<p>')
            .replace(/$/, '</p>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>');
    }

    // HTML转义函数
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // 获取情绪类型对应的颜色样式
    function getMoodColor(type) {
        const moodColors = {
            '开心': 'bg-green-100 text-green-800',
            '愉快': 'bg-green-100 text-green-800',
            '高兴': 'bg-green-100 text-green-800',
            '快乐': 'bg-green-100 text-green-800',
            '兴奋': 'bg-yellow-100 text-yellow-800',
            '平静': 'bg-blue-100 text-blue-800',
            '放松': 'bg-blue-100 text-blue-800',
            '满足': 'bg-indigo-100 text-indigo-800',
            '悲伤': 'bg-gray-100 text-gray-800',
            '难过': 'bg-gray-100 text-gray-800',
            '失落': 'bg-gray-100 text-gray-800',
            '沮丧': 'bg-red-100 text-red-800',
            '愤怒': 'bg-red-100 text-red-800',
            '生气': 'bg-red-100 text-red-800',
            '焦虑': 'bg-orange-100 text-orange-800',
            '紧张': 'bg-orange-100 text-orange-800',
            '担心': 'bg-orange-100 text-orange-800',
            '害怕': 'bg-purple-100 text-purple-800',
            '恐惧': 'bg-purple-100 text-purple-800',
            '困惑': 'bg-pink-100 text-pink-800',
            '疲惫': 'bg-gray-100 text-gray-800',
            '无聊': 'bg-gray-100 text-gray-800'
        };
        
        return moodColors[type] || 'bg-gray-100 text-gray-800';
    }

    // 添加details展开/收起动画
    document.addEventListener('click', function(e) {
        if (e.target.tagName === 'SUMMARY') {
            const details = e.target.parentElement;
            const icon = e.target.querySelector('.fa-chevron-down');
            
            setTimeout(() => {
                if (details.open) {
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    icon.style.transform = 'rotate(0deg)';
                }
            }, 10);
        }
    });
});