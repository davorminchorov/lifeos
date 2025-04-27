import axios from 'axios';

export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
  remember?: boolean;
}

export interface AuthResponse {
  message?: string;
  user?: User;
}

const authService = {
  /**
   * Log in a user with email and password
   */
  async login(credentials: LoginCredentials): Promise<User> {
    try {
      const response = await axios.post<AuthResponse>('/api/login', credentials);

      // Verify that we have a user object
      if (!response.data.user) {
        throw new Error('Invalid response from server');
      }

      return response.data.user;
    } catch (error: any) {
      console.error('Login error:', error.response?.data || error.message);
      throw error;
    }
  },

  /**
   * Log out the current user
   */
  async logout(): Promise<void> {
    try {
      await axios.post('/api/logout');
    } catch (error: any) {
      console.error('Logout error:', error.response?.data || error.message);
      throw error;
    }
  },

  /**
   * Get the current authenticated user
   */
  async getCurrentUser(): Promise<User | null> {
    try {
      const response = await axios.get<User>('/api/user');
      return response.data;
    } catch (error: any) {
      // Return null instead of throwing if it's a 401 error (not authenticated)
      if (error.response?.status === 401) {
        return null;
      }

      console.error('Get user error:', error.response?.data || error.message);
      return null;
    }
  },

  /**
   * Check if the user is authenticated
   */
  async isAuthenticated(): Promise<boolean> {
    const user = await this.getCurrentUser();
    return user !== null;
  }
};

export default authService;
