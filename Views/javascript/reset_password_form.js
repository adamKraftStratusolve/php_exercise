document.addEventListener('DOMContentLoaded', () => {
    const messageDiv = document.getElementById('errorMessage');

    handleFormSubmit('resetPasswordForm', '/Services/perform_reset.php', {
        messageId: 'errorMessage',
        onSuccess: (data) => {
            if (messageDiv) {
                messageDiv.textContent = data.success + ' Redirecting to login...';
                messageDiv.className = 'message success';
                messageDiv.style.display = 'block';
            }
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000);
        }
    });
});
