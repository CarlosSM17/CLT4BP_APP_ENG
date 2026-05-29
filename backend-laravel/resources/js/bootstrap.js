import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.baseURL = '';
window.axios.defaults.withCredentials = true;

// Configurar token al iniciar
const token = localStorage.getItem('token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Interceptor para agregar token en cada request
window.axios.interceptors.request.use(
    config => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    error => Promise.reject(error)
);

// Interceptor para manejar errores
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token');
            if (window.location.pathname !== '/login') {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);
