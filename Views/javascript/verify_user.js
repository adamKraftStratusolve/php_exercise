document.addEventListener('DOMContentLoaded', () => {
    handleFormSubmit('verifyUserForm', '/Services/verify_user.php', {
        messageId: 'errorMessage',
        onSuccess: () => {
            window.location.href = 'reset_password_form.html';
        }
    });
});
