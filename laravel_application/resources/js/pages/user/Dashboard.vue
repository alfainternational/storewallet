<template>
  <div class="dashboard-page container py-5">
    <h2 class="mb-4">{{ $t('dashboard.title') }}</h2>

    <div class="row mb-4">
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card text-white bg-primary">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-white-50">{{ $t('dashboard.total_orders') }}</h6>
                <h2 class="mb-0">{{ stats.total_orders }}</h2>
              </div>
              <i class="fas fa-shopping-bag fa-3x opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card text-white bg-success">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-white-50">{{ $t('dashboard.total_spent') }}</h6>
                <h2 class="mb-0">{{ formatMoney(stats.total_spent) }}</h2>
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
                <h6 class="text-white-50">{{ $t('dashboard.wallet_balance') }}</h6>
                <h2 class="mb-0">{{ formatMoney(walletBalance) }}</h2>
              </div>
              <i class="fas fa-wallet fa-3x opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card text-white bg-info">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-white-50">{{ $t('dashboard.active_auctions') }}</h6>
                <h2 class="mb-0">{{ stats.active_auctions }}</h2>
              </div>
              <i class="fas fa-gavel fa-3x opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $t('dashboard.recent_orders') }}</h5>
            <router-link to="/orders" class="btn btn-sm btn-outline-primary">{{ $t('dashboard.view_all') }}</router-link>
          </div>
          <div class="card-body">
            <div v-if="recentOrders.length > 0" class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>{{ $t('orders.number') }}</th>
                    <th>{{ $t('orders.date') }}</th>
                    <th>{{ $t('orders.total') }}</th>
                    <th>{{ $t('orders.status') }}</th>
                    <th>{{ $t('common.actions') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="order in recentOrders" :key="order.id">
                    <td><strong>{{ order.order_number }}</strong></td>
                    <td>{{ formatDate(order.created_at) }}</td>
                    <td>{{ formatMoney(order.total) }}</td>
                    <td>
                      <span :class="['badge', getStatusBadge(order.status)]">
                        {{ $t(`orders.status_${order.status}`) }}
                      </span>
                    </td>
                    <td>
                      <router-link :to="`/orders/${order.id}`" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i>
                      </router-link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="text-center py-5">
              <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
              <p class="text-muted">{{ $t('dashboard.no_orders') }}</p>
              <router-link to="/products" class="btn btn-primary">{{ $t('dashboard.start_shopping') }}</router-link>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">{{ $t('dashboard.active_bids') }}</h5>
          </div>
          <div class="card-body">
            <div v-if="activeBids.length > 0">
              <div v-for="bid in activeBids" :key="bid.id" class="d-flex align-items-center mb-3 pb-3 border-bottom">
                <img :src="bid.auction?.product?.main_image || '/images/placeholder.jpg'" :alt="bid.auction?.title" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;" />
                <div class="flex-grow-1">
                  <h6 class="mb-1">{{ bid.auction?.title }}</h6>
                  <small class="text-muted">{{ $t('auctions.your_bid') }}: {{ formatMoney(bid.amount) }}</small>
                </div>
                <router-link :to="`/auctions/${bid.auction_id}`" class="btn btn-sm btn-outline-primary">
                  {{ $t('dashboard.view') }}
                </router-link>
              </div>
            </div>
            <div v-else class="text-center py-5">
              <i class="fas fa-gavel fa-3x text-muted mb-3"></i>
              <p class="text-muted">{{ $t('dashboard.no_bids') }}</p>
              <router-link to="/auctions" class="btn btn-primary">{{ $t('dashboard.browse_auctions') }}</router-link>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card mb-4">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <img :src="user?.avatar || '/images/avatar-placeholder.png'" :alt="user?.first_name" class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;" />
              <div>
                <h5 class="mb-0">{{ user?.first_name }} {{ user?.last_name }}</h5>
                <small class="text-muted">{{ user?.email }}</small>
              </div>
            </div>
            <hr />
            <router-link to="/profile" class="btn btn-outline-primary w-100 mb-2">
              <i class="fas fa-user me-2"></i>{{ $t('dashboard.edit_profile') }}
            </router-link>
            <router-link to="/wallet" class="btn btn-outline-success w-100">
              <i class="fas fa-wallet me-2"></i>{{ $t('dashboard.my_wallet') }}
            </router-link>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header">
            <h6 class="mb-0">{{ $t('dashboard.quick_links') }}</h6>
          </div>
          <div class="list-group list-group-flush">
            <router-link to="/orders" class="list-group-item list-group-item-action">
              <i class="fas fa-shopping-bag me-2 text-primary"></i>{{ $t('dashboard.my_orders') }}
            </router-link>
            <router-link to="/wishlist" class="list-group-item list-group-item-action">
              <i class="fas fa-heart me-2 text-danger"></i>{{ $t('dashboard.wishlist') }}
            </router-link>
            <router-link to="/remittances" class="list-group-item list-group-item-action">
              <i class="fas fa-exchange-alt me-2 text-success"></i>{{ $t('dashboard.remittances') }}
            </router-link>
            <router-link to="/auctions" class="list-group-item list-group-item-action">
              <i class="fas fa-gavel me-2 text-warning"></i>{{ $t('dashboard.auctions') }}
            </router-link>
            <router-link to="/reviews" class="list-group-item list-group-item-action">
              <i class="fas fa-star me-2 text-info"></i>{{ $t('dashboard.my_reviews') }}
            </router-link>
          </div>
        </div>

        <div class="card" v-if="user?.is_expatriate">
          <div class="card-header bg-gradient-primary text-white">
            <h6 class="mb-0">
              <i class="fas fa-globe me-2"></i>{{ $t('dashboard.expatriate_services') }}
            </h6>
          </div>
          <div class="list-group list-group-flush">
            <router-link to="/remittances/create" class="list-group-item list-group-item-action">
              <i class="fas fa-paper-plane me-2 text-primary"></i>{{ $t('remittances.send_money') }}
            </router-link>
            <router-link to="/support" class="list-group-item list-group-item-action">
              <i class="fas fa-headset me-2 text-success"></i>{{ $t('dashboard.support') }}
            </router-link>
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
  name: 'Dashboard',
  setup() {
    const store = useStore();
    const { t, locale } = useI18n();

    const stats = ref({
      total_orders: 0,
      total_spent: 0,
      active_auctions: 0
    });
    const recentOrders = ref([]);
    const activeBids = ref([]);

    const user = computed(() => store.state.auth.user);
    const walletBalance = computed(() => store.state.wallet.balance || 0);

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

    const fetchDashboardData = async () => {
      try {
        const response = await window.axios.get('/user/dashboard');
        stats.value = response.data.stats;
        recentOrders.value = response.data.recent_orders || [];
        activeBids.value = response.data.active_bids || [];
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      }
    };

    onMounted(() => {
      fetchDashboardData();
      store.dispatch('wallet/fetchBalance');
    });

    return {
      stats,
      recentOrders,
      activeBids,
      user,
      walletBalance,
      formatMoney,
      formatDate,
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

.bg-gradient-primary {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}
</style>
