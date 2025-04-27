import React, { createContext, useContext } from 'react';
import { useMachine } from '@xstate/react';
import { authMachine } from './authMachine';

// Create context for the auth state
type AuthContextType = ReturnType<typeof useAuthState> | undefined;
const AuthContext = createContext<AuthContextType>(undefined);

// Custom hook to initialize the auth state machine
function useAuthState() {
  const [state, send] = useMachine(authMachine);

  return {
    state,
    isAuthenticated: state.matches('authenticated'),
    isLoading: state.matches('loading') || state.matches('loggingIn') || state.matches('loggingOut'),
    user: state.context.user,
    error: state.context.error,
    login: (email: string, password: string, remember: boolean = false) =>
      send({ type: 'LOGIN', email, password, remember }),
    logout: () => send({ type: 'LOGOUT' }),
    checkAuth: () => send({ type: 'CHECK_AUTH' }),
  };
}

// Provider component to make auth state available
export function AuthProvider({ children }: { children: React.ReactNode }) {
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
