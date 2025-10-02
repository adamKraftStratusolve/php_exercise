document.addEventListener('DOMContentLoaded', () => {
    const welcomeMessage = document.getElementById('welcome-message');
    const postsContainer = document.getElementById('posts-container');
    const logoutLink = document.getElementById('logout-link');
    const postModal = document.getElementById('postModal');
    const createPostTrigger = document.getElementById('create-post-trigger');
    const closeModalBtn = document.getElementById('modal-close-btn');
    const cancelModalBtn = document.getElementById('modal-cancel-btn');
    const postTextarea = document.getElementById('post-textarea');

    const renderPost = (post) => {
        const postCard = createPostCard(post, { showHeader: true });
        postsContainer.append(postCard);
    };

    const fetchAndRenderPosts = () => {
        apiService.get('/index.php')
            .then(posts => {
                postsContainer.innerHTML = '';
                posts.forEach(renderPost); // Call the simplified function
            })
            .catch(error => {
                if (error.message !== 'Redirecting to login.') {
                    postsContainer.innerHTML = `<p class="error">Could not load posts. ${error.message}</p>`;
                }
            });
    };

    const fetchUserProfile = () => {
        apiService.get('/profile.php')
            .then(data => {
                const user = data.profile;
                welcomeMessage.textContent = `Welcome, ${user.FirstName}!`;
                createPostTrigger.textContent = `What's on your mind, ${user.FirstName}?`;
            })
            .catch(error => {
                if (error.message !== 'Redirecting to login.') {
                    console.error("Could not fetch user profile:", error);
                }
            });
    };

    createPostTrigger.addEventListener('click', () => postModal.style.display = 'flex');
    closeModalBtn.addEventListener('click', () => postModal.style.display = 'none');
    cancelModalBtn.addEventListener('click', () => postModal.style.display = 'none');

    handleFormSubmit('create-post-form', '/Services/create_post.php', {
        messageId: 'modal-error-message',
        beforeSubmit: () => {
            if (postTextarea.value.trim() === '') {
                const messageDiv = document.getElementById('modal-error-message');
                messageDiv.textContent = 'Post cannot be empty.';
                messageDiv.style.display = 'block';
                return false;
            }
            return true;
        },
        onSuccess: () => {
            postModal.style.display = 'none';
            postTextarea.value = '';
            fetchAndRenderPosts();
        },
    });

    logoutLink.addEventListener('click', (event) => {
        event.preventDefault();
        apiService.post('/Services/logout.php', {}).then(() => window.location.href = 'login.html');
    });

    fetchUserProfile();
    fetchAndRenderPosts();
});