<template>
  <div class="home-page">
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6 mb-4 mb-lg-0">
            <h1 class="display-4 fw-bold mb-3">
              {{ $t('home.welcome') }}
            </h1>
            <p class="lead text-muted mb-4">
              {{ $t('home.subtitle') }}
            </p>
            <div class="hero-buttons">
              <router-link to="/products" class="btn btn-primary btn-lg me-3">
                <i class="fas fa-shopping-bag me-2"></i>
                {{ $t('nav.products') }}
              </router-link>
              <router-link to="/auctions" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-gavel me-2"></i>
                {{ $t('nav.auctions') }}
              </router-link>
            </div>

            <!-- Stats -->
            <div class="stats mt-5">
              <div class="row">
                <div class="col-4">
                  <div class="stat-item">
                    <h3 class="text-primary fw-bold">70+</h3>
                    <p class="text-muted small mb-0">
                      {{ currentLocale === 'ar' ? 'مدينة' : 'Cities' }}
                    </p>
                  </div>
                </div>
                <div class="col-4">
                  <div class="stat-item">
                    <h3 class="text-primary fw-bold">10</h3>
                    <p class="text-muted small mb-0">
                      {{ currentLocale === 'ar' ? 'عملات' : 'Currencies' }}
                    </p>
                  </div>
                </div>
                <div class="col-4">
                  <div class="stat-item">
                    <h3 class="text-primary fw-bold">24/7</h3>
                    <p class="text-muted small mb-0">
                      {{ currentLocale === 'ar' ? 'دعم' : 'Support' }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="hero-image">
              <img src="/images/hero-illustration.svg" alt="StoreWallet" class="img-fluid">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features-section py-5">
      <div class="container">
        <div class="text-center mb-5">
          <h2 class="fw-bold">{{ $t('home.whyChooseUs') }}</h2>
        </div>

        <div class="row g-4">
          <div class="col-md-6 col-lg-3">
            <div class="feature-card text-center">
              <div class="feature-icon">
                <i class="fas fa-shield-alt"></i>
              </div>
              <h5 class="mt-3 mb-2">{{ $t('home.securePayments') }}</h5>
              <p class="text-muted small">{{ $t('home.securePaymentsDesc') }}</p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="feature-card text-center">
              <div class="feature-icon">
                <i class="fas fa-shipping-fast"></i>
              </div>
              <h5 class="mt-3 mb-2">{{ $t('home.fastShipping') }}</h5>
              <p class="text-muted small">{{ $t('home.fastShippingDesc') }}</p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="feature-card text-center">
              <div class="feature-icon">
                <i class="fas fa-exchange-alt"></i>
              </div>
              <h5 class="mt-3 mb-2">{{ $t('home.multiCurrency') }}</h5>
              <p class="text-muted small">{{ $t('home.multiCurrencyDesc') }}</p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="feature-card text-center">
              <div class="feature-icon">
                <i class="fas fa-headset"></i>
              </div>
              <h5 class="mt-3 mb-2">{{ $t('home.customerSupport') }}</h5>
              <p class="text-muted small">{{ $t('home.customerSupportDesc') }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="products-section py-5 bg-light">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="fw-bold mb-0">{{ $t('home.featuredProducts') }}</h2>
          <router-link to="/products" class="btn btn-outline-primary">
            {{ $t('common.viewAll') }}
            <i class="fas fa-arrow-right ms-2"></i>
          </router-link>
        </div>

        <!-- Products Grid -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ $t('common.loading') }}</span>
          </div>
        </div>

        <div v-else class="row g-4">
          <div
            v-for="product in featuredProducts"
            :key="product.id"
            class="col-md-6 col-lg-3"
          >
            <ProductCard :product="product" />
          </div>
        </div>
      </div>
    </section>

    <!-- Active Auctions -->
    <section class="auctions-section py-5">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="fw-bold mb-0">{{ $t('home.activeAuctions') }}</h2>
          <router-link to="/auctions" class="btn btn-outline-primary">
            {{ $t('common.viewAll') }}
            <i class="fas fa-arrow-right ms-2"></i>
          </router-link>
        </div>

        <div v-if="loadingAuctions" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ $t('common.loading') }}</span>
          </div>
        </div>

        <div v-else class="row g-4">
          <div
            v-for="auction in activeAuctions"
            :key="auction.id"
            class="col-md-6 col-lg-4"
          >
            <AuctionCard :auction="auction" />
          </div>
        </div>
      </div>
    </section>

    <!-- Categories -->
    <section class="categories-section py-5 bg-light">
      <div class="container">
        <div class="text-center mb-5">
          <h2 class="fw-bold">{{ $t('home.categories') }}</h2>
        </div>

        <div class="row g-4">
          <div
            v-for="category in categories"
            :key="category.id"
            class="col-6 col-md-4 col-lg-2"
          >
            <router-link
              :to="`/products?category=${category.id}`"
              class="category-card text-decoration-none"
            >
              <div class="category-icon">
                <i :class="category.icon"></i>
              </div>
              <p class="category-name mt-2 mb-0">{{ category.name }}</p>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-5">
      <div class="container">
        <div class="cta-card text-center">
          <h2 class="fw-bold mb-3">
            {{ currentLocale === 'ar' ? 'ابدأ البيع الآن' : 'Start Selling Now' }}
          </h2>
          <p class="lead mb-4">
            {{
              currentLocale === 'ar'
                ? 'انضم لآلاف التجار في السودان'
                : 'Join thousands of merchants in Sudan'
            }}
          </p>
          <router-link to="/register" class="btn btn-light btn-lg">
            {{ $t('nav.register') }}
            <i class="fas fa-arrow-right ms-2"></i>
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import ProductCard from '../components/product/ProductCard.vue';
import AuctionCard from '../components/auction/AuctionCard.vue';

export default {
  name: 'Home',
  components: {
    ProductCard,
    AuctionCard
  },
  setup() {
    const store = useStore();

    const loading = ref(true);
    const loadingAuctions = ref(true);
    const featuredProducts = ref([]);
    const activeAuctions = ref([]);

    const currentLocale = computed(() => store.state.lang.locale);

    const categories = ref([
      { id: 1, name: currentLocale.value === 'ar' ? 'إلكترونيات' : 'Electronics', icon: 'fas fa-laptop' },
      { id: 2, name: currentLocale.value === 'ar' ? 'ملابس' : 'Fashion', icon: 'fas fa-tshirt' },
      { id: 3, name: currentLocale.value === 'ar' ? 'منزل' : 'Home', icon: 'fas fa-home' },
      { id: 4, name: currentLocale.value === 'ar' ? 'رياضة' : 'Sports', icon: 'fas fa-futbol' },
      { id: 5, name: currentLocale.value === 'ar' ? 'كتب' : 'Books', icon: 'fas fa-book' },
      { id: 6, name: currentLocale.value === 'ar' ? 'أخرى' : 'Others', icon: 'fas fa-th' }
    ]);

    const fetchFeaturedProducts = async () => {
      loading.value = true;
      try {
        // TODO: Replace with actual API call
        await new Promise(resolve => setTimeout(resolve, 1000));
        featuredProducts.value = [
          {
            id: 1,
            name: currentLocale.value === 'ar' ? 'هاتف ذكي' : 'Smartphone',
            price: 15000,
            image: '/images/products/phone.jpg',
            rating: 4.5,
            reviews: 120
          },
          {
            id: 2,
            name: currentLocale.value === 'ar' ? 'لابتوب' : 'Laptop',
            price: 45000,
            image: '/images/products/laptop.jpg',
            rating: 4.8,
            reviews: 85
          },
          {
            id: 3,
            name: currentLocale.value === 'ar' ? 'ساعة ذكية' : 'Smart Watch',
            price: 8000,
            image: '/images/products/watch.jpg',
            rating: 4.3,
            reviews: 200
          },
          {
            id: 4,
            name: currentLocale.value === 'ar' ? 'سماعات' : 'Headphones',
            price: 3500,
            image: '/images/products/headphones.jpg',
            rating: 4.6,
            reviews: 150
          }
        ];
      } catch (error) {
        console.error('Error fetching products:', error);
      } finally {
        loading.value = false;
      }
    };

    const fetchActiveAuctions = async () => {
      loadingAuctions.value = true;
      try {
        // TODO: Replace with actual API call
        await new Promise(resolve => setTimeout(resolve, 1000));
        activeAuctions.value = [
          {
            id: 1,
            title: currentLocale.value === 'ar' ? 'كاميرا رقمية' : 'Digital Camera',
            type: 'product',
            current_bid: 12000,
            end_time: new Date(Date.now() + 86400000),
            image: '/images/auctions/camera.jpg'
          },
          {
            id: 2,
            title: currentLocale.value === 'ar' ? 'توصيل - الخرطوم إلى بحري' : 'Delivery - Khartoum to Bahri',
            type: 'delivery',
            current_bid: 50,
            end_time: new Date(Date.now() + 43200000),
            image: null
          },
          {
            id: 3,
            title: currentLocale.value === 'ar' ? 'شحن دولي - دبي إلى الخرطوم' : 'International - Dubai to Khartoum',
            type: 'international',
            current_bid: 500,
            end_time: new Date(Date.now() + 172800000),
            image: null
          }
        ];
      } catch (error) {
        console.error('Error fetching auctions:', error);
      } finally {
        loadingAuctions.value = false;
      }
    };

    onMounted(() => {
      fetchFeaturedProducts();
      fetchActiveAuctions();
    });

    return {
      loading,
      loadingAuctions,
      featuredProducts,
      activeAuctions,
      categories,
      currentLocale
    };
  }
};
</script>

<style scoped lang="scss">
.home-page {
  .hero-section {
    padding: 5rem 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;

    h1 {
      font-size: 3rem;
    }

    .hero-image {
      img {
        max-width: 100%;
        height: auto;
      }
    }

    .stats {
      .stat-item {
        h3 {
          color: white;
          margin-bottom: 0.25rem;
        }

        p {
          color: rgba(255, 255, 255, 0.8);
        }
      }
    }
  }

  .feature-card {
    padding: 2rem 1rem;
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;

    &:hover {
      transform: translateY(-4px);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    .feature-icon {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto;

      i {
        font-size: 2rem;
        color: white;
      }
    }
  }

  .category-card {
    display: block;
    text-align: center;
    padding: 1.5rem 1rem;
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.2s;
    color: inherit;

    &:hover {
      transform: translateY(-4px);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
      color: #2563eb;
    }

    .category-icon {
      font-size: 2.5rem;
      color: #2563eb;
    }

    .category-name {
      font-weight: 600;
      font-size: 0.9rem;
    }
  }

  .cta-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;

    .cta-card {
      padding: 3rem 2rem;
    }

    .btn-light {
      &:hover {
        background-color: white;
        transform: translateY(-2px);
      }
    }
  }
}

@media (max-width: 768px) {
  .home-page {
    .hero-section {
      padding: 3rem 0;

      h1 {
        font-size: 2rem;
      }
    }
  }
}
</style>
