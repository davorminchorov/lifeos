import axios from 'axios';

// Create a custom axios instance with default configuration
export const axiosClient = axios.create({
    baseURL: '/',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: true,
});

// Add request interceptor to include the CSRF token automatically
axiosClient.interceptors.request.use(config => {
    const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        config.headers['X-CSRF-TOKEN'] = csrfToken;
    }
    return config;
});

// Add response interceptor to handle common errors
axiosClient.interceptors.response.use(
    response => response,
    error => {
        // Handle authentication errors
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

export default axiosClient;
