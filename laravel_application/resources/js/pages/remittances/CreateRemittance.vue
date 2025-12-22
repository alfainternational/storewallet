<template>
  <div class="create-remittance-page container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header">
            <h3 class="mb-0">
              <i class="fas fa-paper-plane me-2"></i>{{ $t('remittances.send_money') }}
            </h3>
          </div>
          <div class="card-body">
            <div class="alert alert-info mb-4">
              <i class="fas fa-info-circle me-2"></i>
              {{ $t('remittances.send_info') }}
            </div>

            <form @submit.prevent="handleSubmit">
              <div class="row mb-4">
                <div class="col-12">
                  <h5 class="mb-3">{{ $t('remittances.sender_info') }}</h5>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('auth.first_name') }}</label>
                  <input v-model="form.sender_first_name" type="text" class="form-control" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('auth.last_name') }}</label>
                  <input v-model="form.sender_last_name" type="text" class="form-control" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('auth.phone') }}</label>
                  <input v-model="form.sender_phone" type="tel" class="form-control" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('remittances.country') }}</label>
                  <select v-model="form.sender_country" class="form-select" required>
                    <option value="">{{ $t('remittances.select_country') }}</option>
                    <option v-for="country in countries" :key="country" :value="country">{{ country }}</option>
                  </select>
                </div>
              </div>

              <hr />

              <div class="row mb-4">
                <div class="col-12">
                  <h5 class="mb-3">{{ $t('remittances.recipient_info') }}</h5>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('auth.first_name') }}</label>
                  <input v-model="form.recipient_first_name" type="text" class="form-control" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('auth.last_name') }}</label>
                  <input v-model="form.recipient_last_name" type="text" class="form-control" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('auth.phone') }}</label>
                  <input v-model="form.recipient_phone" type="tel" class="form-control" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('remittances.city') }}</label>
                  <select v-model="form.recipient_city_id" class="form-select" required>
                    <option value="">{{ $t('remittances.select_city') }}</option>
                    <option v-for="city in cities" :key="city.id" :value="city.id">
                      {{ currentLocale === 'ar' ? city.name_ar : city.name }}
                    </option>
                  </select>
                </div>
              </div>

              <hr />

              <div class="row mb-4">
                <div class="col-12">
                  <h5 class="mb-3">{{ $t('remittances.payment_details') }}</h5>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('remittances.amount') }}</label>
                  <div class="input-group">
                    <input v-model.number="form.amount" @input="calculateConversion" type="number" class="form-control" min="1" step="0.01" required />
                    <select v-model="form.currency_id" @change="calculateConversion" class="form-select">
                      <option v-for="currency in currencies" :key="currency.id" :value="currency.id">
                        {{ currency.code }}
                      </option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('remittances.amount_in_sdg') }}</label>
                  <input :value="formatMoney(convertedAmount)" type="text" class="form-control" disabled />
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label">{{ $t('remittances.receiving_method') }}</label>
                  <select v-model="form.receiving_method" class="form-select" required>
                    <option value="bank_transfer">{{ $t('remittances.bank_transfer') }}</option>
                    <option value="mobile_wallet">{{ $t('remittances.mobile_wallet') }}</option>
                    <option value="cash_pickup">{{ $t('remittances.cash_pickup') }}</option>
                    <option value="door_delivery">{{ $t('remittances.door_delivery') }}</option>
                  </select>
                </div>
                <div class="col-12 mb-3" v-if="form.receiving_method === 'bank_transfer'">
                  <label class="form-label">{{ $t('remittances.bank_account') }}</label>
                  <input v-model="form.bank_account" type="text" class="form-control" required />
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label">{{ $t('remittances.notes') }}</label>
                  <textarea v-model="form.notes" class="form-control" rows="3"></textarea>
                </div>
              </div>

              <div class="card bg-light mb-4">
                <div class="card-body">
                  <h6 class="mb-3">{{ $t('remittances.cost_breakdown') }}</h6>
                  <div class="d-flex justify-content-between mb-2">
                    <span>{{ $t('remittances.send_amount') }}</span>
                    <strong>{{ formatMoney(convertedAmount) }}</strong>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span>{{ $t('remittances.fees') }}</span>
                    <strong>{{ formatMoney(fees) }}</strong>
                  </div>
                  <hr />
                  <div class="d-flex justify-content-between">
                    <h6>{{ $t('remittances.total_to_pay') }}</h6>
                    <h5 class="text-primary">{{ formatMoney(totalAmount) }}</h5>
                  </div>
                  <small class="text-muted">{{ $t('remittances.exchange_rate') }}: 1 {{ selectedCurrency?.code }} = {{ selectedCurrency?.exchange_rate }} SDG</small>
                </div>
              </div>

              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg" :disabled="loading || !isValid">
                  <i class="fas fa-paper-plane me-2"></i>
                  {{ loading ? $t('common.processing') : $t('remittances.send_money') }}
                </button>
                <router-link to="/remittances" class="btn btn-outline-secondary">
                  {{ $t('common.cancel') }}
                </router-link>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';

export default {
  name: 'CreateRemittance',
  setup() {
    const router = useRouter();
    const store = useStore();
    const { t, locale } = useI18n();

    const form = ref({
      sender_first_name: '',
      sender_last_name: '',
      sender_phone: '',
      sender_country: '',
      recipient_first_name: '',
      recipient_last_name: '',
      recipient_phone: '',
      recipient_city_id: '',
      amount: 0,
      currency_id: '',
      receiving_method: 'mobile_wallet',
      bank_account: '',
      notes: ''
    });

    const loading = ref(false);
    const cities = ref([]);
    const currencies = ref([]);
    const convertedAmount = ref(0);
    const fees = ref(0);

    const countries = [
      'Saudi Arabia', 'UAE', 'Qatar', 'Kuwait', 'Bahrain', 'Oman',
      'Egypt', 'Jordan', 'Lebanon', 'Turkey', 'UK', 'USA', 'Canada'
    ];

    const currentLocale = computed(() => locale.value);
    const user = computed(() => store.state.auth.user);

    const selectedCurrency = computed(() => {
      return currencies.value.find(c => c.id === form.value.currency_id);
    });

    const totalAmount = computed(() => {
      return convertedAmount.value + fees.value;
    });

    const isValid = computed(() => {
      return form.value.sender_first_name &&
             form.value.sender_last_name &&
             form.value.recipient_first_name &&
             form.value.recipient_last_name &&
             form.value.recipient_phone &&
             form.value.recipient_city_id &&
             form.value.amount > 0 &&
             form.value.currency_id &&
             form.value.receiving_method;
    });

    const formatMoney = (amount) => {
      return new Intl.NumberFormat(locale.value, {
        style: 'currency',
        currency: 'SDG',
        minimumFractionDigits: 2
      }).format(amount);
    };

    const calculateConversion = () => {
      if (form.value.amount && selectedCurrency.value) {
        convertedAmount.value = form.value.amount * selectedCurrency.value.exchange_rate;
        fees.value = convertedAmount.value * 0.02; // 2% fee
      }
    };

    const fetchCities = async () => {
      try {
        const response = await window.axios.get('/cities');
        cities.value = response.data.cities;
      } catch (error) {
        console.error('Error fetching cities:', error);
      }
    };

    const fetchCurrencies = async () => {
      try {
        const response = await window.axios.get('/currencies');
        currencies.value = response.data.currencies;
        if (currencies.value.length > 0) {
          form.value.currency_id = currencies.value.find(c => c.code === 'USD')?.id || currencies.value[0].id;
        }
      } catch (error) {
        console.error('Error fetching currencies:', error);
      }
    };

    const handleSubmit = async () => {
      if (!isValid.value) return;

      loading.value = true;
      try {
        const response = await window.axios.post('/remittances', form.value);
        if (response.data.success) {
          alert(t('remittances.send_success'));
          router.push(`/remittances/${response.data.remittance.id}`);
        }
      } catch (error) {
        console.error('Error creating remittance:', error);
        alert(error.response?.data?.message || t('remittances.send_failed'));
      }
      loading.value = false;
    };

    onMounted(() => {
      fetchCities();
      fetchCurrencies();

      if (user.value) {
        form.value.sender_first_name = user.value.first_name || '';
        form.value.sender_last_name = user.value.last_name || '';
        form.value.sender_phone = user.value.phone || '';
        form.value.sender_country = user.value.country_of_residence || '';
      }
    });

    return {
      form,
      loading,
      cities,
      currencies,
      countries,
      convertedAmount,
      fees,
      currentLocale,
      selectedCurrency,
      totalAmount,
      isValid,
      formatMoney,
      calculateConversion,
      handleSubmit
    };
  }
};
</script>
