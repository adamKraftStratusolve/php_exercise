document.addEventListener('DOMContentLoaded', () => {
    emailjs.init({
        publicKey: "US37_TRLKsKvMq3KD",
    });

    handleFormSubmit('verifyUserForm', '/Services/verify_user.php', {

        messageId: 'errorMessage',
        onSuccess: (response) => {
            const templateParams = {
                otp: response.otp,
                email: response.email,
            };

            emailjs.send('service_pjtbq3n', 'template_kqa391b', templateParams)
                .then((res) => {
                    console.log('EmailJS Success!', res.status, res.text);
                    window.location.href = 'enter_otp.html';
                })
                .catch((err) => {
                    console.error('EmailJS Failed...', err);
                    const messageDiv = document.getElementById('errorMessage');
                    if (messageDiv) {
                        messageDiv.textContent = 'User verified, but failed to send OTP email.';
                        messageDiv.style.display = 'block';
                    }
                });
        }
    });
});