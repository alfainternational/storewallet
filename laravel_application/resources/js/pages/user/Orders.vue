<template>
  <div class="orders-page container py-5">
    <h2 class="mb-4">{{ $t('orders.my_orders') }}</h2>

    <div class="card">
      <div class="card-header">
        <div class="row align-items-center">
          <div class="col-md-6">
            <div class="input-group">
              <input v-model="searchQuery" @input="handleSearch" type="text" class="form-control" :placeholder="$t('orders.search_placeholder')" />
              <button class="btn btn-primary" @click="handleSearch">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>
          <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="btn-group" role="group">
              <button @click="filterOrders('all')" :class="['btn btn-sm', filter === 'all' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('orders.all') }}</button>
              <button @click="filterOrders('pending')" :class="['btn btn-sm', filter === 'pending' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('orders.pending') }}</button>
              <button @click="filterOrders('processing')" :class="['btn btn-sm', filter === 'processing' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('orders.processing') }}</button>
              <button @click="filterOrders('shipped')" :class="['btn btn-sm', filter === 'shipped' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('orders.shipped') }}</button>
              <button @click="filterOrders('delivered')" :class="['btn btn-sm', filter === 'delivered' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('orders.delivered') }}</button>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ $t('common.loading') }}</span>
          </div>
        </div>

        <div v-else-if="filteredOrders.length > 0">
          <div v-for="order in filteredOrders" :key="order.id" class="order-item card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div>
                <strong>{{ $t('orders.order') }} #{{ order.order_number }}</strong>
                <span class="text-muted ms-3">{{ formatDate(order.created_at) }}</span>
              </div>
              <span :class="['badge', getStatusBadge(order.status)]">
                {{ $t(`orders.status_${order.status}`) }}
              </span>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-8">
                  <div v-for="item in order.items?.slice(0, 3)" :key="item.id" class="d-flex align-items-center mb-2">
                    <img :src="item.product?.main_image || '/images/placeholder.jpg'" :alt="item.product?.name" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;" />
                    <div class="flex-grow-1">
                      <h6 class="mb-0">{{ item.product?.name }}</h6>
                      <small class="text-muted">{{ $t('products.quantity') }}: {{ item.quantity }} × {{ formatMoney(item.price) }}</small>
                    </div>
                  </div>
                  <small v-if="order.items?.length > 3" class="text-muted">
                    + {{ order.items.length - 3 }} {{ $t('orders.more_items') }}
                  </small>
                </div>
                <div class="col-md-4 text-md-end">
                  <h5 class="text-primary mb-3">{{ formatMoney(order.total) }}</h5>
                  <div class="d-grid gap-2">
                    <router-link :to="`/orders/${order.id}`" class="btn btn-sm btn-primary">
                      <i class="fas fa-eye me-2"></i>{{ $t('orders.view_details') }}
                    </router-link>
                    <button v-if="order.status === 'delivered' && !order.is_reviewed" @click="reviewOrder(order.id)" class="btn btn-sm btn-outline-warning">
                      <i class="fas fa-star me-2"></i>{{ $t('orders.write_review') }}
                    </button>
                    <button v-if="order.status === 'pending'" @click="cancelOrder(order.id)" class="btn btn-sm btn-outline-danger">
                      <i class="fas fa-times me-2"></i>{{ $t('orders.cancel') }}
                    </button>
                    <button v-if="order.tracking_number" @click="trackOrder(order.id)" class="btn btn-sm btn-outline-info">
                      <i class="fas fa-map-marker-alt me-2"></i>{{ $t('orders.track') }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <nav v-if="totalPages > 1" class="mt-4">
            <ul class="pagination justify-content-center">
              <li :class="['page-item', currentPage === 1 && 'disabled']">
                <button class="page-link" @click="changePage(currentPage - 1)">{{ $t('common.previous') }}</button>
              </li>
              <li v-for="page in totalPages" :key="page" :class="['page-item', currentPage === page && 'active']">
                <button class="page-link" @click="changePage(page)">{{ page }}</button>
              </li>
              <li :class="['page-item', currentPage === totalPages && 'disabled']">
                <button class="page-link" @click="changePage(currentPage + 1)">{{ $t('common.next') }}</button>
              </li>
            </ul>
          </nav>
        </div>

        <div v-else class="text-center py-5">
          <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
          <h4>{{ $t('orders.no_orders') }}</h4>
          <p class="text-muted">{{ $t('orders.no_orders_message') }}</p>
          <router-link to="/products" class="btn btn-primary">{{ $t('orders.start_shopping') }}</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

export default {
  name: 'Orders',
  setup() {
    const router = useRouter();
    const { t, locale } = useI18n();

    const orders = ref([]);
    const loading = ref(false);
    const searchQuery = ref('');
    const filter = ref('all');
    const currentPage = ref(1);
    const totalPages = ref(1);
    const perPage = 10;

    const filteredOrders = computed(() => {
      let result = orders.value;

      if (searchQuery.value) {
        result = result.filter(order =>
          order.order_number.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
      }

      if (filter.value !== 'all') {
        result = result.filter(order => order.status === filter.value);
      }

      return result;
    });

    const formatMoney = (amount) => {
      return new Intl.NumberFormat(locale.value, {
        style: 'currency',
        currency: 'SDG',
        minimumFractionDigits: 2
      }).format(amount);
    };

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString(locale.value, {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
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

    const fetchOrders = async () => {
      loading.value = true;
      try {
        const response = await window.axios.get('/orders', {
          params: { page: currentPage.value, per_page: perPage }
        });
        orders.value = response.data.orders;
        totalPages.value = response.data.total_pages || 1;
      } catch (error) {
        console.error('Error fetching orders:', error);
      }
      loading.value = false;
    };

    const handleSearch = () => {
      currentPage.value = 1;
    };

    const filterOrders = (newFilter) => {
      filter.value = newFilter;
      currentPage.value = 1;
    };

    const changePage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        fetchOrders();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    };

    const reviewOrder = (orderId) => {
      router.push(`/orders/${orderId}/review`);
    };

    const cancelOrder = async (orderId) => {
      if (!confirm(t('orders.confirm_cancel'))) return;

      try {
        const response = await window.axios.post(`/orders/${orderId}/cancel`);
        if (response.data.success) {
          alert(t('orders.cancel_success'));
          fetchOrders();
        }
      } catch (error) {
        console.error('Error cancelling order:', error);
        alert(t('orders.cancel_failed'));
      }
    };

    const trackOrder = (orderId) => {
      router.push(`/orders/${orderId}/track`);
    };

    onMounted(() => {
      fetchOrders();
    });

    return {
      orders,
      loading,
      searchQuery,
      filter,
      currentPage,
      totalPages,
      filteredOrders,
      formatMoney,
      formatDate,
      getStatusBadge,
      handleSearch,
      filterOrders,
      changePage,
      reviewOrder,
      cancelOrder,
      trackOrder
    };
  }
};
</script>

<style scoped>
.order-item {
  border: 1px solid #e0e0e0;
  transition: box-shadow 0.3s;
}

.order-item:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>
