document.addEventListener('DOMContentLoaded', () => {
    handleFormSubmit('login-form', '/login.php', {
        messageId: 'error-message',
        onSuccess: () => {
            window.location.href = 'index.html';
        }
    });
});