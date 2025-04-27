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
    const response = await axios.post<AuthResponse>('/api/login', credentials);
    return response.data.user as User;
  },

  /**
   * Log out the current user
   */
  async logout(): Promise<void> {
    await axios.get('/api/logout');
  },

  /**
   * Get the current authenticated user
   */
  async getCurrentUser(): Promise<User | null> {
    try {
      const response = await axios.get<User>('/api/user');
      return response.data;
    } catch (error) {
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
