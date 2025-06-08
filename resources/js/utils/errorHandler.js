export const handleLoginError = (error) => {
    if (error.response) {
        switch (error.response.status) {
            case 401:
                return 'Invalid email or password';
            case 429:
                return 'Too many attempts. Please try again later';
            case 422:
                return error.response.data.message || 'Validation error';
            case 500:
                return 'Server error. Please try again later';
            default:
                return 'An error occurred. Please try again';
        }
    }
    return 'Network error. Please check your connection';
};

export const showError = (message) => {
    // You can customize this based on your UI library (e.g., Toastify, SweetAlert)
    alert(message);
};

export const handleAxiosError = (error) => {
    console.error('API Error:', error);
    
    if (error.response) {
        // Server responded with error
        console.error('Response data:', error.response.data);
        console.error('Response status:', error.response.status);
        return error.response.data.message || 'Server error';
    } else if (error.request) {
        // Request made but no response
        console.error('Request error:', error.request);
        return 'No response from server';
    } else {
        // Error in request setup
        console.error('Error setting up request:', error.message);
        return 'Error making request';
    }
}; 