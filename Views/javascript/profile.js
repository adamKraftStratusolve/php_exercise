document.addEventListener('DOMContentLoaded', () => {

    const profileForm = document.getElementById('profile-form');
    const welcomeMessage = document.getElementById('welcome-message');
    const firstNameInput = document.getElementById('firstName');
    const lastNameInput = document.getElementById('lastName');
    const emailInput = document.getElementById('email');
    const usernameInput = document.getElementById('username');
    const currentPasswordInput = document.getElementById('currentPassword');
    const newPasswordInput = document.getElementById('newPassword');
    const messageDiv = document.getElementById('message-display');
    const logoutLink = document.getElementById('logout-link');
    const postsContainer = document.getElementById('user-posts-container');

    const renderPosts = (posts) => {
        postsContainer.innerHTML = '';

        if (!posts || posts.length === 0) {
            postsContainer.innerHTML = '<p>You have not created any posts yet.</p>';
            return;
        }

        posts.forEach(post => {
            const postCard = document.createElement('div');
            postCard.className = 'post-card';
            postCard.setAttribute('data-post-id', post.PostID);

            postCard.innerHTML = `
                <p>${post.PostText}</p>
                <div class="post-meta">
                    <span>${new Date(post.CreatedAt).toLocaleString()}</span>
                    <button class="btn btn-danger delete-btn">Delete</button>
                </div>
            `;
            postsContainer.appendChild(postCard);
        });
    };

    const fetchAndPopulateProfile = () => {
        apiService.get('/profile.php')
            .then(data => {

                const user = data.profile;
                firstNameInput.value = user.FirstName;
                lastNameInput.value = user.LastName;
                emailInput.value = user.EmailAddress;
                usernameInput.value = user.Username;
                welcomeMessage.textContent = `Welcome, ${user.FirstName}!`;

                renderPosts(data.posts);
            })
            .catch(error => {

                if (error.message !== 'Redirecting to login.') {
                    messageDiv.textContent = `Error loading profile: ${error.message}`;
                    messageDiv.className = 'message error';
                    messageDiv.style.display = 'block';
                }
            });
    };

    profileForm.addEventListener('submit', (event) => {
        event.preventDefault();
        messageDiv.style.display = 'none';

        const formData = new FormData();
        formData.append('first_name', firstNameInput.value);
        formData.append('last_name', lastNameInput.value);
        formData.append('email', emailInput.value);
        formData.append('username', usernameInput.value);

        const newPassword = newPasswordInput.value;
        if (newPassword) {
            formData.append('current_password', currentPasswordInput.value);
            formData.append('new_password', newPassword);
        }

        apiService.post('/Services/update_profile.php', formData)
            .then(data => {
                messageDiv.textContent = 'Profile updated successfully!';
                messageDiv.className = 'message success';
                if (data.message) {
                    messageDiv.textContent = data.message;
                }
                messageDiv.style.display = 'block';

                currentPasswordInput.value = '';
                newPasswordInput.value = '';
            })
            .catch(error => {
                if (error.message !== 'Redirecting to login.') {
                    messageDiv.textContent = error.message;
                    messageDiv.className = 'message error';
                    messageDiv.style.display = 'block';
                }
            });
    });

    postsContainer.addEventListener('click', (event) => {
        if (event.target.classList.contains('delete-btn')) {
            const postCard = event.target.closest('.post-card');
            const postId = postCard.getAttribute('data-post-id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('post_id', postId);

                    apiService.post('/Services/delete_post.php', formData)
                        .then(() => {
                            postCard.remove();
                            Swal.fire(
                                'Deleted!',
                                'Your post has been deleted.',
                                'success'
                            );
                        })
                        .catch(error => {
                            if (error.message !== 'Redirecting to login.') {
                                Swal.fire(
                                    'Error!',
                                    `Could not delete post: ${error.message}`,
                                    'error'
                                );
                            }
                        });
                }
            });
        }
    });

    logoutLink.addEventListener('click', (event) => {
        event.preventDefault();
        apiService.post('/Services/logout.php', {})
            .then(() => {
                window.location.href = 'login.html';
            });
    });

    fetchAndPopulateProfile();
});