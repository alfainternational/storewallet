<template>
  <div class="remittances-page container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>{{ $t('remittances.title') }}</h2>
      <router-link to="/remittances/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>{{ $t('remittances.send_money') }}
      </router-link>
    </div>

    <div class="row mb-4">
      <div class="col-md-3 mb-3">
        <div class="card text-center">
          <div class="card-body">
            <i class="fas fa-paper-plane fa-3x text-primary mb-3"></i>
            <h6 class="text-muted">{{ $t('remittances.total_sent') }}</h6>
            <h3 class="text-primary">{{ totalSent }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card text-center">
          <div class="card-body">
            <i class="fas fa-inbox fa-3x text-success mb-3"></i>
            <h6 class="text-muted">{{ $t('remittances.total_received') }}</h6>
            <h3 class="text-success">{{ totalReceived }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card text-center">
          <div class="card-body">
            <i class="fas fa-clock fa-3x text-warning mb-3"></i>
            <h6 class="text-muted">{{ $t('remittances.pending') }}</h6>
            <h3 class="text-warning">{{ pendingCount }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card text-center">
          <div class="card-body">
            <i class="fas fa-check-circle fa-3x text-info mb-3"></i>
            <h6 class="text-muted">{{ $t('remittances.completed') }}</h6>
            <h3 class="text-info">{{ completedCount }}</h3>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-0">{{ $t('remittances.history') }}</h5>
          <div class="btn-group" role="group">
            <button @click="filter = 'all'" :class="['btn btn-sm', filter === 'all' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('remittances.all') }}</button>
            <button @click="filter = 'sent'" :class="['btn btn-sm', filter === 'sent' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('remittances.sent') }}</button>
            <button @click="filter = 'received'" :class="['btn btn-sm', filter === 'received' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('remittances.received') }}</button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ $t('common.loading') }}</span>
          </div>
        </div>

        <div v-else-if="filteredRemittances.length > 0" class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>{{ $t('remittances.number') }}</th>
                <th>{{ $t('remittances.date') }}</th>
                <th>{{ $t('remittances.sender') }}</th>
                <th>{{ $t('remittances.recipient') }}</th>
                <th>{{ $t('remittances.amount') }}</th>
                <th>{{ $t('remittances.status') }}</th>
                <th>{{ $t('common.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="remittance in filteredRemittances" :key="remittance.id">
                <td>
                  <span class="badge bg-secondary">{{ remittance.remittance_number }}</span>
                </td>
                <td>{{ formatDate(remittance.created_at) }}</td>
                <td>
                  <i :class="['fas', remittance.sender_id === user?.id ? 'fa-arrow-up text-danger' : 'fa-arrow-down text-success', 'me-2']"></i>
                  {{ remittance.sender_name }}
                </td>
                <td>{{ remittance.recipient_name }}</td>
                <td>
                  <strong>{{ formatMoney(remittance.amount, remittance.currency?.code || 'SDG') }}</strong>
                  <br />
                  <small class="text-muted">({{ formatMoney(remittance.amount_in_sdg) }} SDG)</small>
                </td>
                <td>
                  <span :class="['badge', getStatusBadge(remittance.status)]">
                    {{ $t(`remittances.status_${remittance.status}`) }}
                  </span>
                </td>
                <td>
                  <router-link :to="`/remittances/${remittance.id}`" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye"></i>
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="text-center py-5">
          <i class="fas fa-exchange-alt fa-4x text-muted mb-3"></i>
          <h4>{{ $t('remittances.no_remittances') }}</h4>
          <p class="text-muted">{{ $t('remittances.no_remittances_message') }}</p>
          <router-link to="/remittances/create" class="btn btn-primary">
            {{ $t('remittances.send_first') }}
          </router-link>
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
  name: 'Remittances',
  setup() {
    const store = useStore();
    const { t, locale } = useI18n();

    const remittances = ref([]);
    const loading = ref(false);
    const filter = ref('all');

    const user = computed(() => store.state.auth.user);
    const currentLocale = computed(() => locale.value);

    const filteredRemittances = computed(() => {
      if (filter.value === 'all') return remittances.value;
      if (filter.value === 'sent') return remittances.value.filter(r => r.sender_id === user.value?.id);
      if (filter.value === 'received') return remittances.value.filter(r => r.recipient_id === user.value?.id);
      return remittances.value;
    });

    const totalSent = computed(() => {
      return remittances.value.filter(r => r.sender_id === user.value?.id).length;
    });

    const totalReceived = computed(() => {
      return remittances.value.filter(r => r.recipient_id === user.value?.id).length;
    });

    const pendingCount = computed(() => {
      return remittances.value.filter(r => r.status === 'pending').length;
    });

    const completedCount = computed(() => {
      return remittances.value.filter(r => r.status === 'completed').length;
    });

    const formatMoney = (amount, currency = 'SDG') => {
      return new Intl.NumberFormat(locale.value, {
        style: 'currency',
        currency: currency,
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
        completed: 'bg-success',
        cancelled: 'bg-danger',
        failed: 'bg-danger'
      };
      return badges[status] || 'bg-secondary';
    };

    const fetchRemittances = async () => {
      loading.value = true;
      try {
        const response = await window.axios.get('/remittances');
        remittances.value = response.data.remittances;
      } catch (error) {
        console.error('Error fetching remittances:', error);
      }
      loading.value = false;
    };

    onMounted(() => {
      fetchRemittances();
    });

    return {
      remittances,
      loading,
      filter,
      user,
      currentLocale,
      filteredRemittances,
      totalSent,
      totalReceived,
      pendingCount,
      completedCount,
      formatMoney,
      formatDate,
      getStatusBadge
    };
  }
};
</script>
