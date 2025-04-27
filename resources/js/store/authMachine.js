import { createMachine, assign } from 'xstate';
import authService from '../services/auth';
// Create the auth machine
export const authMachine = createMachine({
    predictableActionArguments: true,
    schema: {
        context: {},
        events: {},
    },
    id: 'auth',
    initial: 'idle',
    context: {
        user: null,
        error: null,
    },
    states: {
        idle: {
            on: {
                CHECK_AUTH: 'loading',
            },
            always: 'loading',
        },
        loading: {
            invoke: {
                src: 'checkAuth',
                onDone: {
                    target: 'authenticated',
                    actions: assign({
                        user: (_, event) => event.data,
                        error: null,
                    }),
                },
                onError: 'unauthenticated',
            },
        },
        unauthenticated: {
            on: {
                LOGIN: 'loggingIn',
                CHECK_AUTH: 'loading',
            },
        },
        loggingIn: {
            invoke: {
                src: 'login',
                onDone: {
                    target: 'authenticated',
                    actions: assign({
                        user: (_, event) => event.data,
                        error: null,
                    }),
                },
                onError: {
                    target: 'unauthenticated',
                    actions: assign({
                        error: (_, event) => { var _a; return ((_a = event.data) === null || _a === void 0 ? void 0 : _a.message) || 'Login failed'; },
                    }),
                },
            },
        },
        authenticated: {
            on: {
                LOGOUT: 'loggingOut',
                CHECK_AUTH: 'loading',
            },
        },
        loggingOut: {
            invoke: {
                src: 'logout',
                onDone: {
                    target: 'unauthenticated',
                    actions: assign({
                        user: null,
                        error: null,
                    }),
                },
                onError: {
                    target: 'authenticated',
                    actions: assign({
                        error: (_, event) => { var _a; return ((_a = event.data) === null || _a === void 0 ? void 0 : _a.message) || 'Logout failed'; },
                    }),
                },
            },
        },
    },
}, {
    services: {
        checkAuth: async () => {
            const user = await authService.getCurrentUser();
            if (!user)
                throw new Error('Not authenticated');
            return user;
        },
        login: async (_, event) => {
            const { email, password, remember } = event;
            return await authService.login({ email, password, remember });
        },
        logout: async () => {
            await authService.logout();
        },
    },
});
