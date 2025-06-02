let currentUserId = null;
var oinputs = document.getElementsByTagName("input");

// 加载帖子
function loadPosts() {
    $ajax({
        url: './files/php/post_api.php',
        method: 'post',
        data: {
            action: 'get_posts',
        },
        success: function(response) {
            var res = JSON.parse(response);
            if (res.status === 'success') {
                renderPosts(res.posts);
            }else{
                alert(res.message);
            }
        }
    });
}

// 渲染帖子列表
function renderPosts(posts) {
    const container = $('#posts-container');
    container.empty();
    
    posts.forEach(post => {
        const isOwnPost = post.user_id == currentUserId;
        // 处理帖子图片，如果没有图片则使用默认图片
        const postImage ="./files/"+ post.post_image ?"./files/"+ post.post_image : './files/image_webp/bg.webp';
                // 处理标签显示
        let tagsHtml = '';
        if (post.tags && post.tags.trim()) {
            const tags = post.tags.split(',').filter(tag => tag.trim());
            if (tags.length > 0) {
                tagsHtml = '<div class="post-tags">' + 
                    tags.map(tag => `<span class="post-tag">${tag.trim()}</span>`).join('') + 
                    '</div>';
            }
        }
        const postHtml = `
            <div class="post-container" data-post-id="${post.id}">
                <div class="flex space-x-4">
                    <div>
                        <img src="${postImage}" class="user-avatar" alt="Post Image" onerror="this.src='./files/image_webp/bg.webp'">
                    </div>
                    <div class="flex-1">
                        <p class="user-id">${post.username}</p>
                        ${tagsHtml} 
                        <p class="post-content">${post.content}</p>                        
                        ${isOwnPost ? 
                            '<button class="delete-button" onclick="deletePost(' + post.id + ')">删除</button>' : 
                            '' 
                          }
                        <span class="text-blue-500 cursor-pointer" onclick="toggleCommentSection(${post.id}) ">💬评论</span>
                        <div class="comment-section" id="comment-section-${post.id}" style="display: none;">
                            <div id="comment-list-${post.id}" class="comment-list">
                                ${renderComments(post.comments)}
                            </div>
                            <div class="mt-4">
                                <input id="comment-input-${post.id}" class="comment-input" placeholder="写下你的评论...">
                                <button onclick="addComment(${post.id})" class="comment-button">添加评论</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.append(postHtml);
    });
}

// 渲染评论列表
function renderComments(comments) {
    return comments.map(comment => {
        const isOwnComment = comment.user_id == currentUserId;
        return `
            <div class="comment-item" data-comment-id="${comment.id}">
                <div>
                    <img src="./files/image_webp/bg.webp" width="32" height="32" alt="User Avatar">
                    <span>${comment.username}: ${comment.content}</span>
                </div>
                ${isOwnComment ? 
                    '<button class="delete-button" onclick="deleteComment(' + comment.id + ')">删除</button>' : 
                    '' 
                  }
            </div>
        `;
    }).join('');
}

// 弹窗相关变量和函数
let modalOverlay = null;
let modalCloseBtn = null;
let cancelBtn = null;
let newPostForm = null;
let imageUploadArea = null;
let imageInput = null;
let imagePreview = null;
let previewImage = null;
let selectedTags = []; // 存储选中的标签

// 初始化弹窗功能
function initModal() {
    modalOverlay = document.getElementById('post-modal-overlay');
    modalCloseBtn = document.getElementById('modal-close-btn');
    cancelBtn = document.getElementById('cancel-btn');
    newPostForm = document.getElementById('new-post-form');
    imageUploadArea = document.getElementById('image-upload-area');
    imageInput = document.getElementById('image-input');
    imagePreview = document.getElementById('image-preview');
    previewImage = document.getElementById('preview-image');

    // 如果弹窗元素不存在，则不初始化
    if (!modalOverlay) return;

    // 初始化标签选择功能
    initTagSelection();

    // 事件监听器
    if (modalCloseBtn) modalCloseBtn.addEventListener('click', hideModal);
    if (cancelBtn) cancelBtn.addEventListener('click', hideModal);

    // 点击遮罩关闭弹窗
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            hideModal();
        }
    });

    // ESC键关闭弹窗
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalOverlay.classList.contains('show')) {
            hideModal();
        }
    });

    // 图片上传功能
    if (imageUploadArea && imageInput) {
        imageUploadArea.addEventListener('click', function() {
            imageInput.click();
        });

        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                handleImageFile(file);
            }
        });

        // 拖拽上传
        imageUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            imageUploadArea.classList.add('dragover');
        });

        imageUploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            imageUploadArea.classList.remove('dragover');
        });

        imageUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            imageUploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    handleImageFile(file);
                }
            }
        });
    }

                // 表单提交
    if (newPostForm) {
        newPostForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const content = document.getElementById('post-content').value.trim();
            
            if (!content) {
                alert('请填写内容');
                return;
            }

            // 创建FormData对象来处理文件上传
            const formData = new FormData();
            formData.append('action', 'add_post');
            formData.append('content', content);
            formData.append('create_time', new Date().getTime());
            
            // 添加选中的标签
            if (selectedTags.length > 0) {
                formData.append('tags', selectedTags.join(','));
            }
            
            // 如果有选择图片，添加到FormData中
            const imageFile = imageInput.files[0];
            if (imageFile) {
                formData.append('post_image', imageFile);
            }

            // 使用原生XMLHttpRequest来支持FormData
            const xhr = new XMLHttpRequest();
            xhr.open('POST', './files/php/post_api.php', true);
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.status === 'success') {
                            hideModal();
                            loadPosts();
                            alert('帖子发布成功！');
                        } else {
                            alert(res.message);
                        }
                    } catch (error) {
                        alert('发布失败，请重试');
                    }
                }
            };
            
            xhr.send(formData);
        });
    }
}

// 初始化标签选择功能
function initTagSelection() {
    const tagItems = document.querySelectorAll('.tag-item');
    const selectedTagsList = document.getElementById('selected-tags-list');
    
    if (!tagItems.length || !selectedTagsList) return;
    
    tagItems.forEach(item => {
        item.addEventListener('click', function() {
            const tag = this.getAttribute('data-tag');
            
            if (this.classList.contains('selected')) {
                // 取消选择
                this.classList.remove('selected');
                selectedTags = selectedTags.filter(t => t !== tag);
            } else {
                // 限制最多选择8个标签
                if (selectedTags.length >= 8) {
                    alert('最多只能选择8个标签');
                    return;
                }
                // 选择标签
                this.classList.add('selected');
                selectedTags.push(tag);
            }
            
            updateSelectedTagsDisplay();
        });
    });
}

// 更新已选择标签的显示
function updateSelectedTagsDisplay() {
    const selectedTagsList = document.getElementById('selected-tags-list');
    if (!selectedTagsList) return;
    
    selectedTagsList.innerHTML = '';
    
    selectedTags.forEach(tag => {
        const tagElement = document.createElement('span');
        tagElement.className = 'selected-tag';
        tagElement.innerHTML = `
            ${tag}
            <span class="remove-tag" onclick="removeTag('${tag}')">×</span>
        `;
        selectedTagsList.appendChild(tagElement);
    });
}

// 移除标签
function removeTag(tag) {
    selectedTags = selectedTags.filter(t => t !== tag);
    
    // 更新UI中的选择状态
    const tagItem = document.querySelector(`.tag-item[data-tag="${tag}"]`);
    if (tagItem) {
        tagItem.classList.remove('selected');
    }
    
    updateSelectedTagsDisplay();
}

// 显示弹窗
function showModal() {
    if (modalOverlay) {
        modalOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

// 隐藏弹窗
function hideModal() {
    if (modalOverlay) {
        modalOverlay.classList.remove('show');
        document.body.style.overflow = 'auto';
        resetForm();
    }
}

// 重置表单
function resetForm() {
    if (newPostForm) {
        newPostForm.reset();
    }
    if (imagePreview) {
        imagePreview.style.display = 'none';
    }
    if (imageUploadArea) {
        imageUploadArea.classList.remove('dragover');
    }
    
    // 重置标签选择
    selectedTags = [];
    document.querySelectorAll('.tag-item.selected').forEach(item => {
        item.classList.remove('selected');
    });
    const selectedTagsList = document.getElementById('selected-tags-list');
    if (selectedTagsList) {
        selectedTagsList.innerHTML = '';
    }
}

// 处理图片文件
function handleImageFile(file) {
    // 检查文件大小 (5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('图片大小不能超过 5MB');
        return;
    }

    // 显示预览
    if (previewImage && imagePreview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

// 修改原有的添加新帖子功能
function setupAddNewPostButton() {
    const addNewPostBtn = document.querySelector('#add-new-post');
    if (addNewPostBtn) {
        // 移除原有的onclick事件处理器
        addNewPostBtn.onclick = null;
        
        // 添加新的事件监听器来显示弹窗
        addNewPostBtn.addEventListener('click', function(event) {
            event.preventDefault();
            showModal();
        });
    }
}

// 添加评论
function addComment(postId) {
    const content = $(`#comment-input-${postId}`).val();
    var time = new Date();
    $ajax({
        url: './files/php/post_api.php',
        method : "post",
        data: {
            action: 'add_comment',
            post_id: postId,
            content: content,
            create_time : time.getTime()
        },
        success: function(response) {
            var res = JSON.parse(response);
            if (res.status === 'success') {
                $(`#comment-input-${postId}`).val('');
                loadPosts();
                alert('添加成功');
            }else{
                alert(res.message);
            }
        }
    });
}

// 删除帖子
function deletePost(postId) {
    $ajax({
        url: './files/php/post_api.php',
        method : "post",
        data: {
            action: 'delete_post',
            post_id: postId,
        },
        success: function(response) {
            var res = JSON.parse(response);
            if (res.status === 'success') {
                loadPosts();
            }
        }
    });
}

// 删除评论
function deleteComment(commentId) {
    $ajax({
        url: './files/php/post_api.php',
        method : "post",
        data: {
            action: 'delete_comment',
            comment_id: commentId,
            csrf_token: $('#csrf-token').val()
        },
        success: function(response) {
            var res = JSON.parse(response);
            if (res.status === 'success') {
                loadPosts();
            }
        }
    });
}

function toggleCommentSection(postId) {
    const commentSection = document.getElementById(`comment-section-${postId}`);
    if (commentSection) {
        commentSection.style.display = commentSection.style.display === 'none' ? 'block' : 'none';
    }
}

//初始化加载帖子
$(document).ready(function() {
    if(!currentUserId){
        $ajax({
            url: './files/php/post_api.php',
            method: 'post',
            data: { action: 'get_current_user' },
            success: function(response) {
                var res = JSON.parse(response);
                if (res.status === 'success') {
                    currentUserId = res.user_id;
                    loadPosts();
                }
            }
        });
    } else {
        loadPosts();
    }
    
    // 初始化弹窗功能
    initModal();
    
    // 设置发布帖子按钮
    setupAddNewPostButton();
});
