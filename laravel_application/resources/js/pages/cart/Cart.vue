<template>
  <div class="cart-page container py-5">
    <div class="row">
      <div class="col-lg-8">
        <h2 class="mb-4">{{ $t('cart.title') }}</h2>
        <div v-if="items.length > 0">
          <div v-for="(item, index) in items" :key="index" class="card mb-3">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-md-2">
                  <img :src="item.image || '/images/placeholder.jpg'" :alt="item.name" class="img-fluid rounded" />
                </div>
                <div class="col-md-4">
                  <h5 class="mb-1">{{ currentLocale === 'ar' ? item.name_ar : item.name }}</h5>
                  <p class="text-muted mb-0">{{ $t('products.sku') }}: {{ item.sku }}</p>
                </div>
                <div class="col-md-2">
                  <h6 class="text-primary mb-0">{{ formatMoney(item.price) }}</h6>
                </div>
                <div class="col-md-2">
                  <div class="input-group">
                    <button class="btn btn-outline-secondary" @click="updateQuantity(index, item.quantity - 1)" :disabled="item.quantity <= 1">-</button>
                    <input type="number" class="form-control text-center" v-model.number="item.quantity" @change="updateQuantity(index, item.quantity)" min="1" />
                    <button class="btn btn-outline-secondary" @click="updateQuantity(index, item.quantity + 1)">+</button>
                  </div>
                </div>
                <div class="col-md-2 text-end">
                  <h6 class="text-primary mb-2">{{ formatMoney(item.price * item.quantity) }}</h6>
                  <button class="btn btn-sm btn-outline-danger" @click="removeItem(index)">
                    <i class="fas fa-trash"></i> {{ $t('cart.remove') }}
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="text-start mt-3">
            <router-link to="/products" class="btn btn-outline-primary">
              <i class="fas fa-arrow-left me-2"></i>{{ $t('cart.continue_shopping') }}
            </router-link>
          </div>
        </div>
        <div v-else class="text-center py-5">
          <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
          <h4>{{ $t('cart.empty') }}</h4>
          <p class="text-muted">{{ $t('cart.empty_message') }}</p>
          <router-link to="/products" class="btn btn-primary">{{ $t('cart.start_shopping') }}</router-link>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title mb-4">{{ $t('cart.summary') }}</h4>
            <div class="d-flex justify-content-between mb-2">
              <span>{{ $t('cart.subtotal') }}</span>
              <strong>{{ formatMoney(subtotal) }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>{{ $t('cart.shipping') }}</span>
              <strong>{{ shippingCost > 0 ? formatMoney(shippingCost) : $t('cart.free') }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>{{ $t('cart.tax') }}</span>
              <strong>{{ formatMoney(tax) }}</strong>
            </div>
            <hr />
            <div class="d-flex justify-content-between mb-4">
              <h5>{{ $t('cart.total') }}</h5>
              <h5 class="text-primary">{{ formatMoney(total) }}</h5>
            </div>
            <div class="mb-3">
              <input v-model="couponCode" type="text" class="form-control" :placeholder="$t('cart.coupon_code')" />
              <button @click="applyCoupon" class="btn btn-outline-secondary w-100 mt-2" :disabled="!couponCode">{{ $t('cart.apply_coupon') }}</button>
            </div>
            <button @click="proceedToCheckout" class="btn btn-primary w-100 btn-lg" :disabled="items.length === 0">
              <i class="fas fa-lock me-2"></i>{{ $t('cart.proceed_to_checkout') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, ref } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

export default {
  name: 'Cart',
  setup() {
    const store = useStore();
    const router = useRouter();
    const { t, locale } = useI18n();

    const items = computed(() => store.state.cart.items);
    const subtotal = computed(() => store.getters['cart/subtotal']);
    const shippingCost = computed(() => store.state.cart.shippingCost);
    const tax = computed(() => store.getters['cart/tax']);
    const total = computed(() => store.getters['cart/total']);
    const currentLocale = computed(() => locale.value);

    const couponCode = ref('');

    const formatMoney = (amount) => {
      return new Intl.NumberFormat(locale.value, {
        style: 'currency',
        currency: 'SDG',
        minimumFractionDigits: 2
      }).format(amount);
    };

    const updateQuantity = (index, quantity) => {
      if (quantity >= 1) {
        store.commit('cart/UPDATE_QUANTITY', { index, quantity });
      }
    };

    const removeItem = (index) => {
      if (confirm(t('cart.confirm_remove'))) {
        store.commit('cart/REMOVE_ITEM', index);
      }
    };

    const applyCoupon = async () => {
      const result = await store.dispatch('cart/applyCoupon', couponCode.value);
      if (result.success) {
        alert(t('cart.coupon_applied'));
      } else {
        alert(t('cart.invalid_coupon'));
      }
    };

    const proceedToCheckout = () => {
      if (store.getters['auth/isAuthenticated']) {
        router.push('/checkout');
      } else {
        router.push({ path: '/login', query: { redirect: '/checkout' } });
      }
    };

    return {
      items,
      subtotal,
      shippingCost,
      tax,
      total,
      currentLocale,
      couponCode,
      formatMoney,
      updateQuantity,
      removeItem,
      applyCoupon,
      proceedToCheckout
    };
  }
};
</script>

<style scoped>
.cart-page {
  min-height: 70vh;
}

.card {
  border: 1px solid #e0e0e0;
  transition: box-shadow 0.3s;
}

.card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.input-group input[type="number"] {
  max-width: 60px;
}

.input-group input[type="number"]::-webkit-inner-spin-button,
.input-group input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
