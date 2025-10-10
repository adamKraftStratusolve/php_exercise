class IndexPage extends BasePage {
    constructor() {
        super();
        this.postsContainer = document.getElementById('postsContainer');
        this.createPostTrigger = document.getElementById('createPostTrigger');
        this.postModal = document.getElementById('postModal');
        this.closeModalBtn = document.getElementById('modalCloseBtn');
        this.cancelModalBtn = document.getElementById('modalCancelBtn');
        this.postTextarea = document.getElementById('postTextarea');
        this.currentPage = 1;
        this.isLoading = false;
        this.loadingNotification = document.getElementById('loadingNotification');
        this.postCharCounter = document.getElementById('postCharCounter');
    }

    init() {
        super.init();
        this._addPageEventListeners();

        this._fetchAllPosts();

        setInterval(() => this._syncFeed(), 7000);
    }

    onProfileLoad(user) {
        if (this.createPostTrigger && user.firstName) {
            this.createPostTrigger.textContent = `What's on your mind, ${user.firstName}?`;
        }
    }

    _fetchAllPosts() {
        apiService.get('/indexBackend.php')
            .then(posts => {
                this.postsContainer.innerHTML = '';
                if (Array.isArray(posts)) {
                    posts.forEach(post => {
                        const postCard = createPostCard(post);
                        this.postsContainer.append(postCard);
                    });
                }
            })
            .catch(error => {
                if (error.message !== 'Redirecting to login.' && this.postsContainer) {
                    this.postsContainer.innerHTML = `<p class="error">Could not load posts. ${error.message}</p>`;
                }
            });
    }

    _syncFeed() {
        const postElements = this.postsContainer.querySelectorAll('.post-card');
        if (postElements.length === 0) return;

        const existingIds = Array.from(postElements).map(post => post.dataset.postId);
        const latestId = existingIds[0];

        apiService.post('/Services/sync_feed.php', { sinceId: latestId, existingIds: existingIds })
            .then(data => {
                if (data.newPosts && data.newPosts.length > 0) {
                    data.newPosts.reverse().forEach(post => {
                        const postCard = createPostCard(post);
                        this.postsContainer.prepend(postCard);
                    });
                }

                if (data.updates) {
                    for (const postId in data.updates) {
                        this.updatePostCard(postId, data.updates[postId]);
                    }
                }
            })
            .catch(error => console.error("Error syncing feed:", error));
    }

    updatePostCard(postId, postData) {
        const postCard = this.postsContainer.querySelector(`.post-card[data-post-id="${postId}"]`);
        if (!postCard) return;

        const commentInput = postCard.querySelector('.comment-input');
        if (document.activeElement === commentInput) {
            console.log(`Skipping update for post ${postId} because user is typing.`);
            return; // Exit the function to prevent DOM changes
        }

        const likeBtn = postCard.querySelector('.like-btn');
        const likeCountSpan = postCard.querySelector('.like-count');
        if (likeBtn && likeCountSpan) {
            likeCountSpan.textContent = postData.likeCount || 0;
            postData.userHasLiked ? likeBtn.classList.add('liked') : likeBtn.classList.remove('liked');
        }

        const commentsList = postCard.querySelector('.comments-list');
        if (commentsList) {
            const serverComments = postData.comments || [];
            const clientCommentCount = commentsList.children.length;

            if (serverComments.length > clientCommentCount) {
                const newComments = serverComments.slice(clientCommentCount);
                newComments.forEach(comment => {
                    const commentAvatarUrl = (comment.profileImageUrl && comment.profileImageUrl.trim() !== '') ? comment.profileImageUrl : DEFAULT_AVATAR_BASE64;
                    const newCommentHTML = `
                <div class="comment">
                    <img src="${commentAvatarUrl}" alt="${comment.username}'s avatar" class="comment-avatar">
                    <div class="comment-body"><strong>${comment.username}</strong><p>${comment.commentText}</p></div>
                </div>`;
                    commentsList.insertAdjacentHTML('beforeend', newCommentHTML);
                });
            }
        }
    }

    _addPageEventListeners() {
        this.createPostTrigger.addEventListener('click', () => this.postModal.style.display = 'flex');
        this.closeModalBtn.addEventListener('click', () => this.postModal.style.display = 'none');
        this.cancelModalBtn.addEventListener('click', () => this.postModal.style.display = 'none');

        const postCharCounter = document.getElementById('postCharCounter');
        if (postCharCounter && this.postTextarea) {
            this.postTextarea.addEventListener('input', () => {
                const count = this.postTextarea.value.length;
                postCharCounter.textContent = `${count} / 280`;
            });
        }

        this.postsContainer.addEventListener('input', (event) => {
            if (event.target.classList.contains('comment-input')) {
                const input = event.target;
                const form = input.closest('.comment-form');
                const counter = form.querySelector('.char-counter');
                const count = input.value.length;
                counter.textContent = `${count} / 180`;
            }
        });

        handleFormSubmit('createPostForm', '/Services/create_post.php', {

            messageId: 'modalErrorMessage',
            beforeSubmit: () => {
                const postText = this.postTextarea.value;
                if (postText.length > 280) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Character Limit Exceeded',
                        text: 'Your post cannot exceed 280 characters.',
                    });
                    return false;
                }
                return true;
            },

            onSuccess: (newPost) => {
                this.postModal.style.display = 'none';
                this.postTextarea.value = '';
                this.postCharCounter.textContent = '0 / 280';
                const postCard = createPostCard(newPost);
                this.postsContainer.prepend(postCard);
            },
        });

        this.postsContainer.addEventListener('click', (event) => this._handlePostClick(event));
        this.postsContainer.addEventListener('submit', (event) => this._handleCommentSubmit(event));
        window.addEventListener('scroll', () => this._handleScroll());
    }

    _handlePostClick(event) {
        const likeBtn = event.target.closest('.like-btn');
        if (likeBtn) {
            const postCard = likeBtn.closest('.post-card');
            const postId = postCard.dataset.postId;
            const likeCountSpan = likeBtn.querySelector('.like-count');
            const currentCount = parseInt(likeCountSpan.textContent);

            likeBtn.classList.toggle('liked');
            likeCountSpan.textContent = likeBtn.classList.contains('liked') ? currentCount + 1 : currentCount - 1;

            const formData = new FormData();
            formData.append('postId', postId);
            apiService.post('/Services/toggle_like.php', formData).catch(err => console.error("Toggle like failed:", err));
        }
    }

    _handleCommentSubmit(event) {
        const commentForm = event.target.closest('.comment-form');
        if (commentForm) {
            event.preventDefault();
            const commentInput = commentForm.querySelector('.comment-input');
            const formData = new FormData(commentForm);
            const postCard = commentForm.closest('.post-card');
            const commentsList = postCard.querySelector('.comments-list');

            commentInput.disabled = true;

            apiService.post('/Services/post_comment.php', formData)
                .then(response => {
                    const newComment = response.success;
                    commentInput.value = '';

                    const charCounter = commentForm.querySelector('.char-counter');
                    if (charCounter) {
                        charCounter.textContent = '0 / 180';
                    }

                    const commentAvatarUrl = (newComment.profileImageUrl && newComment.profileImageUrl.trim() !== '') ? newComment.profileImageUrl : DEFAULT_AVATAR_BASE64;
                    const newCommentHTML = `
                        <div class="comment">
                            <img src="${commentAvatarUrl}" alt="${newComment.username}'s avatar" class="comment-avatar">
                            <div class="comment-body"><strong>${newComment.username}</strong><p>${newComment.commentText}</p></div>
                        </div>`;

                    commentsList.insertAdjacentHTML('beforeend', newCommentHTML);
                })
                .catch(error => {
                    if (error.message !== 'Redirecting to login.') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: error.message,
                        });
                    }
                })
                .finally(() => {
                    commentInput.disabled = false;
                    commentInput.focus();
                });
        }
    }

    _handleScroll() {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 500 && !this.isLoading) {
            this._loadMorePosts();
        }
    }

    _loadMorePosts() {
        this.isLoading = true;
        this.currentPage++;

        if (this.loadingNotification) {
            this.loadingNotification.classList.add('show');
        }

        apiService.get(`/indexBackend.php?page=${this.currentPage}`)
            .then(posts => {
                if (this.loadingNotification) {
                    this.loadingNotification.classList.remove('show');
                }

                if (Array.isArray(posts) && posts.length > 0) {
                    posts.forEach(post => {
                        const postCard = createPostCard(post);
                        this.postsContainer.append(postCard);
                    });
                    this.isLoading = false;
                } else {
                    console.log("No more posts to load.");
                }
            })
            .catch(error => {
                console.error("Error loading more posts:", error);

                if (this.loadingNotification) {
                    this.loadingNotification.textContent = 'Could not load more posts.';
                    setTimeout(() => {
                        this.loadingNotification.classList.remove('show');
                        this.loadingNotification.textContent = 'Loading more posts...';
                    }, 3000);
                }
            });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const page = new IndexPage();
    page.init();
});