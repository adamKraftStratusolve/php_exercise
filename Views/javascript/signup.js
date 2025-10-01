document.addEventListener('DOMContentLoaded', () => {
    // 1. Select all the form elements
    const signupForm = document.getElementById('signup-form');
    const fullNameInput = document.getElementById('fullName');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const messageDiv = document.getElementById('message-display');

    signupForm.addEventListener('submit', (event) => {
        event.preventDefault();
        messageDiv.style.display = 'none';

        if (passwordInput.value !== confirmPasswordInput.value) {
            messageDiv.textContent = 'Passwords do not match.';
            messageDiv.className = 'message error';
            messageDiv.style.display = 'block';
            return;
        }

        const formData = new FormData();

        const nameParts = fullNameInput.value.trim().split(' ');
        const firstName = nameParts.shift() || '';
        const lastName = nameParts.join(' ') || '';

        formData.append('first_name', firstName);
        formData.append('last_name', lastName);
        formData.append('username', usernameInput.value);
        formData.append('email', emailInput.value);
        formData.append('password', passwordInput.value);

        apiService.post('/Services/register.php', formData)
            .then(data => {

                messageDiv.textContent = data.success + ' Redirecting to login...';
                messageDiv.className = 'message success';
                messageDiv.style.display = 'block';
                signupForm.reset();

                setTimeout(() => {
                    window.location.href = '../html/login.html';
                }, 2000);
            })
            .catch(error => {

                messageDiv.textContent = error.message;
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
            });
    });
});