export const sanitizeInput = (input) => {
    if (typeof input !== 'string') return input;
    return input.trim().replace(/[<>]/g, '');
};

export const validateEmail = (email) => {
    const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return re.test(String(email).toLowerCase());
};

export const validatePassword = (password) => {
    return password && password.length >= 8;
};

export const validateLoginForm = (email, password) => {
    const errors = {};

    if (!email) {
        errors.email = 'Email is required';
    } else if (!validateEmail(email)) {
        errors.email = 'Please enter a valid email';
    }

    if (!password) {
        errors.password = 'Password is required';
    } else if (!validatePassword(password)) {
        errors.password = 'Password must be at least 8 characters';
    }

    return {
        isValid: Object.keys(errors).length === 0,
        errors
    };
};

export const rateLimiter = (() => {
    const attempts = new Map();
    const maxAttempts = 5;
    const timeWindow = 5 * 60 * 1000; // 5 minutes

    return {
        checkLimit: (key) => {
            const now = Date.now();
            const userAttempts = attempts.get(key) || { count: 0, timestamp: now };

            // Reset if time window has passed
            if (now - userAttempts.timestamp > timeWindow) {
                userAttempts.count = 0;
                userAttempts.timestamp = now;
            }

            return userAttempts.count < maxAttempts;
        },
        increment: (key) => {
            const now = Date.now();
            const userAttempts = attempts.get(key) || { count: 0, timestamp: now };

            if (now - userAttempts.timestamp > timeWindow) {
                userAttempts.count = 1;
                userAttempts.timestamp = now;
            } else {
                userAttempts.count++;
            }

            attempts.set(key, userAttempts);
            return userAttempts.count;
        },
        reset: (key) => {
            attempts.delete(key);
        }
    };
})(); 