import axios from 'axios';
import { useAuthStore } from '../store/auth.store';

const api = axios.create({
    baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:5001/api',
});

api.interceptors.request.use(
    (config) => {
        const { token, activeRole } = useAuthStore.getState();
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }
        if (activeRole) {
            config.headers['X-Active-Role'] = activeRole;
        }
        return config;
    },
    (error) => Promise.reject(error)
);

export default api;
