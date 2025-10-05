class BasePage {
    constructor() {
        this.welcomeMessage = document.getElementById('welcomeMessage');
        this.logoutLink = document.getElementById('logoutLink');
    }

    init() {
        this._addEventListeners();
        this._fetchUserProfile();
    }

    _addEventListeners() {
        if (this.logoutLink) {
            this.logoutLink.addEventListener('click', (event) => {
                event.preventDefault();
                apiService.post('/Services/logout.php', {})
                    .then(() => {
                        window.location.href = 'login.html';
                    });
            });
        }
    }

    _fetchUserProfile() {
        apiService.get('/profile.php')
            .then(data => {
                this.onPageLoad(data);
            })
            .catch(error => {
                if (error.message !== 'Redirecting to login.') {
                    console.error("Could not fetch user profile:", error);
                }
            });
    }

    onPageLoad(data) {
        const user = data.profile;
        if (user) {
            if (this.welcomeMessage && user.firstName) {
                this.welcomeMessage.textContent = `Welcome, ${user.firstName}!`;
            }
            this.onProfileLoad(user);
        } else {
            console.error("User profile data is missing in API response.", data);
        }
    }

    onProfileLoad(user) {
    }
}