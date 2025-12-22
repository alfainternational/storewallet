<template>
  <div class="merchant-dashboard container py-5">
    <h2 class="mb-4">{{ $t('merchant.dashboard') }}</h2>

    <div class="row mb-4">
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card text-white bg-primary">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-white-50">{{ $t('merchant.total_products') }}</h6>
                <h2 class="mb-0">{{ stats.total_products }}</h2>
              </div>
              <i class="fas fa-box fa-3x opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card text-white bg-success">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-white-50">{{ $t('merchant.total_sales') }}</h6>
                <h2 class="mb-0">{{ formatMoney(stats.total_sales) }}</h2>
              </div>
              <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card text-white bg-warning">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-white-50">{{ $t('merchant.pending_orders') }}</h6>
                <h2 class="mb-0">{{ stats.pending_orders }}</h2>
              </div>
              <i class="fas fa-clock fa-3x opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card text-white bg-info">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-white-50">{{ $t('merchant.shop_rating') }}</h6>
                <h2 class="mb-0">{{ stats.rating }} <i class="fas fa-star"></i></h2>
              </div>
              <i class="fas fa-star fa-3x opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $t('merchant.recent_orders') }}</h5>
            <router-link to="/merchant/orders" class="btn btn-sm btn-outline-primary">
              {{ $t('merchant.view_all') }}
            </router-link>
          </div>
          <div class="card-body">
            <div v-if="recentOrders.length > 0" class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>{{ $t('orders.number') }}</th>
                    <th>{{ $t('orders.customer') }}</th>
                    <th>{{ $t('orders.items') }}</th>
                    <th>{{ $t('orders.total') }}</th>
                    <th>{{ $t('orders.status') }}</th>
                    <th>{{ $t('common.actions') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="order in recentOrders" :key="order.id">
                    <td><strong>{{ order.order_number }}</strong></td>
                    <td>{{ order.user?.first_name }} {{ order.user?.last_name }}</td>
                    <td>{{ order.items_count }}</td>
                    <td>{{ formatMoney(order.total) }}</td>
                    <td>
                      <span :class="['badge', getStatusBadge(order.status)]">
                        {{ $t(`orders.status_${order.status}`) }}
                      </span>
                    </td>
                    <td>
                      <router-link :to="`/merchant/orders/${order.id}`" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i>
                      </router-link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="text-center py-5">
              <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
              <p class="text-muted">{{ $t('merchant.no_orders') }}</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">{{ $t('merchant.sales_chart') }}</h5>
          </div>
          <div class="card-body">
            <canvas ref="salesChart"></canvas>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">{{ $t('merchant.shop_info') }}</h5>
          </div>
          <div class="card-body">
            <div class="text-center mb-3">
              <img :src="merchant?.shop_logo || '/images/merchant-placeholder.png'" :alt="merchant?.shop_name" class="rounded-circle mb-2" style="width: 100px; height: 100px; object-fit: cover;" />
              <h5>{{ currentLocale === 'ar' ? merchant?.shop_name_ar : merchant?.shop_name }}</h5>
              <div class="rating mb-2">
                <i v-for="i in 5" :key="i" :class="['fas fa-star', i <= merchant?.rating ? 'text-warning' : 'text-muted']"></i>
              </div>
              <span :class="['badge', merchant?.is_verified ? 'bg-success' : 'bg-warning']">
                {{ merchant?.is_verified ? $t('merchant.verified') : $t('merchant.pending_verification') }}
              </span>
            </div>
            <hr />
            <router-link to="/merchant/shop" class="btn btn-outline-primary w-100 mb-2">
              <i class="fas fa-store me-2"></i>{{ $t('merchant.edit_shop') }}
            </router-link>
            <router-link to="/merchant/products" class="btn btn-outline-success w-100 mb-2">
              <i class="fas fa-box me-2"></i>{{ $t('merchant.manage_products') }}
            </router-link>
            <router-link to="/merchant/auctions" class="btn btn-outline-warning w-100">
              <i class="fas fa-gavel me-2"></i>{{ $t('merchant.manage_auctions') }}
            </router-link>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">{{ $t('merchant.quick_actions') }}</h5>
          </div>
          <div class="list-group list-group-flush">
            <router-link to="/merchant/products/create" class="list-group-item list-group-item-action">
              <i class="fas fa-plus-circle me-2 text-success"></i>{{ $t('merchant.add_product') }}
            </router-link>
            <router-link to="/merchant/auctions/create" class="list-group-item list-group-item-action">
              <i class="fas fa-gavel me-2 text-warning"></i>{{ $t('merchant.create_auction') }}
            </router-link>
            <router-link to="/merchant/reports" class="list-group-item list-group-item-action">
              <i class="fas fa-chart-bar me-2 text-info"></i>{{ $t('merchant.view_reports') }}
            </router-link>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">{{ $t('merchant.low_stock_alert') }}</h5>
          </div>
          <div class="card-body">
            <div v-if="lowStockProducts.length > 0">
              <div v-for="product in lowStockProducts" :key="product.id" class="d-flex align-items-center mb-3 pb-3 border-bottom">
                <img :src="product.main_image" :alt="product.name" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" />
                <div class="flex-grow-1">
                  <h6 class="mb-0 small">{{ product.name }}</h6>
                  <small class="text-danger">{{ $t('merchant.stock') }}: {{ product.stock }}</small>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-3">
              <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
              <p class="mb-0 small">{{ $t('merchant.all_stocked') }}</p>
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
import { useI18n } from 'vue-i18n';

export default {
  name: 'MerchantDashboard',
  setup() {
    const store = useStore();
    const { t, locale } = useI18n();

    const stats = ref({
      total_products: 0,
      total_sales: 0,
      pending_orders: 0,
      rating: 0
    });
    const recentOrders = ref([]);
    const lowStockProducts = ref([]);
    const salesChart = ref(null);

    const merchant = computed(() => store.state.auth.user?.merchant);
    const currentLocale = computed(() => locale.value);

    const formatMoney = (amount) => {
      return new Intl.NumberFormat(locale.value, {
        style: 'currency',
        currency: 'SDG',
        minimumFractionDigits: 2
      }).format(amount);
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

    const fetchDashboardData = async () => {
      try {
        const response = await window.axios.get('/merchant/dashboard');
        stats.value = response.data.stats;
        recentOrders.value = response.data.recent_orders || [];
        lowStockProducts.value = response.data.low_stock_products || [];
      } catch (error) {
        console.error('Error fetching merchant dashboard data:', error);
      }
    };

    onMounted(() => {
      fetchDashboardData();
    });

    return {
      stats,
      recentOrders,
      lowStockProducts,
      salesChart,
      merchant,
      currentLocale,
      formatMoney,
      getStatusBadge
    };
  }
};
</script>

<style scoped>
.stat-card {
  border: none;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  transition: transform 0.3s;
}

.stat-card:hover {
  transform: translateY(-4px);
}

.opacity-50 {
  opacity: 0.5;
}
</style>
