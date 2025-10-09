class BasePage {
    constructor() {
        this.welcomeMessage = document.getElementById('welcomeMessage');
        this.logoutLink = document.getElementById('logoutLink');
        this.navToggle = document.querySelector('.nav-toggle');
        this.navLinks = document.querySelector('.nav-links');
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

        if (this.navToggle) {
            this.navToggle.addEventListener('click', () => {
                this.navLinks.classList.toggle('nav-active');
                const icon = this.navToggle.querySelector('i');
                if (icon.classList.contains('fa-bars')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
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