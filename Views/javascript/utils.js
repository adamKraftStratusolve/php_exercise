function handleFormSubmit(formId, apiEndpoint, options = {}) {
    const form = document.getElementById(formId);
    if (!form) return;

    const messageDiv = document.getElementById(options.messageId);

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (messageDiv) {
            messageDiv.style.display = 'none';
        }

        if (options.beforeSubmit && !options.beforeSubmit()) {
            return;
        }

        const formData = options.getFormData ? options.getFormData(form) : new FormData(form);

        apiService.post(apiEndpoint, formData)
            .then(data => {
                if (options.onSuccess) {
                    options.onSuccess(data);
                }
            })
            .catch(error => {
                if (messageDiv && error.message !== 'Redirecting to login.') {
                    messageDiv.textContent = error.message;
                    messageDiv.className = 'message error';
                    messageDiv.style.display = 'block';
                }
            });
    });
}

function createPostCard(post, options = {}) {
    console.log('Inspecting post object:', post);

    const postCard = document.createElement('div');
    postCard.className = 'card post-card';
    postCard.setAttribute('data-post-id', post.postId);

    const avatarUrl = (post.profileImageUrl && post.profileImageUrl.trim() !== '')
        ? post.profileImageUrl
        : DEFAULT_AVATAR_BASE64;

    const avatarHTML = `<div class="post-avatar"><img src="${avatarUrl}" alt="${post.username}'s avatar"></div>`;

    let deleteButtonHTML = '';
    if (options.isMyProfile) {
        deleteButtonHTML = `<button class="btn-icon delete-btn" title="Delete Post"><i class="fas fa-times"></i></button>`;
    }

    const likedClass = post.userHasLiked ? 'liked' : '';
    const actionsHTML = `
        <div class="post-actions">
            <button class="btn like-btn ${likedClass}">
                <i class="fas fa-heart"></i> <span class="like-count">${post.likeCount || 0}</span>
            </button>
        </div>`;

    let commentsHTML = '<div class="comments-list"></div>';
    if (post.comments && post.comments.length > 0) {
        const comments = post.comments.map(comment => {
            const commentAvatarUrl = (comment.profileImageUrl && comment.profileImageUrl.trim() !== '')
                ? comment.profileImageUrl
                : DEFAULT_AVATAR_BASE64;
            return `
            <div class="comment">
                <img src="${commentAvatarUrl}" alt="${comment.username}'s avatar" class="comment-avatar">
                <div class="comment-body"><strong>${comment.username}</strong><p>${comment.commentText}</p></div>
            </div>`;
        }).join('');
        commentsHTML = `<div class="comments-list">${comments}</div>`;
    }

    let commentFormHTML = '';
    if (options.showCommentForm !== false) {
        commentFormHTML = `
        <form class="comment-form">
            <div class="input-wrapper">
                <input type="text" name="commentText" class="comment-input" placeholder="Write a comment..." required>
                <span class="char-counter">0 / 180</span>
            </div>
            <input type="hidden" name="postId" value="${post.postId}">
            <button type="submit" class="btn btn-sm">Post</button>
        </form>`;
    }

    const postContentHTML = `
        <div class="post-content">
            <div class="post-header">
                <div class="user-info">${post.firstName || ''} ${post.lastName || ''} <span>@${post.username}</span></div>
                ${deleteButtonHTML} 
            </div>
            <p class="post-body">${post.postText}</p>
            ${actionsHTML}
            ${commentsHTML}
            ${commentFormHTML} 
        </div>
    `;

    postCard.innerHTML = avatarHTML + postContentHTML;
    return postCard;
}