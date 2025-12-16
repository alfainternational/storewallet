import { createStore } from 'vuex';
import auth from './modules/auth';
import lang from './modules/lang';
import cart from './modules/cart';
import products from './modules/products';
import auctions from './modules/auctions';
import wallet from './modules/wallet';

export default createStore({
  state: {
    loading: false,
    notification: {
      show: false,
      type: 'success',
      message: ''
    }
  },

  mutations: {
    SET_LOADING(state, loading) {
      state.loading = loading;
    },

    SHOW_NOTIFICATION(state, { type, message }) {
      state.notification = {
        show: true,
        type,
        message
      };
    },

    HIDE_NOTIFICATION(state) {
      state.notification.show = false;
    }
  },

  actions: {
    showNotification({ commit }, { type = 'success', message }) {
      commit('SHOW_NOTIFICATION', { type, message });
      setTimeout(() => {
        commit('HIDE_NOTIFICATION');
      }, 5000);
    }
  },

  modules: {
    auth,
    lang,
    cart,
    products,
    auctions,
    wallet
  }
});
