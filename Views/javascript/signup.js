document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const messageDiv = document.getElementById('messageDisplay');

    handleFormSubmit('signupForm', '/Services/register.php', {
        messageId: 'messageDisplay',

        beforeSubmit: () => {
            if (passwordInput.value !== confirmPasswordInput.value) {
                if (messageDiv) {
                    messageDiv.textContent = 'Passwords do not match.';
                    messageDiv.className = 'message error';
                    messageDiv.style.display = 'block';
                }
                return false;
            }
            return true;
        },

        onSuccess: (data) => {
            if (messageDiv) {
                messageDiv.textContent = data.success + ' Redirecting to login...';
                messageDiv.className = 'message success';
                messageDiv.style.display = 'block';
            }
            const signupForm = document.getElementById('signupForm');
            if (signupForm) {
                signupForm.reset();
            }
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000);
        }
    });
});
