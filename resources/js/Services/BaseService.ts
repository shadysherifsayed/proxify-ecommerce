export class BaseService {
    baseUrl: string;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:3000';
    }

    async send(method: string, endpoint: string, data?: any): Promise<any> {
        const response = await fetch(`${this.baseUrl}/${endpoint}`, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'accept': 'application/json',
                'Authorization': `Bearer ${this.getToken()}`
            },
            body: JSON.stringify(data),
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response.json();
    }

    getToken(): string | null {
        return localStorage.getItem('token') || null;
    }

}
