import './bootstrap';
import { createApp } from 'vue';
import axios from 'axios';
import { getAuthToken, setAuthToken, isTokenExpired } from './utils/auth';
import { refreshSession } from './utils/session';
import Login from './components/Login.vue';

// Configure axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

// Add response interceptor for token handling
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            // Clear invalid token
            setAuthToken(null);
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

// Check and set existing token
const token = getAuthToken();
if (token && !isTokenExpired(token)) {
    setAuthToken(token);
    refreshSession();
}

// Create Vue app
const app = createApp({});

// Register components
app.component('login-form', Login);

// Mount app
app.mount('#app');
