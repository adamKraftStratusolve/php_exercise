document.addEventListener('DOMContentLoaded', () => {

    const loginForm = document.getElementById('login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const errorMessageDiv = document.getElementById('error-message');

    loginForm.addEventListener('submit', (event) => {
        event.preventDefault();

        errorMessageDiv.style.display = 'none';

        const formData = new FormData();
        formData.append('username', emailInput.value);
        formData.append('password', passwordInput.value);

        apiService.post('/login.php', formData)
            .then(data => {
                console.log('Login successful:', data);
                window.location.href = 'index.html';
            })
            .catch(error => {
                console.error('Login failed:', error.message);
                errorMessageDiv.textContent = error.message;
                errorMessageDiv.style.display = 'block';
            });
    });
});