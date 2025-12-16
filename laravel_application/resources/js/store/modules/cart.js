export default {
  namespaced: true,

  state: {
    items: JSON.parse(localStorage.getItem('cart')) || []
  },

  getters: {
    items: state => state.items,

    itemCount: state => state.items.reduce((total, item) => total + item.quantity, 0),

    subtotal: state => state.items.reduce((total, item) => {
      return total + (item.price * item.quantity);
    }, 0),

    total: (state, getters) => {
      // Add shipping and other fees here if needed
      return getters.subtotal;
    },

    hasItems: state => state.items.length > 0
  },

  mutations: {
    ADD_ITEM(state, product) {
      const existingItem = state.items.find(item =>
        item.id === product.id &&
        JSON.stringify(item.variant) === JSON.stringify(product.variant)
      );

      if (existingItem) {
        existingItem.quantity += product.quantity || 1;
      } else {
        state.items.push({
          id: product.id,
          name: product.name,
          price: product.price,
          image: product.image,
          variant: product.variant || null,
          quantity: product.quantity || 1,
          merchant_id: product.merchant_id
        });
      }

      localStorage.setItem('cart', JSON.stringify(state.items));
    },

    REMOVE_ITEM(state, index) {
      state.items.splice(index, 1);
      localStorage.setItem('cart', JSON.stringify(state.items));
    },

    UPDATE_QUANTITY(state, { index, quantity }) {
      if (quantity <= 0) {
        state.items.splice(index, 1);
      } else {
        state.items[index].quantity = quantity;
      }
      localStorage.setItem('cart', JSON.stringify(state.items));
    },

    CLEAR_CART(state) {
      state.items = [];
      localStorage.removeItem('cart');
    }
  },

  actions: {
    addToCart({ commit }, product) {
      commit('ADD_ITEM', product);
      return { success: true, message: 'Product added to cart' };
    },

    removeFromCart({ commit }, index) {
      commit('REMOVE_ITEM', index);
      return { success: true, message: 'Product removed from cart' };
    },

    updateQuantity({ commit }, payload) {
      commit('UPDATE_QUANTITY', payload);
      return { success: true };
    },

    clearCart({ commit }) {
      commit('CLEAR_CART');
      return { success: true, message: 'Cart cleared' };
    }
  }
};
