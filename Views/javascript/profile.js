class ProfilePage extends BasePage {
    constructor() {
        super();
        this.firstNameInput = document.getElementById('firstName');
        this.lastNameInput = document.getElementById('lastName');
        this.emailInput = document.getElementById('email');
        this.usernameInput = document.getElementById('username');
        this.currentPasswordInput = document.getElementById('currentPassword');
        this.newPasswordInput = document.getElementById('newPassword');
        this.messageDiv = document.getElementById('messageDisplay');
        this.postsContainer = document.getElementById('userPostsContainer');
        this.profilePicturePreview = document.getElementById('profilePicturePreview');
        this.pictureInput = document.getElementById('pictureInput');
        this.savePictureBtn = document.getElementById('savePictureBtn');
        this.pictureMessage = document.getElementById('pictureMessage');
        this.selectedFile = null;
    }

    init() {
        super.init();
        this._addPageEventListeners();
    }

    onPageLoad(data) {

        this.onProfileLoad(data.profile);
        this._renderPosts(data.posts);
    }

    onProfileLoad(user) {
        this.firstNameInput.value = user.firstName || '';
        this.lastNameInput.value = user.lastName || '';
        this.emailInput.value = user.emailAddress || '';
        this.usernameInput.value = user.username || '';
        this.profilePicturePreview.src = user.profileImageUrl || '/Uploads/default-avatar.jpg';
    }

    _renderPosts(posts) {
        this.postsContainer.innerHTML = '';
        if (!posts || posts.length === 0) {
            this.postsContainer.innerHTML = '<p>You have not created any posts yet.</p>';
            return;
        }
        posts.forEach(post => {
            const postCard = createPostCard(post);
            this.postsContainer.appendChild(postCard);
        });
    }

    _addPageEventListeners() {

        this.pictureInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (!file) return;

            const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2 MB

            if (file.size > MAX_FILE_SIZE) {
                this.pictureMessage.textContent = 'File is too large. Please select an image smaller than 2MB.';
                this.pictureMessage.className = 'message error';
                this.pictureMessage.style.display = 'block';
                this.savePictureBtn.style.display = 'none';
                this.pictureInput.value = '';
                this.selectedFile = null;
                return;
            }

            this.selectedFile = file;
            const reader = new FileReader();
            reader.onload = (e) => { this.profilePicturePreview.src = e.target.result; };
            reader.readAsDataURL(this.selectedFile);
            this.savePictureBtn.style.display = 'inline-block';
            this.pictureMessage.style.display = 'none'; // Hide any previous messages
        });

        this.savePictureBtn.addEventListener('click', () => {
            if (!this.selectedFile) return;
            const formData = new FormData();
            formData.append('profilePicture', this.selectedFile);

            apiService.post('../../Services/upload_picture.php', formData)
                .then(data => {

                    this.pictureMessage.textContent = 'Picture updated successfully! Image was converted to JPG format.';
                    this.pictureMessage.className = 'message success';
                    this.pictureMessage.style.display = 'block';
                    this.savePictureBtn.style.display = 'none';
                    this.profilePicturePreview.src = data.imageUrl;
                })
                .catch(error => {
                    this.pictureMessage.textContent = error.message;
                    this.pictureMessage.className = 'message error';
                    this.pictureMessage.style.display = 'block';
                });
        });

        handleFormSubmit('profileForm', '/Services/update_profile.php', {
            messageId: 'messageDisplay',
            getFormData: (form) => {
                const formData = new FormData(form);
                if (!formData.get('newPassword')) {
                    formData.delete('currentPassword');
                }
                return formData;
            },
            onSuccess: (data) => {
                this.messageDiv.textContent = data.message || 'Profile updated successfully!';
                this.messageDiv.className = 'message success';
                this.messageDiv.style.display = 'block';
                this.currentPasswordInput.value = '';
                this.newPasswordInput.value = '';
            }
        });

        this.postsContainer.addEventListener('click', (event) => {
            const deleteBtn = event.target.closest('.delete-btn');
            if (deleteBtn) {
                const postCard = deleteBtn.closest('.post-card');
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
                        formData.append('postId', postId);

                        apiService.post('/Services/delete_post.php', formData)
                            .then(() => {
                                postCard.remove();
                                Swal.fire('Deleted!', 'Your post has been deleted.', 'success');
                            })
                            .catch(error => {
                                if (error.message !== 'Redirecting to login.') {
                                    Swal.fire('Error!', `Could not delete post: ${error.message}`, 'error');
                                }
                            });
                    }
                });
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const page = new ProfilePage();
    page.init();
});