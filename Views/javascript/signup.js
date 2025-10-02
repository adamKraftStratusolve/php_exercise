document.addEventListener('DOMContentLoaded', () => {

    const signupForm = document.getElementById('signup-form');
    const firstNameInput = document.getElementById('firstName'); // New
    const lastNameInput = document.getElementById('lastName');   // New
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

        formData.append('first_name', firstNameInput.value);
        formData.append('last_name', lastNameInput.value);
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
                    window.location.href = 'login.html';
                }, 2000);
            })
            .catch(error => {
                messageDiv.textContent = error.message;
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
            });
    });
});