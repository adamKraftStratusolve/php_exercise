const apiService = {
    async post(url, data) {
        try {
            // This config object tells axios to send session cookies with the request.
            const config = {
                withCredentials: true
            };
            const response = await axios.post(url, data, config);
            return response.data;
        } catch (error) {
            throw this._handleError(error);
        }
    },

    async get(url) {
        try {
            // Also adding the config here for consistency on GET requests.
            const config = {
                withCredentials: true
            };
            const response = await axios.get(url, config);
            return response.data;
        } catch (error) {
            throw this._handleError(error);
        }
    },

    _handleError(error) {

        if (error.response && error.response.status === 401) {

            console.error(error.response.data);

            if (!window.location.pathname.endsWith('login.html')) {
                window.location.href = '/Views/html/login.html';
                return new Error('Redirecting to login.');
            }
        }

        let errorMessage = 'An unexpected network error occurred.';
        if (error.response && error.response.data && error.response.data.error) {
            errorMessage = error.response.data.error;
        }

        return new Error(errorMessage);
    }
};