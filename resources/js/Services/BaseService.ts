import axios, { AxiosInstance } from 'axios';
import qs from 'qs';

/**
 * BaseService
 *
 * Abstract base class for all API services.
 * Provides common HTTP functionality including authentication,
 * error handling, and request/response interceptors.
 */
export class BaseService {
  client: AxiosInstance;

  /**
   * Initialize the base service with axios client and interceptors
   *
   * Sets up:
   * - Base URL from environment variables
   * - Response interceptor for validation error handling
   * - Default headers and authentication
   */
  constructor() {
    this.client = axios.create({
      baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:3000',
    });

    // Add interceptor for validation errors
    this.client.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response && error.response.status === 422) {
          // Attach validation errors to the error object
          error.validationErrors = error.response.data.errors || {};
        }
        return Promise.reject(error);
      },
    );
  }

  /**
   * Send HTTP request to API endpoint
   *
   * @param {string} method - HTTP method (GET, POST, PATCH, DELETE, etc.)
   * @param {string} endpoint - API endpoint path
   * @param {any} [data] - Request data (body for POST/PATCH, query params for GET)
   * @param {Record<string, string>} [headers] - Additional headers to include
   * @returns {Promise<any>} Promise resolving to response data
   * @throws {Error} Throws error with validation errors attached for 422 responses
   *
   * @example
   */
  async send(
    method: string,
    endpoint: string,
    data?: any,
    headers?: Record<string, string>,
  ): Promise<any> {
    headers = headers || {
      'Content-Type': 'application/json',
      accept: 'application/json',
    };

    const token = this.getToken();

    if (token) {
      headers.Authorization = `Bearer ${token}`;
    }

    // send data as query parameters for GET requests
    if (method.toUpperCase() === 'GET' && data) {
      const params = qs.stringify(data, {
        arrayFormat: 'brackets', 
        encode: false,
        skipNulls: true,
        sort: (a, b) => a.localeCompare(b),
      });
      endpoint += `?${params}`;
      data = undefined; // clear data for GET requests
    }

    const response = await this.client.request({
      method,
      url: endpoint,
      data,
      headers,
    });

    if (response.status >= 200 && response.status < 300) {
      return response.data;
    } else {
      return response;
    }
  }

  /**
   * Get authentication token from localStorage
   *
   * @returns {string | null} JWT token if exists, null otherwise
   */
  getToken(): string | null {
    return localStorage.getItem('token') || null;
  }
}
