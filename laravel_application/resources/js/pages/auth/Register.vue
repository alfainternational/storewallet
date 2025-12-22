<template>
  <div class="auth-page">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
          <div class="auth-card">
            <div class="text-center mb-4">
              <h2 class="fw-bold">{{ $t('auth.register') }}</h2>
            </div>
            <form @submit.prevent="handleRegister">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <input v-model="form.first_name" type="text" class="form-control" :placeholder="$t('auth.first_name')" required />
                </div>
                <div class="col-md-6 mb-3">
                  <input v-model="form.last_name" type="text" class="form-control" :placeholder="$t('auth.last_name')" required />
                </div>
              </div>
              <div class="mb-3">
                <input v-model="form.email" type="email" class="form-control" :placeholder="$t('auth.email')" required />
              </div>
              <div class="mb-3">
                <input v-model="form.phone" type="tel" class="form-control" :placeholder="$t('auth.phone')" required />
              </div>
              <div class="mb-3">
                <input v-model="form.password" type="password" class="form-control" :placeholder="$t('auth.password')" required />
              </div>
              <div class="mb-3">
                <select v-model="form.role" class="form-select" required>
                  <option value="buyer">{{ $t('auth.buyer') }}</option>
                  <option value="merchant">{{ $t('auth.merchant') }}</option>
                  <option value="shipping_company">{{ $t('auth.shippingCompany') }}</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary w-100" :disabled="loading">{{ $t('auth.register') }}</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
export default {
  setup() {
    const store = useStore();
    const router = useRouter();
    const loading = ref(false);
    const form = ref({ first_name: '', last_name: '', email: '', phone: '', password: '', role: 'buyer' });
    const handleRegister = async () => {
      loading.value = true;
      const result = await store.dispatch('auth/register', form.value);
      if (result.success) router.push('/dashboard');
      loading.value = false;
    };
    return { form, loading, handleRegister };
  }
};
</script>
