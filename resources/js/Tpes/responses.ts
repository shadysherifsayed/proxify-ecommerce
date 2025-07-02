import { User } from "./entities";

export interface LoginResponse {
    token: string;
    user: User;
}
