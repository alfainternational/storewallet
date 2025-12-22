import axios from 'axios';

export default {
  namespaced: true,

  state: {
    user: null,
    token: localStorage.getItem('auth_token') || null
  },

  getters: {
    isAuthenticated: state => !!state.token,
    user: state => state.user,
    isMerchant: state => state.user?.role === 'merchant',
    isShippingCompany: state => state.user?.role === 'shipping_company',
    isAdmin: state => state.user?.role === 'admin'
  },

  mutations: {
    SET_USER(state, user) {
      state.user = user;
      if (user) {
        localStorage.setItem('user', JSON.stringify(user));
      } else {
        localStorage.removeItem('user');
      }
    },

    SET_TOKEN(state, token) {
      state.token = token;
      if (token) {
        localStorage.setItem('auth_token', token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      } else {
        localStorage.removeItem('auth_token');
        delete axios.defaults.headers.common['Authorization'];
      }
    },

    LOGOUT(state) {
      state.user = null;
      state.token = null;
      localStorage.removeItem('user');
      localStorage.removeItem('auth_token');
      delete axios.defaults.headers.common['Authorization'];
    }
  },

  actions: {
    async login({ commit }, credentials) {
      try {
        const response = await axios.post('/login', credentials);
        const { user, token } = response.data;

        commit('SET_USER', user);
        commit('SET_TOKEN', token);

        return { success: true };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Login failed'
        };
      }
    },

    async register({ commit }, userData) {
      try {
        const response = await axios.post('/register', userData);
        const { user, token } = response.data;

        commit('SET_USER', user);
        commit('SET_TOKEN', token);

        return { success: true };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Registration failed',
          errors: error.response?.data?.errors || {}
        };
      }
    },

    async logout({ commit }) {
      try {
        await axios.post('/logout');
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        commit('LOGOUT');
      }
    },

    async fetchUser({ commit }) {
      try {
        const response = await axios.get('/user');
        commit('SET_USER', response.data);
        return { success: true };
      } catch (error) {
        commit('LOGOUT');
        return { success: false };
      }
    },

    async updateProfile({ commit, state }, profileData) {
      try {
        const response = await axios.put('/user/profile', profileData);
        commit('SET_USER', response.data.user);
        return { success: true, message: 'Profile updated successfully' };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Update failed',
          errors: error.response?.data?.errors || {}
        };
      }
    },

    async verifyPhone({ commit }, { phone, code }) {
      try {
        const response = await axios.post('/verify-phone', { phone, code });
        commit('SET_USER', response.data.user);
        return { success: true, message: 'Phone verified successfully' };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Verification failed'
        };
      }
    },

    async sendOTP({ commit }, phone) {
      try {
        await axios.post('/send-otp', { phone });
        return { success: true, message: 'OTP sent successfully' };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to send OTP'
        };
      }
    }
  }
};
