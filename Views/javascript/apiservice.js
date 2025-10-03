const apiService = {
    async post(url, data) {
        try {
            const response = await axios.post(url, data);
            return response.data;
        } catch (error) {
            this._handleError(error);
        }
    },

    async get(url) {
        try {
            const response = await axios.get(url);
            return response.data;
        } catch (error) {
            this._handleError(error);
        }
    },

    _handleError(error) {
        if (error.response && error.response.status === 401) {
            window.location.href = '/Views/html/login.html';
            throw new Error('Redirecting to login.');
        }

        let errorMessage = 'An unexpected network error occurred.';
        if (error.response && error.response.data && error.response.data.error) {
            errorMessage = error.response.data.error;
        }

        throw new Error(errorMessage);
    }
};
