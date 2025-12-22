<template>
  <div class="order-detail-page container py-5" v-if="order">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link to="/dashboard">{{ $t('nav.dashboard') }}</router-link></li>
        <li class="breadcrumb-item"><router-link to="/orders">{{ $t('nav.orders') }}</router-link></li>
        <li class="breadcrumb-item active">{{ order.order_number }}</li>
      </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>{{ $t('orders.order') }} #{{ order.order_number }}</h2>
      <span :class="['badge badge-lg', getStatusBadge(order.status)]">
        {{ $t(`orders.status_${order.status}`) }}
      </span>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">{{ $t('orders.order_items') }}</h5>
          </div>
          <div class="card-body">
            <div v-for="item in order.items" :key="item.id" class="order-item-row d-flex align-items-center mb-3 pb-3 border-bottom">
              <img :src="item.product?.main_image || '/images/placeholder.jpg'" :alt="item.product?.name" class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;" />
              <div class="flex-grow-1">
                <h6 class="mb-1">{{ currentLocale === 'ar' ? item.product?.name_ar : item.product?.name }}</h6>
                <p class="text-muted mb-0">{{ $t('products.quantity') }}: {{ item.quantity }}</p>
                <small class="text-muted">{{ $t('products.sku') }}: {{ item.sku }}</small>
              </div>
              <div class="text-end">
                <p class="mb-0">{{ formatMoney(item.price) }} × {{ item.quantity }}</p>
                <h6 class="text-primary mb-0">{{ formatMoney(item.price * item.quantity) }}</h6>
              </div>
            </div>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">{{ $t('orders.order_timeline') }}</h5>
          </div>
          <div class="card-body">
            <div class="timeline">
              <div :class="['timeline-item', order.status !== 'cancelled' && 'active']">
                <div class="timeline-marker"><i class="fas fa-check"></i></div>
                <div class="timeline-content">
                  <h6>{{ $t('orders.order_placed') }}</h6>
                  <small class="text-muted">{{ formatDateTime(order.created_at) }}</small>
                </div>
              </div>
              <div :class="['timeline-item', ['processing', 'shipped', 'delivered'].includes(order.status) && 'active']">
                <div class="timeline-marker"><i class="fas fa-box"></i></div>
                <div class="timeline-content">
                  <h6>{{ $t('orders.order_processing') }}</h6>
                  <small v-if="order.updated_at" class="text-muted">{{ formatDateTime(order.updated_at) }}</small>
                </div>
              </div>
              <div :class="['timeline-item', ['shipped', 'delivered'].includes(order.status) && 'active']">
                <div class="timeline-marker"><i class="fas fa-truck"></i></div>
                <div class="timeline-content">
                  <h6>{{ $t('orders.order_shipped') }}</h6>
                  <small v-if="order.shipped_at" class="text-muted">{{ formatDateTime(order.shipped_at) }}</small>
                  <p v-if="order.tracking_number" class="mb-0">
                    <strong>{{ $t('orders.tracking') }}:</strong> {{ order.tracking_number }}
                  </p>
                </div>
              </div>
              <div :class="['timeline-item', order.status === 'delivered' && 'active']">
                <div class="timeline-marker"><i class="fas fa-check-circle"></i></div>
                <div class="timeline-content">
                  <h6>{{ $t('orders.order_delivered') }}</h6>
                  <small v-if="order.delivered_at" class="text-muted">{{ formatDateTime(order.delivered_at) }}</small>
                </div>
              </div>
              <div v-if="order.status === 'cancelled'" class="timeline-item active text-danger">
                <div class="timeline-marker bg-danger"><i class="fas fa-times"></i></div>
                <div class="timeline-content">
                  <h6>{{ $t('orders.order_cancelled') }}</h6>
                  <small v-if="order.cancelled_at" class="text-muted">{{ formatDateTime(order.cancelled_at) }}</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card" v-if="order.notes">
          <div class="card-header">
            <h5 class="mb-0">{{ $t('orders.notes') }}</h5>
          </div>
          <div class="card-body">
            <p class="mb-0">{{ order.notes }}</p>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">{{ $t('orders.order_summary') }}</h5>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
              <span>{{ $t('orders.subtotal') }}</span>
              <strong>{{ formatMoney(order.subtotal) }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>{{ $t('orders.shipping') }}</span>
              <strong>{{ formatMoney(order.shipping_cost) }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>{{ $t('orders.tax') }}</span>
              <strong>{{ formatMoney(order.tax) }}</strong>
            </div>
            <div v-if="order.discount > 0" class="d-flex justify-content-between mb-2 text-success">
              <span>{{ $t('orders.discount') }}</span>
              <strong>-{{ formatMoney(order.discount) }}</strong>
            </div>
            <hr />
            <div class="d-flex justify-content-between mb-3">
              <h5>{{ $t('orders.total') }}</h5>
              <h5 class="text-primary">{{ formatMoney(order.total) }}</h5>
            </div>
            <div class="mb-3">
              <small class="text-muted">{{ $t('orders.payment_method') }}</small>
              <p class="mb-0"><strong>{{ getPaymentMethod(order.payment_method) }}</strong></p>
              <span :class="['badge mt-1', order.payment_status === 'paid' ? 'bg-success' : 'bg-warning']">
                {{ $t(`orders.payment_${order.payment_status}`) }}
              </span>
            </div>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">{{ $t('orders.shipping_address') }}</h5>
          </div>
          <div class="card-body">
            <p class="mb-1"><strong>{{ order.shipping_address }}</strong></p>
            <p class="mb-1">{{ order.shipping_city?.name }}</p>
            <p class="mb-0"><i class="fas fa-phone me-2"></i>{{ order.shipping_phone }}</p>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <div class="d-grid gap-2">
              <button v-if="order.tracking_number" @click="trackOrder" class="btn btn-info">
                <i class="fas fa-map-marker-alt me-2"></i>{{ $t('orders.track_order') }}
              </button>
              <button v-if="order.status === 'pending'" @click="cancelOrder" class="btn btn-danger">
                <i class="fas fa-times me-2"></i>{{ $t('orders.cancel_order') }}
              </button>
              <button v-if="order.status === 'delivered' && !order.is_reviewed" @click="reviewOrder" class="btn btn-warning">
                <i class="fas fa-star me-2"></i>{{ $t('orders.write_review') }}
              </button>
              <a :href="`/api/orders/${order.id}/invoice`" target="_blank" class="btn btn-outline-primary">
                <i class="fas fa-file-invoice me-2"></i>{{ $t('orders.download_invoice') }}
              </a>
              <router-link to="/orders" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>{{ $t('orders.back_to_orders') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div v-else class="container py-5">
    <div class="text-center">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">{{ $t('common.loading') }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

export default {
  name: 'OrderDetail',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const { t, locale } = useI18n();

    const order = ref(null);
    const currentLocale = computed(() => locale.value);

    const formatMoney = (amount) => {
      return new Intl.NumberFormat(locale.value, {
        style: 'currency',
        currency: 'SDG',
        minimumFractionDigits: 2
      }).format(amount);
    };

    const formatDateTime = (date) => {
      return new Date(date).toLocaleString(locale.value, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    const getStatusBadge = (status) => {
      const badges = {
        pending: 'bg-warning',
        processing: 'bg-info',
        shipped: 'bg-primary',
        delivered: 'bg-success',
        cancelled: 'bg-danger'
      };
      return badges[status] || 'bg-secondary';
    };

    const getPaymentMethod = (method) => {
      const methods = {
        wallet: t('checkout.wallet'),
        xcash: 'xCash',
        bankak: 'Bankak',
        e15: 'E15',
        sudanipay: 'SudaniPay',
        stripe: 'Stripe',
        cod: t('checkout.cash_on_delivery')
      };
      return methods[method] || method;
    };

    const fetchOrder = async () => {
      try {
        const response = await window.axios.get(`/orders/${route.params.id}`);
        order.value = response.data.order;
      } catch (error) {
        console.error('Error fetching order:', error);
        alert(t('errors.order_not_found'));
        router.push('/orders');
      }
    };

    const trackOrder = () => {
      router.push(`/orders/${order.value.id}/track`);
    };

    const cancelOrder = async () => {
      if (!confirm(t('orders.confirm_cancel'))) return;

      try {
        const response = await window.axios.post(`/orders/${order.value.id}/cancel`);
        if (response.data.success) {
          alert(t('orders.cancel_success'));
          fetchOrder();
        }
      } catch (error) {
        console.error('Error cancelling order:', error);
        alert(t('orders.cancel_failed'));
      }
    };

    const reviewOrder = () => {
      router.push(`/orders/${order.value.id}/review`);
    };

    onMounted(() => {
      fetchOrder();
    });

    return {
      order,
      currentLocale,
      formatMoney,
      formatDateTime,
      getStatusBadge,
      getPaymentMethod,
      trackOrder,
      cancelOrder,
      reviewOrder
    };
  }
};
</script>

<style scoped>
.badge-lg {
  font-size: 1rem;
  padding: 0.5rem 1rem;
}

.order-item-row:last-child {
  border-bottom: none !important;
}

.timeline {
  position: relative;
  padding-left: 40px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 15px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #e0e0e0;
}

.timeline-item {
  position: relative;
  padding-bottom: 30px;
  opacity: 0.5;
}

.timeline-item.active {
  opacity: 1;
}

.timeline-marker {
  position: absolute;
  left: -32px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #e0e0e0;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
}

.timeline-item.active .timeline-marker {
  background: #2563eb;
}

.timeline-content h6 {
  margin-bottom: 5px;
}
</style>
