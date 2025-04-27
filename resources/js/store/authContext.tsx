import React, { createContext, useContext, useEffect } from 'react';
import { useSelector } from '@xstate/store/react';
import { authStore, initAuth } from './authStore';
import type { User } from '../services/auth';

// Define the Auth context type
interface AuthContextValue {
  isAuthenticated: boolean;
  isLoading: boolean;
  user: User | null;
  error: string | null;
  login: (email: string, password: string, remember?: boolean) => void;
  logout: () => void;
  checkAuth: () => void;
}

// Create the Auth context
const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  // Initialize auth on mount
  useEffect(() => {
    initAuth();
  }, []);

  // Get values from store using selectors
  const user = useSelector(authStore, state => state.context.user);
  const error = useSelector(authStore, state => state.context.error);
  const isLoading = useSelector(authStore, state => state.context.isLoading);
  const isAuthenticated = useSelector(authStore, state => state.context.user !== null);

  // Create context value
  const value: AuthContextValue = {
    isAuthenticated,
    isLoading,
    user,
    error,
    login: (email: string, password: string, remember: boolean = false) =>
      authStore.send({ type: 'LOGIN', email, password, remember }),
    logout: () => authStore.send({ type: 'LOGOUT' }),
    checkAuth: () => initAuth()
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}

// Custom hook to use auth
export function useAuth() {
  const context = useContext(AuthContext);

  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }

  return context;
}
