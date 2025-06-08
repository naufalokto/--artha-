import { logout } from './auth';

let sessionTimer = null;

export const initializeSession = (userData) => {
    localStorage.setItem('user', JSON.stringify(userData));
    startSessionTimer();
};

export const startSessionTimer = () => {
    if (sessionTimer) {
        clearTimeout(sessionTimer);
    }

    const sessionTimeout = 30 * 60 * 1000; // 30 minutes
    sessionTimer = setTimeout(() => {
        logout();
    }, sessionTimeout);
};

export const refreshSession = () => {
    const user = getUser();
    if (user) {
        startSessionTimer();
    }
};

export const getUser = () => {
    try {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    } catch (e) {
        console.error('Error getting user data:', e);
        return null;
    }
};

export const clearSession = () => {
    if (sessionTimer) {
        clearTimeout(sessionTimer);
    }
    localStorage.removeItem('user');
}; 