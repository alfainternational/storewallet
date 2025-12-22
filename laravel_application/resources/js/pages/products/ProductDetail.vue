<template>
  <div class="product-detail-page" v-if="product">
    <div class="container py-5">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><router-link to="/">{{ $t('nav.home') }}</router-link></li>
          <li class="breadcrumb-item"><router-link to="/products">{{ $t('nav.products') }}</router-link></li>
          <li class="breadcrumb-item active">{{ currentLocale === 'ar' ? product.name_ar : product.name }}</li>
        </ol>
      </nav>

      <div class="row">
        <div class="col-lg-6 mb-4">
          <div class="product-images">
            <div class="main-image mb-3">
              <img :src="selectedImage || product.main_image" :alt="product.name" class="img-fluid rounded" />
            </div>
            <div class="image-thumbnails d-flex gap-2">
              <img v-for="(image, index) in product.images" :key="index" :src="image.url" @click="selectedImage = image.url" :class="['img-thumbnail', { active: selectedImage === image.url }]" style="width: 80px; height: 80px; cursor: pointer;" />
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <h1 class="mb-3">{{ currentLocale === 'ar' ? product.name_ar : product.name }}</h1>
          <div class="d-flex align-items-center mb-3">
            <div class="rating me-3">
              <i v-for="i in 5" :key="i" :class="['fas fa-star', i <= product.rating ? 'text-warning' : 'text-muted']"></i>
              <span class="ms-2">({{ product.reviews_count }} {{ $t('products.reviews') }})</span>
            </div>
            <span class="badge bg-success" v-if="product.stock > 0">{{ $t('products.in_stock') }}</span>
            <span class="badge bg-danger" v-else>{{ $t('products.out_of_stock') }}</span>
          </div>

          <div class="price mb-4">
            <h2 class="text-primary mb-0">{{ formatMoney(product.price) }}</h2>
            <span v-if="product.original_price > product.price" class="text-muted text-decoration-line-through">{{ formatMoney(product.original_price) }}</span>
            <span v-if="product.discount_percentage > 0" class="badge bg-danger ms-2">-{{ product.discount_percentage }}%</span>
          </div>

          <div class="description mb-4">
            <h5>{{ $t('products.description') }}</h5>
            <p>{{ currentLocale === 'ar' ? product.description_ar : product.description }}</p>
          </div>

          <div class="product-meta mb-4">
            <p><strong>{{ $t('products.category') }}:</strong> {{ product.category?.name }}</p>
            <p><strong>{{ $t('products.sku') }}:</strong> {{ product.sku }}</p>
            <p v-if="product.weight"><strong>{{ $t('products.weight') }}:</strong> {{ product.weight }}g</p>
            <p><strong>{{ $t('products.merchant') }}:</strong> {{ product.merchant?.shop_name }}</p>
          </div>

          <div v-if="product.variants && product.variants.length > 0" class="variants mb-4">
            <h5>{{ $t('products.variants') }}</h5>
            <div class="btn-group" role="group">
              <button v-for="variant in product.variants" :key="variant.id" @click="selectedVariant = variant" :class="['btn', selectedVariant?.id === variant.id ? 'btn-primary' : 'btn-outline-primary']">
                {{ variant.name }} (+{{ formatMoney(variant.price_difference) }})
              </button>
            </div>
          </div>

          <div class="quantity-selector mb-4">
            <label class="form-label">{{ $t('products.quantity') }}</label>
            <div class="input-group" style="max-width: 150px;">
              <button class="btn btn-outline-secondary" @click="quantity > 1 && quantity--">-</button>
              <input v-model.number="quantity" type="number" class="form-control text-center" min="1" :max="product.stock" />
              <button class="btn btn-outline-secondary" @click="quantity < product.stock && quantity++">+</button>
            </div>
          </div>

          <div class="actions d-flex gap-2 mb-4">
            <button @click="addToCart" class="btn btn-primary btn-lg flex-fill" :disabled="product.stock === 0 || loading">
              <i class="fas fa-shopping-cart me-2"></i>{{ $t('products.add_to_cart') }}
            </button>
            <button @click="toggleWishlist" :class="['btn btn-lg', isInWishlist ? 'btn-danger' : 'btn-outline-danger']">
              <i :class="['fas', isInWishlist ? 'fa-heart' : 'fa-heart']"></i>
            </button>
            <button @click="buyNow" class="btn btn-success btn-lg" :disabled="product.stock === 0">
              {{ $t('products.buy_now') }}
            </button>
          </div>

          <div class="merchant-info card">
            <div class="card-body">
              <h5>{{ $t('products.seller_info') }}</h5>
              <div class="d-flex align-items-center">
                <img :src="product.merchant?.shop_logo || '/images/merchant-placeholder.png'" alt="Merchant" class="rounded-circle me-3" style="width: 50px; height: 50px;" />
                <div>
                  <h6 class="mb-0">{{ product.merchant?.shop_name }}</h6>
                  <div class="rating-small">
                    <i v-for="i in 5" :key="i" :class="['fas fa-star fa-sm', i <= product.merchant?.rating ? 'text-warning' : 'text-muted']"></i>
                  </div>
                </div>
              </div>
              <router-link :to="`/merchant/${product.merchant_id}`" class="btn btn-outline-primary btn-sm mt-3 w-100">
                {{ $t('products.visit_store') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-5">
        <div class="col-12">
          <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button :class="['nav-link', activeTab === 'details' && 'active']" @click="activeTab = 'details'">{{ $t('products.details') }}</button>
            </li>
            <li class="nav-item" role="presentation">
              <button :class="['nav-link', activeTab === 'reviews' && 'active']" @click="activeTab = 'reviews'">{{ $t('products.reviews') }} ({{ product.reviews_count }})</button>
            </li>
          </ul>
          <div class="tab-content p-4 border border-top-0">
            <div v-show="activeTab === 'details'">
              <p>{{ currentLocale === 'ar' ? product.description_ar : product.description }}</p>
              <ul v-if="product.specifications">
                <li v-for="(value, key) in product.specifications" :key="key">
                  <strong>{{ key }}:</strong> {{ value }}
                </li>
              </ul>
            </div>
            <div v-show="activeTab === 'reviews'">
              <div v-if="product.reviews && product.reviews.length > 0">
                <div v-for="review in product.reviews" :key="review.id" class="review-item mb-4 pb-4 border-bottom">
                  <div class="d-flex justify-content-between mb-2">
                    <div>
                      <strong>{{ review.user.first_name }} {{ review.user.last_name }}</strong>
                      <div class="rating-small">
                        <i v-for="i in 5" :key="i" :class="['fas fa-star fa-sm', i <= review.rating ? 'text-warning' : 'text-muted']"></i>
                      </div>
                    </div>
                    <small class="text-muted">{{ formatDate(review.created_at) }}</small>
                  </div>
                  <p>{{ review.comment }}</p>
                </div>
              </div>
              <div v-else class="text-center py-5">
                <p class="text-muted">{{ $t('products.no_reviews') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-5" v-if="relatedProducts && relatedProducts.length > 0">
        <div class="col-12">
          <h3 class="mb-4">{{ $t('products.related_products') }}</h3>
          <div class="row">
            <div v-for="relatedProduct in relatedProducts" :key="relatedProduct.id" class="col-md-3 mb-4">
              <ProductCard :product="relatedProduct" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div v-else class="container py-5">
    <div class="text-center">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">{{ $t('common.loading') }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import ProductCard from '@/components/product/ProductCard.vue';

export default {
  name: 'ProductDetail',
  components: { ProductCard },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const store = useStore();
    const { t, locale } = useI18n();

    const product = ref(null);
    const relatedProducts = ref([]);
    const selectedImage = ref(null);
    const selectedVariant = ref(null);
    const quantity = ref(1);
    const loading = ref(false);
    const activeTab = ref('details');

    const currentLocale = computed(() => locale.value);
    const isInWishlist = computed(() => {
      return store.state.wishlist?.items?.some(item => item.id === product.value?.id);
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
        month: 'long',
        day: 'numeric'
      });
    };

    const fetchProduct = async () => {
      loading.value = true;
      try {
        const response = await window.axios.get(`/products/${route.params.id}`);
        product.value = response.data.product;
        relatedProducts.value = response.data.related_products || [];
      } catch (error) {
        console.error('Error fetching product:', error);
        alert(t('errors.product_not_found'));
        router.push('/products');
      }
      loading.value = false;
    };

    const addToCart = () => {
      const item = {
        ...product.value,
        quantity: quantity.value,
        variant: selectedVariant.value
      };
      store.commit('cart/ADD_ITEM', item);
      alert(t('products.added_to_cart'));
    };

    const toggleWishlist = () => {
      if (isInWishlist.value) {
        store.commit('wishlist/REMOVE_ITEM', product.value.id);
      } else {
        store.commit('wishlist/ADD_ITEM', product.value);
      }
    };

    const buyNow = () => {
      addToCart();
      router.push('/checkout');
    };

    onMounted(() => {
      fetchProduct();
    });

    return {
      product,
      relatedProducts,
      selectedImage,
      selectedVariant,
      quantity,
      loading,
      activeTab,
      currentLocale,
      isInWishlist,
      formatMoney,
      formatDate,
      addToCart,
      toggleWishlist,
      buyNow
    };
  }
};
</script>

<style scoped>
.main-image img {
  width: 100%;
  height: 500px;
  object-fit: cover;
}

.img-thumbnail.active {
  border: 2px solid #2563eb;
}

.review-item:last-child {
  border-bottom: none !important;
}
</style>
