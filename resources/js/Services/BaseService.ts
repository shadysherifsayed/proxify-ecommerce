import axios, { AxiosInstance } from 'axios';
export class BaseService {
  client: AxiosInstance;

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
      const params = new URLSearchParams(data).toString();
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

  getToken(): string | null {
    return localStorage.getItem('token') || null;
  }
}
