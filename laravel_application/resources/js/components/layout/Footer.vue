<template>
  <footer class="footer">
    <div class="container">
      <div class="row">
        <!-- About Section -->
        <div class="col-lg-4 col-md-6 mb-4">
          <h5>
            <i class="fas fa-wallet me-2"></i>
            StoreWallet
          </h5>
          <p class="text-muted">{{ $t('footer.aboutDescription') }}</p>

          <!-- Payment Methods -->
          <div class="payment-methods mt-3">
            <h6>{{ $t('footer.paymentMethods') }}</h6>
            <div class="d-flex gap-2 flex-wrap">
              <span class="badge bg-light text-dark border">
                <i class="fas fa-wallet"></i> xCash
              </span>
              <span class="badge bg-light text-dark border">Bankak</span>
              <span class="badge bg-light text-dark border">E15</span>
              <span class="badge bg-light text-dark border">SudaniPay</span>
              <span class="badge bg-light text-dark border">
                <i class="fab fa-cc-stripe"></i> Stripe
              </span>
            </div>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="col-lg-2 col-md-6 mb-4">
          <h5>{{ $t('footer.quickLinks') }}</h5>
          <ul class="list-unstyled">
            <li>
              <router-link to="/">{{ $t('nav.home') }}</router-link>
            </li>
            <li>
              <router-link to="/products">{{ $t('nav.products') }}</router-link>
            </li>
            <li>
              <router-link to="/auctions">{{ $t('nav.auctions') }}</router-link>
            </li>
            <li>
              <router-link to="/remittances">{{ $t('nav.remittances') }}</router-link>
            </li>
          </ul>
        </div>

        <!-- Customer Service -->
        <div class="col-lg-3 col-md-6 mb-4">
          <h5>{{ $t('footer.customerService') }}</h5>
          <ul class="list-unstyled">
            <li>
              <a href="#" @click.prevent="openHelp">
                <i class="fas fa-question-circle me-1"></i>
                {{ $t('footer.helpCenter') }}
              </a>
            </li>
            <li>
              <a href="#" @click.prevent="openContact">
                <i class="fas fa-envelope me-1"></i>
                {{ $t('footer.contactUs') }}
              </a>
            </li>
            <li>
              <a href="#" @click.prevent="openFAQ">
                <i class="fas fa-info-circle me-1"></i>
                {{ $t('footer.faq') }}
              </a>
            </li>
            <li>
              <router-link to="/terms">
                <i class="fas fa-file-alt me-1"></i>
                {{ $t('footer.terms') }}
              </router-link>
            </li>
            <li>
              <router-link to="/privacy">
                <i class="fas fa-shield-alt me-1"></i>
                {{ $t('footer.privacy') }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Newsletter & Social -->
        <div class="col-lg-3 col-md-6 mb-4">
          <h5>{{ $t('footer.followUs') }}</h5>

          <!-- Social Icons -->
          <div class="social-icons mb-4">
            <a href="#" class="social-link" target="_blank">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="social-link" target="_blank">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="#" class="social-link" target="_blank">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="#" class="social-link" target="_blank">
              <i class="fab fa-whatsapp"></i>
            </a>
            <a href="#" class="social-link" target="_blank">
              <i class="fab fa-telegram"></i>
            </a>
          </div>

          <!-- Newsletter -->
          <div class="newsletter">
            <h6>{{ $t('footer.newsletter') }}</h6>
            <p class="small text-muted">{{ $t('footer.newsletterDescription') }}</p>
            <form @submit.prevent="subscribeNewsletter">
              <div class="input-group">
                <input
                  type="email"
                  v-model="email"
                  class="form-control"
                  :placeholder="$t('footer.yourEmail')"
                  required
                />
                <button type="submit" class="btn btn-primary">
                  {{ $t('footer.subscribe') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Cities Coverage -->
      <div class="row mt-4 pt-4 border-top">
        <div class="col-12">
          <h6 class="text-center mb-3">
            <i class="fas fa-map-marker-alt me-2"></i>
            {{ currentLocale === 'ar' ? 'نغطي 70+ مدينة سودانية' : 'Covering 70+ Sudanese Cities' }}
          </h6>
          <div class="cities-list text-center">
            <span class="badge bg-secondary me-2 mb-2">{{ currentLocale === 'ar' ? 'الخرطوم' : 'Khartoum' }}</span>
            <span class="badge bg-secondary me-2 mb-2">{{ currentLocale === 'ar' ? 'أم درمان' : 'Omdurman' }}</span>
            <span class="badge bg-secondary me-2 mb-2">{{ currentLocale === 'ar' ? 'بحري' : 'Bahri' }}</span>
            <span class="badge bg-secondary me-2 mb-2">{{ currentLocale === 'ar' ? 'بورتسودان' : 'Port Sudan' }}</span>
            <span class="badge bg-secondary me-2 mb-2">{{ currentLocale === 'ar' ? 'كسلا' : 'Kassala' }}</span>
            <span class="badge bg-secondary me-2 mb-2">{{ currentLocale === 'ar' ? 'نيالا' : 'Nyala' }}</span>
            <span class="badge bg-secondary me-2 mb-2">{{ currentLocale === 'ar' ? 'الفاشر' : 'El Fasher' }}</span>
            <span class="badge bg-secondary me-2 mb-2">{{ currentLocale === 'ar' ? 'القضارف' : 'Gedaref' }}</span>
            <span class="badge bg-secondary me-2 mb-2">{{ currentLocale === 'ar' ? 'عطبرة' : 'Atbara' }}</span>
            <span class="badge bg-secondary me-2 mb-2">+60 {{ currentLocale === 'ar' ? 'مدينة أخرى' : 'more cities' }}</span>
          </div>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="row mt-4 pt-4 border-top">
        <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
          <p class="mb-0 text-muted">
            © {{ currentYear }} StoreWallet. {{ $t('footer.allRightsReserved') }}
          </p>
        </div>
        <div class="col-md-6 text-center text-md-end">
          <p class="mb-0 text-muted">
            <i class="fas fa-lock me-1"></i>
            {{ $t('footer.securePayment') }}
          </p>
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
import { ref, computed } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'Footer',
  setup() {
    const store = useStore();
    const email = ref('');
    const currentYear = new Date().getFullYear();
    const currentLocale = computed(() => store.state.lang.locale);

    const subscribeNewsletter = async () => {
      // TODO: Implement newsletter subscription
      console.log('Subscribe:', email.value);
      alert(currentLocale.value === 'ar' ? 'شكراً للاشتراك!' : 'Thanks for subscribing!');
      email.value = '';
    };

    const openHelp = () => {
      // TODO: Open help center
      console.log('Open help center');
    };

    const openContact = () => {
      // TODO: Open contact form
      console.log('Open contact form');
    };

    const openFAQ = () => {
      // TODO: Open FAQ
      console.log('Open FAQ');
    };

    return {
      email,
      currentYear,
      currentLocale,
      subscribeNewsletter,
      openHelp,
      openContact,
      openFAQ
    };
  }
};
</script>

<style scoped lang="scss">
.footer {
  background-color: #1e293b;
  color: #e2e8f0;
  padding: 3rem 0 1rem;
  margin-top: 4rem;

  h5, h6 {
    color: white;
    font-weight: 600;
    margin-bottom: 1.5rem;
  }

  a {
    color: #cbd5e1;
    text-decoration: none;
    transition: color 0.2s;
    display: inline-block;
    padding: 0.25rem 0;

    &:hover {
      color: white;
    }
  }

  ul li {
    margin-bottom: 0.5rem;
  }

  .social-icons {
    display: flex;
    gap: 1rem;

    .social-link {
      width: 40px;
      height: 40px;
      background-color: #334155;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;

      &:hover {
        background-color: #2563eb;
        transform: translateY(-2px);
      }

      i {
        font-size: 1.1rem;
      }
    }
  }

  .newsletter {
    .input-group {
      .form-control {
        border-top-right-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
      }

      .btn {
        white-space: nowrap;
      }
    }
  }

  .payment-methods {
    .badge {
      font-size: 0.85rem;
      padding: 0.5rem 0.75rem;
      font-weight: 500;
    }
  }

  .cities-list {
    .badge {
      font-size: 0.8rem;
      padding: 0.4rem 0.8rem;
      font-weight: 500;
    }
  }
}

// RTL Support
[dir="rtl"] {
  .footer {
    .newsletter {
      .input-group {
        .form-control {
          border-top-right-radius: 0;
          border-bottom-right-radius: 0;
          border-top-left-radius: 0.375rem;
          border-bottom-left-radius: 0.375rem;
        }
      }
    }
  }
}
</style>
