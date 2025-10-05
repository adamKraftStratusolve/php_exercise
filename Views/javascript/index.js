class IndexPage extends BasePage {
    constructor() {
        super();
        this.postsContainer = document.getElementById('postsContainer');
        this.createPostTrigger = document.getElementById('createPostTrigger');
        this.postModal = document.getElementById('postModal');
        this.closeModalBtn = document.getElementById('modalCloseBtn');
        this.cancelModalBtn = document.getElementById('modalCancelBtn');
        this.postTextarea = document.getElementById('postTextarea');
    }

    init() {
        super.init();
        this._addPageEventListeners();
        this._fetchAllPosts();
        setInterval(() => this._fetchLatestPosts(), 5000);
    }

    onProfileLoad(user) {
        if (this.createPostTrigger && user.firstName) {
            this.createPostTrigger.textContent = `What's on your mind, ${user.firstName}?`;
        }
    }

    _fetchAllPosts() {
        apiService.get('/index.php')
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

    _fetchLatestPosts() {
        const firstPost = this.postsContainer.querySelector('.post-card');
        const latestId = firstPost ? firstPost.dataset.postId : 0;

        apiService.get(`/index.php?sinceId=${latestId}`)
            .then(newPosts => {
                if (Array.isArray(newPosts) && newPosts.length > 0) {
                    newPosts.reverse().forEach(post => {
                        const postCard = createPostCard(post);
                        this.postsContainer.prepend(postCard);
                    });
                }
            })
            .catch(error => {
                console.error("Error fetching latest posts:", error);
            });
    }

    _addPageEventListeners() {
        this.createPostTrigger.addEventListener('click', () => this.postModal.style.display = 'flex');
        this.closeModalBtn.addEventListener('click', () => this.postModal.style.display = 'none');
        this.cancelModalBtn.addEventListener('click', () => this.postModal.style.display = 'none');

        handleFormSubmit('createPostForm', '/Services/create_post.php', {
            messageId: 'modalErrorMessage',
            onSuccess: () => {
                this.postModal.style.display = 'none';
                this.postTextarea.value = '';
                this._fetchLatestPosts();
            },
        });

        this.postsContainer.addEventListener('click', (event) => this._handlePostClick(event));
        this.postsContainer.addEventListener('submit', (event) => this._handleCommentSubmit(event));
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
            apiService.post('/Services/toggle_like.php', formData).catch(err => console.error(err));
        }
    }

    _handleCommentSubmit(event) {
        const commentForm = event.target.closest('.comment-form');
        if (commentForm) {
            event.preventDefault();
            const commentInput = commentForm.querySelector('.comment-input');
            const formData = new FormData(commentForm);

            commentInput.disabled = true;

            apiService.post('/Services/post_comment.php', formData)
                .then(() => {
                    commentInput.value = '';
                    this._fetchAllPosts();
                })
                .catch(error => alert('Could not post comment: ' + error.message))
                .finally(() => {
                    commentInput.disabled = false;
                });
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const page = new IndexPage();
    page.init();
});