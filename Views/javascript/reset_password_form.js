document.addEventListener('DOMContentLoaded', () => {
    const messageDiv = document.getElementById('error-message');

    handleFormSubmit('reset-password-form', '/Services/perform_reset.php', {
        messageId: 'error-message',
        onSuccess: (data) => {
            messageDiv.textContent = data.success + ' Redirecting to login...';
            messageDiv.className = 'message success';
            messageDiv.style.display = 'block';

            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000);
        }
    });
});