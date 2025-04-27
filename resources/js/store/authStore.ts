import { createStore } from '@xstate/store';
import authService, { User } from '../services/auth';

export const authStore = createStore({
  context: {
    user: null as User | null,
    error: null as string | null,
    isLoading: false
  },
  emits: {
    authStateChanged: (payload: { isAuthenticated: boolean }) => {
      // Optional side effects can go here
    }
  },
  on: {
    // Check current authentication status
    CHECK_AUTH: (context, _, enqueue) => {
      return {
        ...context,
        isLoading: true,
        error: null
      };
    },
    AUTH_SUCCESS: (context, event: { user: User }, enqueue) => {
      enqueue.emit.authStateChanged({ isAuthenticated: true });
      return {
        ...context,
        user: event.user,
        error: null,
        isLoading: false
      };
    },
    AUTH_FAILURE: (context, event: { error: string | null }, enqueue) => {
      enqueue.emit.authStateChanged({ isAuthenticated: false });
      return {
        ...context,
        user: null,
        error: event.error,
        isLoading: false
      };
    },
    // Login flow
    LOGIN: (context, event: { email: string; password: string; remember: boolean }, enqueue) => {
      // Start async login process
      enqueue.effect(async () => {
        try {
          const user = await authService.login({
            email: event.email,
            password: event.password,
            remember: event.remember
          });
          authStore.send({ type: 'AUTH_SUCCESS', user });
        } catch (error: any) {
          const errorMessage = error.response?.data?.message ||
                             error.response?.data?.error ||
                             "Authentication failed";
          authStore.send({ type: 'AUTH_FAILURE', error: errorMessage });
        }
      });

      return {
        ...context,
        isLoading: true,
        error: null
      };
    },
    // Logout flow
    LOGOUT: (context, _, enqueue) => {
      // Start async logout process
      enqueue.effect(async () => {
        try {
          await authService.logout();
          authStore.send({ type: 'AUTH_FAILURE', error: null });
        } catch (error: any) {
          const errorMessage = error.response?.data?.message ||
                             "Logout failed";
          authStore.send({ type: 'AUTH_FAILURE', error: errorMessage });
        }
      });

      return {
        ...context,
        isLoading: true
      };
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
      } else {
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
