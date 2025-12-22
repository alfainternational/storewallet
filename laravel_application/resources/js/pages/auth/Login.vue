<template>
  <div class="auth-page">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          <div class="auth-card">
            <!-- Logo -->
            <div class="text-center mb-4">
              <h2 class="fw-bold">
                <i class="fas fa-wallet text-primary me-2"></i>
                StoreWallet
              </h2>
              <p class="text-muted">{{ $t('auth.login') }}</p>
            </div>

            <!-- Error Alert -->
            <div v-if="error" class="alert alert-danger" role="alert">
              <i class="fas fa-exclamation-circle me-2"></i>
              {{ error }}
            </div>

            <!-- Login Form -->
            <form @submit.prevent="handleLogin">
              <!-- Email/Phone -->
              <div class="mb-3">
                <label class="form-label">{{ $t('auth.email') }} / {{ $t('auth.phone') }}</label>
                <input
                  type="text"
                  v-model="credentials.email"
                  class="form-control"
                  :placeholder="currentLocale === 'ar' ? 'البريد أو الهاتف' : 'Email or Phone'"
                  required
                />
              </div>

              <!-- Password -->
              <div class="mb-3">
                <label class="form-label">{{ $t('auth.password') }}</label>
                <div class="input-group">
                  <input
                    :type="showPassword ? 'text' : 'password'"
                    v-model="credentials.password"
                    class="form-control"
                    :placeholder="$t('auth.password')"
                    required
                  />
                  <button
                    type="button"
                    class="btn btn-outline-secondary"
                    @click="showPassword = !showPassword"
                  >
                    <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                  </button>
                </div>
              </div>

              <!-- Remember Me -->
              <div class="mb-3 form-check">
                <input
                  type="checkbox"
                  v-model="credentials.remember"
                  class="form-check-input"
                  id="rememberMe"
                />
                <label class="form-check-label" for="rememberMe">
                  {{ $t('auth.rememberMe') }}
                </label>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                class="btn btn-primary w-100 mb-3"
                :disabled="loading"
              >
                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                {{ $t('auth.login') }}
              </button>

              <!-- Forgot Password -->
              <div class="text-center mb-3">
                <router-link to="/forgot-password" class="text-decoration-none">
                  {{ $t('auth.forgotPassword') }}
                </router-link>
              </div>

              <!-- Divider -->
              <div class="divider my-4">
                <span>{{ currentLocale === 'ar' ? 'أو' : 'OR' }}</span>
              </div>

              <!-- Register Link -->
              <div class="text-center">
                <p class="mb-0">
                  {{ $t('auth.dontHaveAccount') }}
                  <router-link to="/register" class="fw-bold text-decoration-none">
                    {{ $t('auth.registerNow') }}
                  </router-link>
                </p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useStore } from 'vuex';
import { useRouter, useRoute } from 'vue-router';

export default {
  name: 'Login',
  setup() {
    const store = useStore();
    const router = useRouter();
    const route = useRoute();

    const loading = ref(false);
    const error = ref('');
    const showPassword = ref(false);
    const credentials = ref({
      email: '',
      password: '',
      remember: false
    });

    const currentLocale = computed(() => store.state.lang.locale);

    const handleLogin = async () => {
      loading.value = true;
      error.value = '';

      const result = await store.dispatch('auth/login', credentials.value);

      if (result.success) {
        const redirect = route.query.redirect || '/dashboard';
        router.push(redirect);
      } else {
        error.value = result.message;
      }

      loading.value = false;
    };

    return {
      loading,
      error,
      showPassword,
      credentials,
      currentLocale,
      handleLogin
    };
  }
};
</script>

<style scoped lang="scss">
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 0;

  .auth-card {
    background: white;
    padding: 2.5rem;
    border-radius: 1rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  }

  .divider {
    position: relative;
    text-align: center;

    &::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      height: 1px;
      background-color: #e2e8f0;
    }

    span {
      position: relative;
      background: white;
      padding: 0 1rem;
      color: #64748b;
      font-size: 0.9rem;
    }
  }
}
</style>
