import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const token = ref(localStorage.getItem('token') || null);
    const loading = ref(false);
    const error = ref(null);

    const isAuthenticated = computed(() => !!token.value);

    const setToken = (newToken) => {
        token.value = newToken;
        localStorage.setItem('token', newToken);
        axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`;
    };

    const clearAuth = () => {
        user.value = null;
        token.value = null;
        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];
    };

    const register = async (userData) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.post('/api/register', userData);
            user.value = response.data.user;
            setToken(response.data.access_token);
            window.location.href = '/dashboard';
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error en el registro';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const login = async (credentials) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.post('/api/login', credentials);
            user.value = response.data.user;
            setToken(response.data.access_token);
            window.location.href = '/dashboard';
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error en el login';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const logout = async () => {
        try {
            await axios.post('/api/logout');
        } catch (err) {
            console.error('Error en logout:', err);
        } finally {
            clearAuth();
            window.location.href = '/login';
        }
    };

    const fetchUser = async () => {
        try {
            const response = await axios.get('/api/me');
            user.value = response.data.user;
        } catch (err) {
            clearAuth();
            throw err;
        }
    };

    return {
        user,
        token,
        loading,
        error,
        isAuthenticated,
        register,
        login,
        logout,
        fetchUser,
    };
});
