document.addEventListener('DOMContentLoaded', () => {
    handleFormSubmit('verify-user-form', '/Services/verify_user.php', {
        messageId: 'error-message',
        onSuccess: () => {
            window.location.href = 'reset-password-form.html';
        }
    });
});