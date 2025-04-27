import React, { createContext, useContext, useState, useEffect } from 'react';
import authService from '../services/auth';

// Create context for the auth state
const AuthContext = createContext(undefined);

// Custom hook to initialize the auth state
function useAuthState() {
  const [state, setState] = useState({
    user: null,
    error: null,
    isLoading: true,
    isAuthenticated: false
  });

  const checkAuth = async () => {
    setState(prev => ({ ...prev, isLoading: true, error: null }));
    try {
      const user = await authService.getCurrentUser();
      setState({
        user,
        error: null,
        isLoading: false,
        isAuthenticated: !!user
      });
    } catch (error) {
      setState({
        user: null,
        error: error instanceof Error ? error.message : 'Authentication failed',
        isLoading: false,
        isAuthenticated: false
      });
    }
  };

  const login = async (email, password, remember = false) => {
    setState(prev => ({ ...prev, isLoading: true, error: null }));
    try {
      const user = await authService.login({ email, password, remember });
      setState({
        user,
        error: null,
        isLoading: false,
        isAuthenticated: true
      });
    } catch (error) {
      setState({
        user: null,
        error: error instanceof Error ? error.message : 'Login failed',
        isLoading: false,
        isAuthenticated: false
      });
    }
  };

  const logout = async () => {
    setState(prev => ({ ...prev, isLoading: true }));
    try {
      await authService.logout();
      setState({
        user: null,
        error: null,
        isLoading: false,
        isAuthenticated: false
      });
    } catch (error) {
      setState(prev => ({
        ...prev,
        error: error instanceof Error ? error.message : 'Logout failed',
        isLoading: false
      }));
    }
  };

  // Check auth status on mount
  useEffect(() => {
    checkAuth();
  }, []);

  return {
    state,
    isAuthenticated: state.isAuthenticated,
    isLoading: state.isLoading,
    user: state.user,
    error: state.error,
    login,
    logout,
    checkAuth
  };
}

// Provider component to make auth state available
export function AuthProvider({ children }) {
  const auth = useAuthState();
  return <AuthContext.Provider value={auth}>{children}</AuthContext.Provider>;
}

// Custom hook to use the auth state
export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
