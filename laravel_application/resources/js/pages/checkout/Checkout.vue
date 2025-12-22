<template>
  <div class="checkout-page container py-5">
    <h2 class="mb-4">{{ $t('checkout.title') }}</h2>

    <div class="row">
      <div class="col-lg-8">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="fas fa-shipping-fast me-2"></i>{{ $t('checkout.shipping_info') }}
            </h5>
          </div>
          <div class="card-body">
            <form>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('auth.first_name') }}</label>
                  <input v-model="shippingInfo.first_name" type="text" class="form-control" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('auth.last_name') }}</label>
                  <input v-model="shippingInfo.last_name" type="text" class="form-control" required />
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('auth.phone') }}</label>
                <input v-model="shippingInfo.phone" type="tel" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('checkout.address') }}</label>
                <textarea v-model="shippingInfo.address" class="form-control" rows="3" required></textarea>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('checkout.city') }}</label>
                  <select v-model="shippingInfo.city_id" class="form-select" required>
                    <option value="">{{ $t('checkout.select_city') }}</option>
                    <option v-for="city in cities" :key="city.id" :value="city.id">
                      {{ currentLocale === 'ar' ? city.name_ar : city.name }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('checkout.postal_code') }}</label>
                  <input v-model="shippingInfo.postal_code" type="text" class="form-control" />
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('checkout.notes') }}</label>
                <textarea v-model="shippingInfo.notes" class="form-control" rows="2" :placeholder="$t('checkout.notes_placeholder')"></textarea>
              </div>
            </form>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="fas fa-credit-card me-2"></i>{{ $t('checkout.payment_method') }}
            </h5>
          </div>
          <div class="card-body">
            <div class="payment-methods">
              <div class="form-check mb-3">
                <input v-model="paymentMethod" class="form-check-input" type="radio" value="wallet" id="payment-wallet" />
                <label class="form-check-label d-flex justify-content-between align-items-center" for="payment-wallet">
                  <span>
                    <i class="fas fa-wallet me-2 text-primary"></i>{{ $t('checkout.wallet') }}
                  </span>
                  <span class="badge bg-primary">{{ formatMoney(walletBalance) }}</span>
                </label>
              </div>
              <div class="form-check mb-3">
                <input v-model="paymentMethod" class="form-check-input" type="radio" value="xcash" id="payment-xcash" />
                <label class="form-check-label" for="payment-xcash">
                  <i class="fas fa-mobile-alt me-2 text-success"></i>xCash
                </label>
              </div>
              <div class="form-check mb-3">
                <input v-model="paymentMethod" class="form-check-input" type="radio" value="bankak" id="payment-bankak" />
                <label class="form-check-label" for="payment-bankak">
                  <i class="fas fa-university me-2 text-info"></i>Bankak
                </label>
              </div>
              <div class="form-check mb-3">
                <input v-model="paymentMethod" class="form-check-input" type="radio" value="e15" id="payment-e15" />
                <label class="form-check-label" for="payment-e15">
                  <i class="fas fa-money-bill-wave me-2 text-warning"></i>E15
                </label>
              </div>
              <div class="form-check mb-3">
                <input v-model="paymentMethod" class="form-check-input" type="radio" value="sudanipay" id="payment-sudanipay" />
                <label class="form-check-label" for="payment-sudanipay">
                  <i class="fas fa-credit-card me-2 text-danger"></i>SudaniPay
                </label>
              </div>
              <div class="form-check mb-3">
                <input v-model="paymentMethod" class="form-check-input" type="radio" value="stripe" id="payment-stripe" />
                <label class="form-check-label" for="payment-stripe">
                  <i class="fab fa-stripe me-2 text-primary"></i>Stripe
                </label>
              </div>
              <div class="form-check mb-3">
                <input v-model="paymentMethod" class="form-check-input" type="radio" value="cod" id="payment-cod" />
                <label class="form-check-label" for="payment-cod">
                  <i class="fas fa-money-bill me-2 text-secondary"></i>{{ $t('checkout.cash_on_delivery') }}
                </label>
              </div>
            </div>

            <div v-if="paymentMethod === 'wallet' && walletBalance < total" class="alert alert-warning mt-3">
              <i class="fas fa-exclamation-triangle me-2"></i>{{ $t('checkout.insufficient_balance') }}
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="fas fa-box me-2"></i>{{ $t('checkout.order_items') }}
            </h5>
          </div>
          <div class="card-body">
            <div v-for="(item, index) in cartItems" :key="index" class="d-flex align-items-center mb-3 pb-3 border-bottom">
              <img :src="item.image || '/images/placeholder.jpg'" :alt="item.name" class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;" />
              <div class="flex-grow-1">
                <h6 class="mb-1">{{ currentLocale === 'ar' ? item.name_ar : item.name }}</h6>
                <p class="text-muted mb-0">{{ $t('products.quantity') }}: {{ item.quantity }}</p>
              </div>
              <div class="text-end">
                <h6 class="text-primary mb-0">{{ formatMoney(item.price * item.quantity) }}</h6>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
          <div class="card-body">
            <h5 class="card-title mb-4">{{ $t('checkout.order_summary') }}</h5>

            <div class="d-flex justify-content-between mb-2">
              <span>{{ $t('checkout.subtotal') }}</span>
              <strong>{{ formatMoney(subtotal) }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>{{ $t('checkout.shipping') }}</span>
              <strong>{{ formatMoney(shippingCost) }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>{{ $t('checkout.tax') }}</span>
              <strong>{{ formatMoney(tax) }}</strong>
            </div>
            <div v-if="discount > 0" class="d-flex justify-content-between mb-2 text-success">
              <span>{{ $t('checkout.discount') }}</span>
              <strong>-{{ formatMoney(discount) }}</strong>
            </div>
            <hr />
            <div class="d-flex justify-content-between mb-4">
              <h5>{{ $t('checkout.total') }}</h5>
              <h5 class="text-primary">{{ formatMoney(total) }}</h5>
            </div>

            <div class="mb-3">
              <div class="input-group">
                <input v-model="couponCode" type="text" class="form-control" :placeholder="$t('checkout.coupon_code')" />
                <button @click="applyCoupon" class="btn btn-outline-secondary" :disabled="!couponCode || couponLoading">
                  {{ couponLoading ? $t('common.loading') : $t('checkout.apply') }}
                </button>
              </div>
            </div>

            <div class="d-grid gap-2">
              <button @click="placeOrder" class="btn btn-primary btn-lg" :disabled="!isValid || orderLoading">
                <i class="fas fa-lock me-2"></i>
                {{ orderLoading ? $t('checkout.processing') : $t('checkout.place_order') }}
              </button>
              <router-link to="/cart" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>{{ $t('checkout.back_to_cart') }}
              </router-link>
            </div>

            <div class="alert alert-info mt-3">
              <small>
                <i class="fas fa-shield-alt me-2"></i>
                {{ $t('checkout.secure_payment_note') }}
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

export default {
  name: 'Checkout',
  setup() {
    const store = useStore();
    const router = useRouter();
    const { t, locale } = useI18n();

    const shippingInfo = ref({
      first_name: '',
      last_name: '',
      phone: '',
      address: '',
      city_id: '',
      postal_code: '',
      notes: ''
    });

    const paymentMethod = ref('wallet');
    const couponCode = ref('');
    const couponLoading = ref(false);
    const orderLoading = ref(false);
    const cities = ref([]);

    const cartItems = computed(() => store.state.cart.items);
    const subtotal = computed(() => store.getters['cart/subtotal']);
    const shippingCost = computed(() => store.state.cart.shippingCost || 0);
    const tax = computed(() => store.getters['cart/tax']);
    const discount = computed(() => store.state.cart.discount || 0);
    const total = computed(() => store.getters['cart/total']);
    const walletBalance = computed(() => store.state.wallet.balance || 0);
    const currentLocale = computed(() => locale.value);
    const user = computed(() => store.state.auth.user);

    const isValid = computed(() => {
      return shippingInfo.value.first_name &&
             shippingInfo.value.last_name &&
             shippingInfo.value.phone &&
             shippingInfo.value.address &&
             shippingInfo.value.city_id &&
             paymentMethod.value &&
             cartItems.value.length > 0 &&
             !(paymentMethod.value === 'wallet' && walletBalance.value < total.value);
    });

    const formatMoney = (amount) => {
      return new Intl.NumberFormat(locale.value, {
        style: 'currency',
        currency: 'SDG',
        minimumFractionDigits: 2
      }).format(amount);
    };

    const fetchCities = async () => {
      try {
        const response = await window.axios.get('/cities');
        cities.value = response.data.cities;
      } catch (error) {
        console.error('Error fetching cities:', error);
      }
    };

    const applyCoupon = async () => {
      couponLoading.value = true;
      const result = await store.dispatch('cart/applyCoupon', couponCode.value);
      couponLoading.value = false;
      if (result.success) {
        alert(t('checkout.coupon_applied'));
      } else {
        alert(t('checkout.invalid_coupon'));
      }
    };

    const placeOrder = async () => {
      if (!isValid.value) return;

      orderLoading.value = true;
      try {
        const orderData = {
          shipping_info: shippingInfo.value,
          payment_method: paymentMethod.value,
          items: cartItems.value,
          coupon_code: couponCode.value
        };

        const response = await window.axios.post('/orders', orderData);

        if (response.data.success) {
          store.commit('cart/CLEAR');
          alert(t('checkout.order_success'));
          router.push(`/orders/${response.data.order.id}`);
        }
      } catch (error) {
        console.error('Error placing order:', error);
        alert(error.response?.data?.message || t('checkout.order_failed'));
      }
      orderLoading.value = false;
    };

    onMounted(() => {
      if (cartItems.value.length === 0) {
        router.push('/cart');
        return;
      }

      fetchCities();
      store.dispatch('wallet/fetchBalance');

      if (user.value) {
        shippingInfo.value = {
          first_name: user.value.first_name || '',
          last_name: user.value.last_name || '',
          phone: user.value.phone || '',
          address: user.value.address || '',
          city_id: user.value.city_id || '',
          postal_code: '',
          notes: ''
        };
      }
    });

    return {
      shippingInfo,
      paymentMethod,
      couponCode,
      couponLoading,
      orderLoading,
      cities,
      cartItems,
      subtotal,
      shippingCost,
      tax,
      discount,
      total,
      walletBalance,
      currentLocale,
      isValid,
      formatMoney,
      applyCoupon,
      placeOrder
    };
  }
};
</script>

<style scoped>
.form-check-label {
  width: 100%;
  cursor: pointer;
  padding: 10px;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  transition: all 0.3s;
}

.form-check-input:checked + .form-check-label {
  background-color: #f0f7ff;
  border-color: #2563eb;
}
</style>
