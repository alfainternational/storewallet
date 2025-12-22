import axios from 'axios';

export default {
  namespaced: true,

  state: {
    auctions: [],
    currentAuction: null,
    filters: {
      type: null, // 'product', 'delivery', 'international'
      status: 'active',
      city: null
    },
    pagination: {
      currentPage: 1,
      perPage: 20,
      total: 0
    }
  },

  getters: {
    auctions: state => state.auctions,
    currentAuction: state => state.currentAuction,
    filters: state => state.filters,
    activeAuctions: state => state.auctions.filter(a => a.status === 'active'),
    endingSoonAuctions: state => {
      const now = new Date();
      return state.auctions.filter(a => {
        const endTime = new Date(a.end_time);
        const hoursLeft = (endTime - now) / (1000 * 60 * 60);
        return a.status === 'active' && hoursLeft < 24;
      });
    }
  },

  mutations: {
    SET_AUCTIONS(state, auctions) {
      state.auctions = auctions;
    },

    SET_CURRENT_AUCTION(state, auction) {
      state.currentAuction = auction;
    },

    SET_FILTERS(state, filters) {
      state.filters = { ...state.filters, ...filters };
    },

    SET_PAGINATION(state, pagination) {
      state.pagination = { ...state.pagination, ...pagination };
    },

    UPDATE_AUCTION_BID(state, { auctionId, bid }) {
      const auction = state.auctions.find(a => a.id === auctionId);
      if (auction) {
        auction.current_bid = bid.amount;
        auction.bid_count = (auction.bid_count || 0) + 1;
      }

      if (state.currentAuction && state.currentAuction.id === auctionId) {
        state.currentAuction.current_bid = bid.amount;
        state.currentAuction.bids.unshift(bid);
      }
    }
  },

  actions: {
    async fetchAuctions({ commit, state }, page = 1) {
      try {
        const params = {
          page,
          per_page: state.pagination.perPage,
          ...state.filters
        };

        const response = await axios.get('/auctions', { params });

        commit('SET_AUCTIONS', response.data.data);
        commit('SET_PAGINATION', {
          currentPage: response.data.current_page,
          total: response.data.total
        });

        return { success: true };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to fetch auctions'
        };
      }
    },

    async fetchAuction({ commit }, auctionId) {
      try {
        const response = await axios.get(`/auctions/${auctionId}`);
        commit('SET_CURRENT_AUCTION', response.data);
        return { success: true };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to fetch auction'
        };
      }
    },

    async placeBid({ commit }, { auctionId, amount }) {
      try {
        const response = await axios.post(`/auctions/${auctionId}/bid`, { amount });

        commit('UPDATE_AUCTION_BID', {
          auctionId,
          bid: response.data.bid
        });

        return {
          success: true,
          message: 'Bid placed successfully',
          bid: response.data.bid
        };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to place bid'
        };
      }
    },

    async createAuction({ dispatch }, auctionData) {
      try {
        const response = await axios.post('/auctions', auctionData);
        return {
          success: true,
          message: 'Auction created successfully',
          auction: response.data
        };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to create auction',
          errors: error.response?.data?.errors || {}
        };
      }
    },

    async applyFilters({ commit, dispatch }, filters) {
      commit('SET_FILTERS', filters);
      return dispatch('fetchAuctions', 1);
    }
  }
};
