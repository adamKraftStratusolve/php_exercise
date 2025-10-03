document.addEventListener('DOMContentLoaded', () => {
    handleFormSubmit('loginForm', '/login.php', {
        messageId: 'errorMessage',
        onSuccess: () => {
            window.location.href = 'index.html';
        }
    });
});
