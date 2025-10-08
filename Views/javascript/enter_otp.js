document.addEventListener('DOMContentLoaded', () => {
    handleFormSubmit('otpForm', '/Services/verify_otp.php', {
        messageId: 'errorMessage',
        onSuccess: () => {
            window.location.href = 'reset_password_form.html';
        }
    });
});