import { jsx as _jsx } from "react/jsx-runtime";
import { createContext, useContext, useEffect } from 'react';
import { useSelector } from '@xstate/store/react';
import { authStore, initAuth } from './authStore';
// Create the Auth context
const AuthContext = createContext(undefined);
export function AuthProvider({ children }) {
    // Initialize auth on mount
    useEffect(() => {
        // Check auth status when component mounts
        initAuth();
        // Set up a timer to periodically check auth status (every 5 minutes)
        const intervalId = setInterval(() => {
            initAuth();
        }, 5 * 60 * 1000);
        // Clean up the interval when component unmounts
        return () => clearInterval(intervalId);
    }, []);
    // Get values from store using selectors
    const user = useSelector(authStore, state => state.context.user);
    const error = useSelector(authStore, state => state.context.error);
    const isLoading = useSelector(authStore, state => state.context.isLoading);
    const isAuthenticated = useSelector(authStore, state => state.context.user !== null);
    // Create context value
    const value = {
        isAuthenticated,
        isLoading,
        user,
        error,
        login: (email, password, remember = false) => authStore.send({ type: 'LOGIN', email, password, remember }),
        logout: () => authStore.send({ type: 'LOGOUT' }),
        checkAuth: () => initAuth()
    };
    return (_jsx(AuthContext.Provider, { value: value, children: children }));
}
// Custom hook to use auth
export function useAuth() {
    const context = useContext(AuthContext);
    if (context === undefined) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
}
