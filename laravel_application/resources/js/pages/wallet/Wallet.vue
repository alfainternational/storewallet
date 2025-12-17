<template>
  <div class="wallet-page container py-5">
    <h2 class="mb-4">{{ $t('wallet.title') }}</h2>

    <div class="row mb-4">
      <div class="col-md-4 mb-3">
        <div class="card text-center balance-card">
          <div class="card-body">
            <i class="fas fa-wallet fa-3x text-primary mb-3"></i>
            <h6 class="text-muted">{{ $t('wallet.total_balance') }}</h6>
            <h2 class="text-primary fw-bold">{{ formatMoney(totalBalance) }}</h2>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card text-center balance-card">
          <div class="card-body">
            <i class="fas fa-lock fa-3x text-warning mb-3"></i>
            <h6 class="text-muted">{{ $t('wallet.escrow_balance') }}</h6>
            <h2 class="text-warning fw-bold">{{ formatMoney(escrowBalance) }}</h2>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card text-center balance-card">
          <div class="card-body">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <h6 class="text-muted">{{ $t('wallet.available_balance') }}</h6>
            <h2 class="text-success fw-bold">{{ formatMoney(availableBalance) }}</h2>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-md-4 mb-2">
        <button @click="showDepositModal = true" class="btn btn-primary w-100 btn-lg">
          <i class="fas fa-plus-circle me-2"></i>{{ $t('wallet.deposit') }}
        </button>
      </div>
      <div class="col-md-4 mb-2">
        <button @click="showWithdrawModal = true" class="btn btn-success w-100 btn-lg" :disabled="availableBalance <= 0">
          <i class="fas fa-minus-circle me-2"></i>{{ $t('wallet.withdraw') }}
        </button>
      </div>
      <div class="col-md-4 mb-2">
        <button @click="showTransferModal = true" class="btn btn-info w-100 btn-lg" :disabled="availableBalance <= 0">
          <i class="fas fa-exchange-alt me-2"></i>{{ $t('wallet.transfer') }}
        </button>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">{{ $t('wallet.transactions') }}</h4>
        <div class="btn-group" role="group">
          <button @click="filterTransactions('all')" :class="['btn btn-sm', filter === 'all' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('wallet.all') }}</button>
          <button @click="filterTransactions('deposit')" :class="['btn btn-sm', filter === 'deposit' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('wallet.deposits') }}</button>
          <button @click="filterTransactions('withdraw')" :class="['btn btn-sm', filter === 'withdraw' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('wallet.withdrawals') }}</button>
          <button @click="filterTransactions('transfer')" :class="['btn btn-sm', filter === 'transfer' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('wallet.transfers') }}</button>
        </div>
      </div>
      <div class="card-body">
        <div v-if="filteredTransactions.length > 0" class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>{{ $t('wallet.date') }}</th>
                <th>{{ $t('wallet.type') }}</th>
                <th>{{ $t('wallet.description') }}</th>
                <th>{{ $t('wallet.amount') }}</th>
                <th>{{ $t('wallet.status') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="transaction in filteredTransactions" :key="transaction.id">
                <td>{{ formatDate(transaction.created_at) }}</td>
                <td>
                  <span :class="['badge', getTypeBadgeClass(transaction.type)]">
                    <i :class="getTypeIcon(transaction.type)"></i> {{ $t(`wallet.${transaction.type}`) }}
                  </span>
                </td>
                <td>{{ transaction.description }}</td>
                <td :class="transaction.type === 'withdraw' || transaction.type === 'transfer_out' ? 'text-danger' : 'text-success'">
                  {{ transaction.type === 'withdraw' || transaction.type === 'transfer_out' ? '-' : '+' }}{{ formatMoney(transaction.amount) }}
                </td>
                <td>
                  <span :class="['badge', getStatusBadgeClass(transaction.status)]">
                    {{ $t(`wallet.status_${transaction.status}`) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-5">
          <i class="fas fa-history fa-3x text-muted mb-3"></i>
          <p class="text-muted">{{ $t('wallet.no_transactions') }}</p>
        </div>
      </div>
    </div>

    <!-- Deposit Modal -->
    <div v-if="showDepositModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t('wallet.deposit') }}</h5>
            <button type="button" class="btn-close" @click="showDepositModal = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleDeposit">
              <div class="mb-3">
                <label class="form-label">{{ $t('wallet.amount') }}</label>
                <input v-model.number="depositAmount" type="number" class="form-control" min="10" step="0.01" required />
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('wallet.payment_method') }}</label>
                <select v-model="depositMethod" class="form-select" required>
                  <option value="xcash">xCash</option>
                  <option value="bankak">Bankak</option>
                  <option value="e15">E15</option>
                  <option value="sudanipay">SudaniPay</option>
                  <option value="stripe">Stripe</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary w-100" :disabled="depositLoading">
                {{ depositLoading ? $t('wallet.processing') : $t('wallet.deposit') }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Withdraw Modal -->
    <div v-if="showWithdrawModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t('wallet.withdraw') }}</h5>
            <button type="button" class="btn-close" @click="showWithdrawModal = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleWithdraw">
              <div class="mb-3">
                <label class="form-label">{{ $t('wallet.amount') }}</label>
                <input v-model.number="withdrawAmount" type="number" class="form-control" :max="availableBalance" min="10" step="0.01" required />
                <small class="text-muted">{{ $t('wallet.available') }}: {{ formatMoney(availableBalance) }}</small>
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('wallet.bank_account') }}</label>
                <input v-model="withdrawAccount" type="text" class="form-control" required />
              </div>
              <button type="submit" class="btn btn-success w-100" :disabled="withdrawLoading">
                {{ withdrawLoading ? $t('wallet.processing') : $t('wallet.withdraw') }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Transfer Modal -->
    <div v-if="showTransferModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t('wallet.transfer') }}</h5>
            <button type="button" class="btn-close" @click="showTransferModal = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleTransfer">
              <div class="mb-3">
                <label class="form-label">{{ $t('wallet.recipient_phone') }}</label>
                <input v-model="transferPhone" type="tel" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('wallet.amount') }}</label>
                <input v-model.number="transferAmount" type="number" class="form-control" :max="availableBalance" min="1" step="0.01" required />
                <small class="text-muted">{{ $t('wallet.available') }}: {{ formatMoney(availableBalance) }}</small>
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('wallet.note') }}</label>
                <textarea v-model="transferNote" class="form-control" rows="2"></textarea>
              </div>
              <button type="submit" class="btn btn-info w-100" :disabled="transferLoading">
                {{ transferLoading ? $t('wallet.processing') : $t('wallet.transfer') }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showDepositModal || showWithdrawModal || showTransferModal" class="modal-backdrop fade show"></div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';

export default {
  name: 'Wallet',
  setup() {
    const store = useStore();
    const { t, locale } = useI18n();

    const totalBalance = computed(() => store.state.wallet.balance);
    const escrowBalance = computed(() => store.state.wallet.escrowBalance);
    const availableBalance = computed(() => totalBalance.value - escrowBalance.value);
    const transactions = computed(() => store.state.wallet.transactions);

    const filter = ref('all');
    const showDepositModal = ref(false);
    const showWithdrawModal = ref(false);
    const showTransferModal = ref(false);

    const depositAmount = ref(0);
    const depositMethod = ref('xcash');
    const depositLoading = ref(false);

    const withdrawAmount = ref(0);
    const withdrawAccount = ref('');
    const withdrawLoading = ref(false);

    const transferPhone = ref('');
    const transferAmount = ref(0);
    const transferNote = ref('');
    const transferLoading = ref(false);

    const filteredTransactions = computed(() => {
      if (filter.value === 'all') return transactions.value;
      return transactions.value.filter(t => t.type === filter.value);
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
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    const filterTransactions = (type) => {
      filter.value = type;
    };

    const getTypeBadgeClass = (type) => {
      const classes = {
        deposit: 'bg-success',
        withdraw: 'bg-danger',
        transfer_in: 'bg-info',
        transfer_out: 'bg-warning',
        payment: 'bg-primary',
        refund: 'bg-secondary'
      };
      return classes[type] || 'bg-secondary';
    };

    const getTypeIcon = (type) => {
      const icons = {
        deposit: 'fas fa-arrow-down',
        withdraw: 'fas fa-arrow-up',
        transfer_in: 'fas fa-arrow-right',
        transfer_out: 'fas fa-arrow-left',
        payment: 'fas fa-shopping-cart',
        refund: 'fas fa-undo'
      };
      return icons[type] || 'fas fa-exchange-alt';
    };

    const getStatusBadgeClass = (status) => {
      const classes = {
        completed: 'bg-success',
        pending: 'bg-warning',
        failed: 'bg-danger',
        cancelled: 'bg-secondary'
      };
      return classes[status] || 'bg-secondary';
    };

    const handleDeposit = async () => {
      depositLoading.value = true;
      const result = await store.dispatch('wallet/deposit', {
        amount: depositAmount.value,
        method: depositMethod.value
      });
      depositLoading.value = false;
      if (result.success) {
        showDepositModal.value = false;
        depositAmount.value = 0;
        alert(t('wallet.deposit_success'));
      } else {
        alert(t('wallet.deposit_failed'));
      }
    };

    const handleWithdraw = async () => {
      withdrawLoading.value = true;
      const result = await store.dispatch('wallet/withdraw', {
        amount: withdrawAmount.value,
        account: withdrawAccount.value
      });
      withdrawLoading.value = false;
      if (result.success) {
        showWithdrawModal.value = false;
        withdrawAmount.value = 0;
        withdrawAccount.value = '';
        alert(t('wallet.withdraw_success'));
      } else {
        alert(t('wallet.withdraw_failed'));
      }
    };

    const handleTransfer = async () => {
      transferLoading.value = true;
      const result = await store.dispatch('wallet/transfer', {
        phone: transferPhone.value,
        amount: transferAmount.value,
        note: transferNote.value
      });
      transferLoading.value = false;
      if (result.success) {
        showTransferModal.value = false;
        transferPhone.value = '';
        transferAmount.value = 0;
        transferNote.value = '';
        alert(t('wallet.transfer_success'));
      } else {
        alert(t('wallet.transfer_failed'));
      }
    };

    onMounted(() => {
      store.dispatch('wallet/fetchBalance');
      store.dispatch('wallet/fetchTransactions');
    });

    return {
      totalBalance,
      escrowBalance,
      availableBalance,
      transactions,
      filter,
      filteredTransactions,
      showDepositModal,
      showWithdrawModal,
      showTransferModal,
      depositAmount,
      depositMethod,
      depositLoading,
      withdrawAmount,
      withdrawAccount,
      withdrawLoading,
      transferPhone,
      transferAmount,
      transferNote,
      transferLoading,
      formatMoney,
      formatDate,
      filterTransactions,
      getTypeBadgeClass,
      getTypeIcon,
      getStatusBadgeClass,
      handleDeposit,
      handleWithdraw,
      handleTransfer
    };
  }
};
</script>

<style scoped>
.balance-card {
  border: none;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}

.balance-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.modal.show {
  background-color: rgba(0,0,0,0.5);
}
</style>
