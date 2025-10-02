document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const messageDiv = document.getElementById('message-display');

    handleFormSubmit('signup-form', '/Services/register.php', {
        messageId: 'message-display',

        beforeSubmit: () => {
            if (passwordInput.value !== confirmPasswordInput.value) {
                messageDiv.textContent = 'Passwords do not match.';
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
                return false;
            }
            return true;
        },

        onSuccess: (data) => {
            messageDiv.textContent = data.success + ' Redirecting to login...';
            messageDiv.className = 'message success';
            messageDiv.style.display = 'block';

            document.getElementById('signup-form').reset();

            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000);
        }
    });
});