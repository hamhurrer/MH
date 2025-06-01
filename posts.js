let currentUserId = null;
var oinputs = document.getElementsByTagName("input");
// 加载帖子
function loadPosts() {
    $ajax({
        url: './files/post_api.php',
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
        const postHtml = `
            <div class="post-container" data-post-id="${post.id}">
                <div class="flex space-x-4">
                    <div class="flex space-x-4">
                        <img src="./files/image/bg.jpg" width="100" height="100">
                        <div  class="fas fa-user"></div>
                        <p class="user-id">${post.username}</p>
                    </div>
                    <div class="flex-1">
                        <p class="post-content">${post.content}</p>
                        ${isOwnPost ? 
                            '<button class="delete-button" onclick="deletePost(' + post.id + ')">删除</button>' : 
                            '' 
                          }
                        <span class="text-blue-500 cursor-pointer" onclick="toggleCommentSection(${post.id}) ">评论</span>
                        <div class="comment-section" id="comment-section-${post.id}" style="display: none;">
                            <div class="mt-4">
                                <input id="comment-input-${post.id}" class="comment-input" placeholder="写下你的评论...">
                                <button onclick="addComment(${post.id})" class="comment-button">添加评论</button>
                            </div>
                            <div id="comment-list-${post.id}" class="comment-list">
                                ${renderComments(post.comments)}
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
                
                    <span>${comment.username}: ${comment.content}</span>
                
                ${isOwnComment ? 
                    '<button class="delete-button" onclick="deleteComment(' + comment.id + ')">删除</button>' : 
                    '' 
                  }
            </div>
        `;
    }).join('');
}

// 添加新帖子
document.querySelector('#add-new-post').onclick = (event) =>{
   const content = oinputs[8].value;
   var time = new Date();
    $ajax({
        url: './files/post_api.php',
        method : "post",
        data: {
            action: 'add_post',
            content: content,
            create_time : time.getTime()//获取到毫秒数
        },
        success: function(response) {
            var res = JSON.parse(response);
            if (res.status === 'success') {
                 $('#new-post-content').val('');
                loadPosts();
            }else{
                alert(res.message);
            }
        }
    });
};

// 添加评论
function addComment(postId) {
    const content = $(`#comment-input-${postId}`).val();
    var time = new Date();
    $ajax({
        url: './files/post_api.php',
        method : "post",
        data: {
            action: 'add_comment',
            post_id: postId,
            content: content,
            create_time : time.getTime()//获取到毫秒数
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
    //if (!confirm('确定要删除这个帖子吗？')) return;
    
    $ajax({
        url: './files/post_api.php',
        method : "post",
        data: {
            action: 'delete_post',
            post_id: postId,
            //csrf_token: $('#csrf-token').val()
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
    //if (!confirm('确定要删除这个评论吗？')) return;
    
    $ajax({
        url: './files/post_api.php',
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
            url: './files/post_api.php',
            method: 'post',
            data: { action: 'get_current_user' },
            success: function(response) {
                var res = JSON.parse(response);
                if (res.status === 'success') {
                    currentUserId = res.user_id;
                    loadPosts();
                }
            }
        });}else{
        loadPosts();
    }
});