import { LoginRequest, RegisterRequest } from "@/Tpes/requests";
import { BaseService } from "./BaseService";
import { LoginResponse } from "@/Tpes/responses";

class AuthService extends BaseService {

    user() {
        return this.send('GET', 'users/me');
    }

    login(data: LoginRequest): Promise<LoginResponse> {
        return this.send('POST', 'login', data);
    }

    logout() {
        return this.send('POST', 'logout');
    }

    register(data: RegisterRequest) {
        return this.send('POST', 'register', data);
    }
}


export default new AuthService();
