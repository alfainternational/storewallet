/**
 * StoreWallet - Main Application Entry Point
 *
 * Arabic-first e-commerce platform for Sudan
 */

import { createApp } from 'vue';
import router from './router';
import store from './store';
import i18n from './i18n';
import axios from 'axios';

// Import Bootstrap
import 'bootstrap';

// Import Font Awesome
import '@fortawesome/fontawesome-free/css/all.css';

// Import main components
import App from './App.vue';

// Configure Axios
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.baseURL = '/api';

// Add auth token if exists
const token = localStorage.getItem('auth_token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Response interceptor for handling errors
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            // Unauthorized - clear token and redirect to login
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            router.push('/login');
        }
        return Promise.reject(error);
    }
);

// Create Vue app
const app = createApp(App);

// Use plugins
app.use(router);
app.use(store);
app.use(i18n);

// Global properties
app.config.globalProperties.$axios = axios;

// Mount app
app.mount('#app');
