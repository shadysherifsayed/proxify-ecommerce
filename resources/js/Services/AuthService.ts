import { User } from '@/Types/entities';
import { LoginRequest, RegisterRequest } from '@/Types/requests';
import { AuthenticatedResponse } from '@/Types/responses';
import { BaseService } from './BaseService';

/**
 * AuthService
 *
 * Service class for handling authentication-related API operations.
 * Manages user login, registration, logout, and token storage.
 */
class AuthService extends BaseService {
  /**
   * Get the current authenticated user's information
   *
   * @returns {Promise<{user: User}>} Promise resolving to current user data
   * @throws {Error} Throws error if user is not authenticated
   */
  user(): Promise<{ user: User }> {
    return this.send('GET', 'users/me');
  }

  /**
   * Authenticate a user with email and password
   *
   * @param {LoginRequest} data - Login credentials
   * @param {string} data.email - User's email address
   * @param {string} data.password - User's password
   * @returns {Promise<AuthenticatedResponse>} Promise resolving to user data and token
   * @throws {Error} Throws error if credentials are invalid
   */
  login(data: LoginRequest): Promise<AuthenticatedResponse> {
    return this.send('POST', 'login', data);
  }

  /**
   * Log out the current user and clear stored token
   *
   * @returns {Promise<void>} Promise that resolves when logout is complete
   *
   */
  logout(): Promise<void> {
    localStorage.removeItem('token');
    return this.send('POST', 'logout');
  }

  /**
   * Register a new user account
   *
   * @param {RegisterRequest} data - Registration data
   * @param {string} data.name - User's full name
   * @param {string} data.email - User's email address
   * @param {string} data.password - User's password
   * @param {string} data.password_confirmation - Password confirmation
   * @returns {Promise<AuthenticatedResponse>} Promise resolving to user data and token
   * @throws {Error} Throws error if validation fails or email already exists
   */
  register(data: RegisterRequest): Promise<AuthenticatedResponse> {
    return this.send('POST', 'register', data);
  }

  /**
   * Store authentication token in localStorage
   *
   * @param {string} token - JWT authentication token
   */
  setToken(token: string) {
    localStorage.setItem('token', token);
  }
}

export default new AuthService();
