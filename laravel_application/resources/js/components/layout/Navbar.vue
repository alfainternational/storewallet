<template>
  <nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
      <!-- Logo -->
      <router-link to="/" class="navbar-brand">
        <i class="fas fa-wallet me-2"></i>
        <span>StoreWallet</span>
      </router-link>

      <!-- Mobile Toggle -->
      <button
        class="navbar-toggler"
        type="button"
        @click="toggleMobileMenu"
        :aria-expanded="showMobileMenu"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Nav Items -->
      <div class="collapse navbar-collapse" :class="{ 'show': showMobileMenu }">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <router-link to="/" class="nav-link" active-class="active">
              {{ $t('nav.home') }}
            </router-link>
          </li>
          <li class="nav-item">
            <router-link to="/products" class="nav-link" active-class="active">
              {{ $t('nav.products') }}
            </router-link>
          </li>
          <li class="nav-item">
            <router-link to="/auctions" class="nav-link" active-class="active">
              {{ $t('nav.auctions') }}
            </router-link>
          </li>
          <li class="nav-item" v-if="isAuthenticated">
            <router-link to="/remittances" class="nav-link" active-class="active">
              {{ $t('nav.remittances') }}
            </router-link>
          </li>
        </ul>

        <!-- Right Side -->
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <!-- Language Toggle -->
          <li class="nav-item">
            <button @click="toggleLanguage" class="btn btn-link nav-link">
              <i class="fas fa-globe me-1"></i>
              {{ currentLocale === 'ar' ? 'English' : 'العربية' }}
            </button>
          </li>

          <!-- Cart -->
          <li class="nav-item">
            <router-link to="/cart" class="nav-link position-relative">
              <i class="fas fa-shopping-cart"></i>
              <span v-if="cartItemCount > 0" class="cart-badge">
                {{ cartItemCount }}
              </span>
            </router-link>
          </li>

          <!-- User Menu (Authenticated) -->
          <template v-if="isAuthenticated">
            <!-- Wallet Balance -->
            <li class="nav-item">
              <router-link to="/wallet" class="nav-link">
                <i class="fas fa-wallet me-1"></i>
                {{ formatMoney(walletBalance) }}
              </router-link>
            </li>

            <!-- User Dropdown -->
            <li class="nav-item dropdown">
              <a
                class="nav-link dropdown-toggle"
                href="#"
                @click.prevent="toggleUserMenu"
                :aria-expanded="showUserMenu"
              >
                <i class="fas fa-user-circle me-1"></i>
                {{ user?.first_name }}
              </a>
              <ul class="dropdown-menu" :class="{ 'show': showUserMenu }">
                <li>
                  <router-link to="/dashboard" class="dropdown-item">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    {{ $t('nav.dashboard') }}
                  </router-link>
                </li>
                <li v-if="isMerchant">
                  <router-link to="/merchant/dashboard" class="dropdown-item">
                    <i class="fas fa-store me-2"></i>
                    {{ $t('merchant.dashboard') }}
                  </router-link>
                </li>
                <li v-if="isShippingCompany">
                  <router-link to="/shipper/dashboard" class="dropdown-item">
                    <i class="fas fa-truck me-2"></i>
                    {{ $t('shipper.dashboard') }}
                  </router-link>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <router-link to="/my-orders" class="dropdown-item">
                    <i class="fas fa-box me-2"></i>
                    {{ $t('nav.myOrders') }}
                  </router-link>
                </li>
                <li>
                  <router-link to="/profile" class="dropdown-item">
                    <i class="fas fa-user me-2"></i>
                    {{ $t('nav.myProfile') }}
                  </router-link>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a href="#" @click.prevent="handleLogout" class="dropdown-item text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    {{ $t('nav.logout') }}
                  </a>
                </li>
              </ul>
            </li>
          </template>

          <!-- Guest Links -->
          <template v-else>
            <li class="nav-item">
              <router-link to="/login" class="nav-link">
                {{ $t('nav.login') }}
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/register" class="btn btn-primary ms-2">
                {{ $t('nav.register') }}
              </router-link>
            </li>
          </template>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';

export default {
  name: 'Navbar',
  setup() {
    const store = useStore();
    const router = useRouter();

    const showMobileMenu = ref(false);
    const showUserMenu = ref(false);

    const isAuthenticated = computed(() => store.getters['auth/isAuthenticated']);
    const user = computed(() => store.getters['auth/user']);
    const isMerchant = computed(() => store.getters['auth/isMerchant']);
    const isShippingCompany = computed(() => store.getters['auth/isShippingCompany']);
    const cartItemCount = computed(() => store.getters['cart/itemCount']);
    const walletBalance = computed(() => store.state.wallet.balance);
    const currentLocale = computed(() => store.state.lang.locale);

    const toggleMobileMenu = () => {
      showMobileMenu.value = !showMobileMenu.value;
    };

    const toggleUserMenu = () => {
      showUserMenu.value = !showUserMenu.value;
    };

    const toggleLanguage = () => {
      store.dispatch('lang/toggleLocale');
    };

    const handleLogout = async () => {
      await store.dispatch('auth/logout');
      router.push('/');
    };

    const formatMoney = (amount) => {
      return new Intl.NumberFormat(currentLocale.value, {
        style: 'currency',
        currency: 'SDG',
        minimumFractionDigits: 0
      }).format(amount || 0);
    };

    // Close dropdowns when clicking outside
    const handleClickOutside = (event) => {
      if (!event.target.closest('.dropdown')) {
        showUserMenu.value = false;
      }
    };

    onMounted(() => {
      document.addEventListener('click', handleClickOutside);

      // Fetch wallet balance if authenticated
      if (isAuthenticated.value) {
        store.dispatch('wallet/fetchBalance');
      }
    });

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside);
    });

    return {
      showMobileMenu,
      showUserMenu,
      isAuthenticated,
      user,
      isMerchant,
      isShippingCompany,
      cartItemCount,
      walletBalance,
      currentLocale,
      toggleMobileMenu,
      toggleUserMenu,
      toggleLanguage,
      handleLogout,
      formatMoney
    };
  }
};
</script>

<style scoped lang="scss">
.navbar {
  background-color: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 0.75rem 0;

  .navbar-brand {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2563eb;

    i {
      font-size: 1.75rem;
    }
  }

  .nav-link {
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: color 0.2s;

    &:hover {
      color: #2563eb;
    }

    &.active {
      color: #2563eb;
      font-weight: 600;
    }
  }

  .cart-badge {
    position: absolute;
    top: -5px;
    right: -8px;
    background-color: #ef4444;
    color: white;
    font-size: 0.7rem;
    font-weight: 700;
    border-radius: 50%;
    padding: 0.15rem 0.4rem;
    min-width: 20px;
    text-align: center;
  }

  .dropdown-menu {
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-top: 0.5rem;

    .dropdown-item {
      padding: 0.75rem 1.25rem;

      &:hover {
        background-color: #f3f4f6;
      }
    }
  }
}

// RTL Support
[dir="rtl"] {
  .navbar {
    .cart-badge {
      right: auto;
      left: -8px;
    }
  }
}

@media (max-width: 992px) {
  .navbar-collapse {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 1rem;
    z-index: 1000;
  }
}
</style>
