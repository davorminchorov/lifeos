import { createStore } from '@xstate/store';
import authService from '../services/auth';
export const authStore = createStore({
    context: {
        user: null,
        error: null,
        isLoading: false
    },
    emits: {
        authStateChanged: (payload) => {
            // Optional side effects can go here
        }
    },
    on: {
        // Check current authentication status
        CHECK_AUTH: (context, _, enqueue) => {
            return Object.assign(Object.assign({}, context), { isLoading: true, error: null });
        },
        AUTH_SUCCESS: (context, event, enqueue) => {
            enqueue.emit.authStateChanged({ isAuthenticated: true });
            return Object.assign(Object.assign({}, context), { user: event.user, error: null, isLoading: false });
        },
        AUTH_FAILURE: (context, event, enqueue) => {
            enqueue.emit.authStateChanged({ isAuthenticated: false });
            return Object.assign(Object.assign({}, context), { user: null, error: event.error, isLoading: false });
        },
        // Login flow
        LOGIN: (context, event, enqueue) => {
            // Start async login process
            enqueue.effect(async () => {
                var _a, _b, _c, _d;
                try {
                    const user = await authService.login({
                        email: event.email,
                        password: event.password,
                        remember: event.remember
                    });
                    authStore.send({ type: 'AUTH_SUCCESS', user });
                }
                catch (error) {
                    const errorMessage = ((_b = (_a = error.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.message) ||
                        ((_d = (_c = error.response) === null || _c === void 0 ? void 0 : _c.data) === null || _d === void 0 ? void 0 : _d.error) ||
                        "Authentication failed";
                    authStore.send({ type: 'AUTH_FAILURE', error: errorMessage });
                }
            });
            return Object.assign(Object.assign({}, context), { isLoading: true, error: null });
        },
        // Logout flow
        LOGOUT: (context, _, enqueue) => {
            // Start async logout process
            enqueue.effect(async () => {
                var _a, _b;
                try {
                    await authService.logout();
                    // After logout, clear the user and reset state
                    authStore.send({ type: 'AUTH_FAILURE', error: null });
                    // Redirect to login page will be handled by the component
                }
                catch (error) {
                    const errorMessage = ((_b = (_a = error.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.message) ||
                        "Logout failed";
                    authStore.send({ type: 'AUTH_FAILURE', error: errorMessage });
                }
            });
            return Object.assign(Object.assign({}, context), { isLoading: true });
        }
    }
});
// Initialize by checking authentication on load
export function initAuth() {
    authStore.send({ type: 'CHECK_AUTH' });
    // Check authentication status
    authService.getCurrentUser()
        .then(user => {
        if (user) {
            authStore.send({ type: 'AUTH_SUCCESS', user });
        }
        else {
            authStore.send({ type: 'AUTH_FAILURE', error: null });
        }
    })
        .catch(error => {
        authStore.send({
            type: 'AUTH_FAILURE',
            error: error.message || "Failed to fetch authentication status"
        });
    });
}
