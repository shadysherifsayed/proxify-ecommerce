import { LoginRequest, RegisterRequest } from '@/Types/requests';
import { DataWrapper } from '@/Types/responses';
import { BaseService } from './BaseService';
import { User } from '@/Types/entities';

class AuthService extends BaseService {
    user(): Promise<DataWrapper<User>> {
        return this.send('GET', 'users/me');
    }

    login(data: LoginRequest): Promise<DataWrapper<User>> {
        return this.send('POST', 'login', data);
    }

    logout() {
        return this.send('POST', 'logout');
    }

    register(data: RegisterRequest): Promise<DataWrapper<User>> {
        return this.send('POST', 'register', data);
    }

    setToken(token: string) {
        localStorage.setItem('token', token);
    }
}

export default new AuthService();
