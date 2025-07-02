import axios, { AxiosInstance } from 'axios';
export class BaseService {
  client: AxiosInstance;

  constructor() {
    this.client = axios.create({
      baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:3000',
    });
  }

  async send(method: string, endpoint: string, data?: any): Promise<any> {
    const response = await this.client.request({
      method,
      url: endpoint,
      data,
      headers: {
        'Content-Type': 'application/json',
        accept: 'application/json',
        Authorization: `Bearer ${this.getToken()}`,
      },
    });

    if (response.status >= 200 && response.status < 300) {
      return response.data;
    } else {
      return response
    }
  }

  getToken(): string | null {
    return localStorage.getItem('token') || null;
  }
}
