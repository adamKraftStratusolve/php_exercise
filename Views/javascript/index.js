document.addEventListener('DOMContentLoaded', () => {
    const welcomeMessage = document.getElementById('welcome-message');
    const postsContainer = document.getElementById('posts-container');
    const logoutLink = document.getElementById('logout-link');

    const postModal = document.getElementById('postModal');
    const createPostTrigger = document.getElementById('create-post-trigger');
    const closeModalBtn = document.getElementById('modal-close-btn');
    const cancelModalBtn = document.getElementById('modal-cancel-btn');
    const createPostForm = document.getElementById('create-post-form');
    const postTextarea = document.getElementById('post-textarea');
    const modalErrorMessage = document.getElementById('modal-error-message');

    const renderPost = (post) => {
        const postCard = document.createElement('div');
        postCard.className = 'card post-card';
        postCard.innerHTML = `
            <div class="post-header">
                <div class="user-info">${post.FirstName} ${post.LastName} <span>@${post.Username}</span></div>
                <div class="timestamp">${new Date(post.CreatedAt).toLocaleString()}</div>
            </div>
            <p class="post-body">${post.PostText}</p>
        `;
        postsContainer.prepend(postCard);
    };

    const fetchAndRenderPosts = () => {
        apiService.get('/index.php')
            .then(posts => {
                postsContainer.innerHTML = '';
                posts.forEach(post => renderPost(post));
            })
            .catch(error => {
                postsContainer.innerHTML = `<p class="error">Could not load posts. ${error.message}</p>`;
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
                console.error("Could not fetch user profile:", error);
                welcomeMessage.textContent = 'Welcome!';
            });
    };

    createPostTrigger.addEventListener('click', () => postModal.style.display = 'flex');
    closeModalBtn.addEventListener('click', () => postModal.style.display = 'none');
    cancelModalBtn.addEventListener('click', () => postModal.style.display = 'none');

    createPostForm.addEventListener('submit', (event) => {
        event.preventDefault();
        modalErrorMessage.style.display = 'none';

        const postText = postTextarea.value.trim();
        if (!postText) {
            modalErrorMessage.textContent = 'Post cannot be empty.';
            modalErrorMessage.style.display = 'block';
            return;
        }

        const formData = new FormData();
        formData.append('post_text', postText);

        apiService.post('/Services/create_post.php', formData)
            .then(() => {
                postModal.style.display = 'none';
                postTextarea.value = '';
                fetchAndRenderPosts();
            })
            .catch(error => {
                modalErrorMessage.textContent = error.message;
                modalErrorMessage.style.display = 'block';
            });
    });

    logoutLink.addEventListener('click', (event) => {
        event.preventDefault();
        apiService.post('/Services/logout.php', {})
            .then(() => {
                window.location.href = '../html/login.html';
            })
            .catch(error => {
                console.error('Logout failed:', error);
                alert('Could not log out. Please try again.');
            });
    });

    fetchUserProfile();
    fetchAndRenderPosts();
});