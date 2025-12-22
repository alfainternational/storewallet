import axios from 'axios';

export default {
  namespaced: true,

  state: {
    balance: 0,
    escrowBalance: 0,
    transactions: [],
    remittances: [],
    pagination: {
      currentPage: 1,
      perPage: 20,
      total: 0
    }
  },

  getters: {
    balance: state => state.balance,
    escrowBalance: state => state.escrowBalance,
    availableBalance: state => state.balance - state.escrowBalance,
    transactions: state => state.transactions,
    remittances: state => state.remittances,
    pagination: state => state.pagination
  },

  mutations: {
    SET_BALANCE(state, balance) {
      state.balance = balance;
    },

    SET_ESCROW_BALANCE(state, escrowBalance) {
      state.escrowBalance = escrowBalance;
    },

    SET_TRANSACTIONS(state, transactions) {
      state.transactions = transactions;
    },

    SET_REMITTANCES(state, remittances) {
      state.remittances = remittances;
    },

    SET_PAGINATION(state, pagination) {
      state.pagination = { ...state.pagination, ...pagination };
    },

    ADD_TRANSACTION(state, transaction) {
      state.transactions.unshift(transaction);
    }
  },

  actions: {
    async fetchBalance({ commit }) {
      try {
        const response = await axios.get('/wallet/balance');
        commit('SET_BALANCE', response.data.balance);
        commit('SET_ESCROW_BALANCE', response.data.escrow_balance || 0);
        return { success: true };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to fetch balance'
        };
      }
    },

    async fetchTransactions({ commit, state }, page = 1) {
      try {
        const params = {
          page,
          per_page: state.pagination.perPage
        };

        const response = await axios.get('/wallet/transactions', { params });

        commit('SET_TRANSACTIONS', response.data.data);
        commit('SET_PAGINATION', {
          currentPage: response.data.current_page,
          total: response.data.total
        });

        return { success: true };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to fetch transactions'
        };
      }
    },

    async deposit({ commit, dispatch }, { amount, paymentMethod }) {
      try {
        const response = await axios.post('/wallet/deposit', {
          amount,
          payment_method: paymentMethod
        });

        // Refresh balance
        await dispatch('fetchBalance');

        return {
          success: true,
          message: 'Deposit initiated successfully',
          payment_url: response.data.payment_url
        };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to deposit'
        };
      }
    },

    async withdraw({ commit, dispatch }, { amount, method, details }) {
      try {
        await axios.post('/wallet/withdraw', {
          amount,
          method,
          details
        });

        // Refresh balance
        await dispatch('fetchBalance');

        return {
          success: true,
          message: 'Withdrawal request submitted successfully'
        };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to withdraw'
        };
      }
    },

    async transfer({ commit, dispatch }, { recipient, amount, note }) {
      try {
        const response = await axios.post('/wallet/transfer', {
          recipient,
          amount,
          note
        });

        // Refresh balance
        await dispatch('fetchBalance');
        commit('ADD_TRANSACTION', response.data.transaction);

        return {
          success: true,
          message: 'Transfer completed successfully'
        };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to transfer'
        };
      }
    },

    async fetchRemittances({ commit, state }, page = 1) {
      try {
        const params = {
          page,
          per_page: state.pagination.perPage
        };

        const response = await axios.get('/remittances', { params });

        commit('SET_REMITTANCES', response.data.data);
        commit('SET_PAGINATION', {
          currentPage: response.data.current_page,
          total: response.data.total
        });

        return { success: true };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to fetch remittances'
        };
      }
    },

    async createRemittance({ dispatch }, remittanceData) {
      try {
        const response = await axios.post('/remittances', remittanceData);

        // Refresh balance
        await dispatch('fetchBalance');

        return {
          success: true,
          message: 'Remittance created successfully',
          remittance: response.data
        };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to create remittance',
          errors: error.response?.data?.errors || {}
        };
      }
    },

    async verifyRemittance({ dispatch }, { remittanceId, pickupCode }) {
      try {
        await axios.post(`/remittances/${remittanceId}/verify`, {
          pickup_code: pickupCode
        });

        return {
          success: true,
          message: 'Remittance verified and completed successfully'
        };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to verify remittance'
        };
      }
    }
  }
};
