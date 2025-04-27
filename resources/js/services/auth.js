import axios from 'axios';
const authService = {
    /**
     * Log in a user with email and password
     */
    async login(credentials) {
        var _a;
        try {
            const response = await axios.post('/api/login', credentials);
            // Verify that we have a user object
            if (!response.data.user) {
                throw new Error('Invalid response from server');
            }
            return response.data.user;
        }
        catch (error) {
            console.error('Login error:', ((_a = error.response) === null || _a === void 0 ? void 0 : _a.data) || error.message);
            throw error;
        }
    },
    /**
     * Log out the current user
     */
    async logout() {
        var _a;
        try {
            await axios.post('/api/logout');
        }
        catch (error) {
            console.error('Logout error:', ((_a = error.response) === null || _a === void 0 ? void 0 : _a.data) || error.message);
            throw error;
        }
    },
    /**
     * Get the current authenticated user
     */
    async getCurrentUser() {
        var _a, _b;
        try {
            const response = await axios.get('/api/user');
            return response.data;
        }
        catch (error) {
            // Return null instead of throwing if it's a 401 error (not authenticated)
            if (((_a = error.response) === null || _a === void 0 ? void 0 : _a.status) === 401) {
                return null;
            }
            console.error('Get user error:', ((_b = error.response) === null || _b === void 0 ? void 0 : _b.data) || error.message);
            return null;
        }
    },
    /**
     * Check if the user is authenticated
     */
    async isAuthenticated() {
        const user = await this.getCurrentUser();
        return user !== null;
    }
};
export default authService;
