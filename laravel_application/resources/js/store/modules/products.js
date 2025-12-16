import axios from 'axios';

export default {
  namespaced: true,

  state: {
    products: [],
    currentProduct: null,
    filters: {
      category: null,
      minPrice: null,
      maxPrice: null,
      city: null,
      search: ''
    },
    pagination: {
      currentPage: 1,
      perPage: 20,
      total: 0
    }
  },

  getters: {
    products: state => state.products,
    currentProduct: state => state.currentProduct,
    filters: state => state.filters,
    pagination: state => state.pagination
  },

  mutations: {
    SET_PRODUCTS(state, products) {
      state.products = products;
    },

    SET_CURRENT_PRODUCT(state, product) {
      state.currentProduct = product;
    },

    SET_FILTERS(state, filters) {
      state.filters = { ...state.filters, ...filters };
    },

    SET_PAGINATION(state, pagination) {
      state.pagination = { ...state.pagination, ...pagination };
    },

    RESET_FILTERS(state) {
      state.filters = {
        category: null,
        minPrice: null,
        maxPrice: null,
        city: null,
        search: ''
      };
    }
  },

  actions: {
    async fetchProducts({ commit, state }, page = 1) {
      try {
        const params = {
          page,
          per_page: state.pagination.perPage,
          ...state.filters
        };

        const response = await axios.get('/products', { params });

        commit('SET_PRODUCTS', response.data.data);
        commit('SET_PAGINATION', {
          currentPage: response.data.current_page,
          total: response.data.total
        });

        return { success: true };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to fetch products'
        };
      }
    },

    async fetchProduct({ commit }, productId) {
      try {
        const response = await axios.get(`/products/${productId}`);
        commit('SET_CURRENT_PRODUCT', response.data);
        return { success: true };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to fetch product'
        };
      }
    },

    async searchProducts({ commit, dispatch }, searchTerm) {
      commit('SET_FILTERS', { search: searchTerm });
      return dispatch('fetchProducts', 1);
    },

    async applyFilters({ commit, dispatch }, filters) {
      commit('SET_FILTERS', filters);
      return dispatch('fetchProducts', 1);
    },

    resetFilters({ commit, dispatch }) {
      commit('RESET_FILTERS');
      return dispatch('fetchProducts', 1);
    },

    async addReview({ dispatch }, { productId, rating, comment }) {
      try {
        await axios.post(`/products/${productId}/reviews`, { rating, comment });
        return { success: true, message: 'Review added successfully' };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to add review'
        };
      }
    }
  }
};
