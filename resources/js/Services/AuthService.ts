import { User } from '@/Types/entities';
import { LoginRequest, RegisterRequest } from '@/Types/requests';
import { AuthenticatedResponse } from '@/Types/responses';
import { BaseService } from './BaseService';

class AuthService extends BaseService {
  user(): Promise<{ user: User }> {
    return this.send('GET', 'users/me');
  }

  login(data: LoginRequest): Promise<AuthenticatedResponse> {
    return this.send('POST', 'login', data);
  }

  logout(): Promise<void> {
    localStorage.removeItem('token');
    return this.send('POST', 'logout');
  }

  register(data: RegisterRequest): Promise<AuthenticatedResponse> {
    return this.send('POST', 'register', data);
  }

  setToken(token: string) {
    localStorage.setItem('token', token);
  }
}

export default new AuthService();
