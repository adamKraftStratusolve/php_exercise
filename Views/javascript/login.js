document.addEventListener('DOMContentLoaded', () => {

    const credentialInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const rememberedCredential = localStorage.getItem('rememberedCredential');

    if (credentialInput && rememberedCredential) {

        credentialInput.value = rememberedCredential;

        if (passwordInput) {
            passwordInput.focus();
        }
    }

    handleFormSubmit('loginForm', '/login.php', {
        messageId: 'errorMessage',
        onSuccess: () => {

            if (credentialInput) {
                localStorage.setItem('rememberedCredential', credentialInput.value);
            }

            window.location.href = 'index.html';
        }
    });
});