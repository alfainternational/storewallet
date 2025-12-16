<template>
  <div id="app" :dir="isRTL ? 'rtl' : 'ltr'" :lang="currentLocale">
    <Navbar v-if="!isAuthPage" />

    <main class="main-content">
      <router-view />
    </main>

    <Footer v-if="!isAuthPage" />

    <!-- Loading overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">{{ $t('common.loading') }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';
import { useStore } from 'vuex';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import Navbar from './components/layout/Navbar.vue';
import Footer from './components/layout/Footer.vue';

export default {
  name: 'App',
  components: {
    Navbar,
    Footer
  },
  setup() {
    const store = useStore();
    const route = useRoute();
    const { locale } = useI18n();

    const isRTL = computed(() => store.getters['lang/isRTL']);
    const currentLocale = computed(() => store.state.lang.locale);
    const loading = computed(() => store.state.loading);

    const isAuthPage = computed(() => {
      return ['/login', '/register', '/forgot-password'].includes(route.path);
    });

    // Initialize user from localStorage
    const user = localStorage.getItem('user');
    if (user) {
      store.commit('auth/SET_USER', JSON.parse(user));
    }

    // Sync i18n locale with store
    locale.value = currentLocale.value;

    return {
      isRTL,
      currentLocale,
      loading,
      isAuthPage
    };
  }
};
</script>

<style lang="scss">
@import '../sass/variables';

#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
  padding-top: 70px; // Account for fixed navbar
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;

  .spinner-border {
    width: 3rem;
    height: 3rem;
  }
}

// RTL Support
[dir="rtl"] {
  text-align: right;

  .text-left {
    text-align: right !important;
  }

  .text-right {
    text-align: left !important;
  }

  .float-left {
    float: right !important;
  }

  .float-right {
    float: left !important;
  }

  .me-1, .me-2, .me-3 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
  }

  .ms-1, .ms-2, .ms-3 {
    margin-left: 0 !important;
    margin-right: 0.25rem !important;
  }
}
</style>
