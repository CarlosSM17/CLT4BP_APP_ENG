import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    withCredentials: true,
});

// Auth endpoints
export const authAPI = {
    register: (data) => api.post('/register', data),
    login: (data) => api.post('/login', data),
    logout: () => api.post('/logout'),
    me: () => api.get('/me'),
};

// User endpoints
export const userAPI = {
    getAll: (params) => api.get('/users', { params }),
    getById: (id) => api.get(`/users/${id}`),
    update: (id, data) => api.put(`/users/${id}`, data),
    createInstructor: (data) => api.post('/users/instructors', data),
    deactivate: (id) => api.delete(`/users/${id}/deactivate`),
};

export default api;
