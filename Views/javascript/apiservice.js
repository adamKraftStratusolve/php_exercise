const apiService = {
    async post(url, data) {
        try {
            const response = await axios.post(url, data);
            return response.data;
        } catch (error) {
            let errorMessage = 'An unexpected error occurred while sending data.';
            if (error.response && error.response.data && error.response.data.error) {
                errorMessage = error.response.data.error;
            }
            throw new Error(errorMessage);
        }
    },

    async get(url) {
        try {
            const response = await axios.get(url);
            return response.data;
        } catch (error) {
            let errorMessage = 'An unexpected error occurred while fetching data.';
            if (error.response && error.response.data && error.response.data.error) {
                errorMessage = error.response.data.error;
            }
            throw new Error(errorMessage);
        }
    }
};