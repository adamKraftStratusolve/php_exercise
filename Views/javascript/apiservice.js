const apiService = {
    async post(url, data) {
        try {
            const response = await axios.post(url, data);
            return response.data;
        } catch (error) {
            throw this._handleError(error);
        }
    },

    async get(url) {
        try {
            const response = await axios.get(url);
            return response.data;
        } catch (error) {
            throw this._handleError(error);
        }
    },

    _handleError(error) {

        if (error.response && error.response.status === 401) {

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