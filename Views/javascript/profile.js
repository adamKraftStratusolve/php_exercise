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

    const fetchAndPopulateProfile = () => {
        apiService.get('/profile.php')
            .then(data => {
                const user = data.profile;
                firstNameInput.value = user.FirstName;
                lastNameInput.value = user.LastName;
                emailInput.value = user.EmailAddress;
                usernameInput.value = user.Username;
                welcomeMessage.textContent = `Welcome, ${user.FirstName}!`;
            })
            .catch(error => {
                messageDiv.textContent = `Error loading profile: ${error.message}`;
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
            });
    };

    profileForm.addEventListener('submit', (event) => {
        event.preventDefault();
        messageDiv.style.display = 'none';

        const formData = new FormData();
        formData.append('first_name', firstNameInput.value);
        formData.append('last_name', lastNameInput.value);
        formData.append('email', emailInput.value);

        const newPassword = newPasswordInput.value;
        if (newPassword) {
            formData.append('current_password', currentPasswordInput.value);
            formData.append('new_password', newPassword);
        }

        apiService.post('/Services/update_profile.php', formData)
            .then(data => {
                messageDiv.textContent = 'Profile updated successfully!';
                messageDiv.className = 'message success';

                if(data.message) {
                    messageDiv.textContent = data.message;
                    messageDiv.className = 'message';
                }

                messageDiv.style.display = 'block';

                currentPasswordInput.value = '';
                newPasswordInput.value = '';
            })
            .catch(error => {
                messageDiv.textContent = error.message;
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
            });
    });

    logoutLink.addEventListener('click', (event) => {
        event.preventDefault();
        apiService.post('/logout.php', {})
            .then(() => {
                window.location.href = 'login.html';
            });
    });

    fetchAndPopulateProfile();
});