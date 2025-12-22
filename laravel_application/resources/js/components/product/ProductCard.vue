<template>
  <div class="product-card">
    <router-link :to="`/products/${product.id}`" class="text-decoration-none">
      <!-- Product Image -->
      <div class="product-image-wrapper">
        <img
          :src="product.image || '/images/placeholder.jpg'"
          :alt="product.name"
          class="product-image"
        />

        <!-- Badges -->
        <div class="product-badges">
          <span v-if="product.is_new" class="badge bg-success">
            {{ $t('products.new') }}
          </span>
          <span v-if="product.discount" class="badge bg-danger">
            -{{ product.discount }}%
          </span>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
          <button
            @click.prevent="toggleWishlist"
            class="btn btn-sm btn-light rounded-circle"
            :class="{ 'text-danger': isInWishlist }"
          >
            <i :class="isInWishlist ? 'fas fa-heart' : 'far fa-heart'"></i>
          </button>
        </div>
      </div>

      <!-- Product Info -->
      <div class="product-info">
        <h5 class="product-title">{{ product.name }}</h5>

        <!-- Rating -->
        <div class="product-rating mb-2" v-if="product.rating">
          <div class="stars">
            <i
              v-for="star in 5"
              :key="star"
              class="fas fa-star"
              :class="{
                'text-warning': star <= Math.floor(product.rating),
                'text-muted': star > Math.floor(product.rating)
              }"
            ></i>
          </div>
          <span class="rating-count text-muted small ms-1">
            ({{ product.reviews || 0 }})
          </span>
        </div>

        <!-- Price -->
        <div class="product-price">
          <span class="current-price">
            {{ formatPrice(product.price) }}
          </span>
          <span v-if="product.original_price" class="original-price">
            {{ formatPrice(product.original_price) }}
          </span>
        </div>

        <!-- Stock Status -->
        <div class="stock-status mt-2">
          <span
            v-if="product.stock > 0"
            class="badge bg-success-subtle text-success"
          >
            <i class="fas fa-check-circle me-1"></i>
            {{ $t('products.inStock') }}
          </span>
          <span v-else class="badge bg-danger-subtle text-danger">
            <i class="fas fa-times-circle me-1"></i>
            {{ $t('products.outOfStock') }}
          </span>
        </div>

        <!-- Merchant -->
        <div class="merchant-info mt-2" v-if="product.merchant">
          <small class="text-muted">
            <i class="fas fa-store me-1"></i>
            {{ product.merchant.name }}
          </small>
        </div>
      </div>
    </router-link>

    <!-- Add to Cart -->
    <div class="product-actions mt-3">
      <button
        @click="addToCart"
        class="btn btn-primary w-100"
        :disabled="product.stock <= 0"
      >
        <i class="fas fa-shopping-cart me-2"></i>
        {{ $t('products.addToCart') }}
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'ProductCard',
  props: {
    product: {
      type: Object,
      required: true
    }
  },
  setup(props) {
    const store = useStore();
    const isInWishlist = ref(false);

    const currentLocale = computed(() => store.state.lang.locale);

    const formatPrice = (price) => {
      return new Intl.NumberFormat(currentLocale.value, {
        style: 'currency',
        currency: 'SDG',
        minimumFractionDigits: 0
      }).format(price);
    };

    const addToCart = () => {
      store.dispatch('cart/addToCart', {
        id: props.product.id,
        name: props.product.name,
        price: props.product.price,
        image: props.product.image,
        quantity: 1,
        merchant_id: props.product.merchant?.id
      });

      store.dispatch('showNotification', {
        type: 'success',
        message: currentLocale.value === 'ar'
          ? 'تمت إضافة المنتج للسلة'
          : 'Product added to cart'
      });
    };

    const toggleWishlist = () => {
      isInWishlist.value = !isInWishlist.value;
      // TODO: Implement wishlist API
    };

    return {
      isInWishlist,
      formatPrice,
      addToCart,
      toggleWishlist
    };
  }
};
</script>

<style scoped lang="scss">
.product-card {
  background: white;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;

  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  }

  .product-image-wrapper {
    position: relative;
    overflow: hidden;
    padding-top: 100%; // 1:1 aspect ratio

    .product-image {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    &:hover .product-image {
      transform: scale(1.05);
    }

    .product-badges {
      position: absolute;
      top: 0.75rem;
      right: 0.75rem;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      z-index: 2;

      .badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.6rem;
      }
    }

    .quick-actions {
      position: absolute;
      top: 0.75rem;
      left: 0.75rem;
      z-index: 2;

      .btn {
        width: 35px;
        height: 35px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

        &:hover {
          transform: scale(1.1);
        }
      }
    }
  }

  .product-info {
    padding: 1rem;
    flex: 1;

    .product-title {
      font-size: 1rem;
      font-weight: 600;
      color: #1e293b;
      margin-bottom: 0.5rem;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }

    .product-rating {
      display: flex;
      align-items: center;

      .stars {
        display: flex;
        gap: 0.1rem;

        i {
          font-size: 0.85rem;
        }
      }
    }

    .product-price {
      display: flex;
      align-items: center;
      gap: 0.5rem;

      .current-price {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2563eb;
      }

      .original-price {
        font-size: 0.9rem;
        color: #94a3b8;
        text-decoration: line-through;
      }
    }

    .stock-status {
      .badge {
        font-size: 0.75rem;
        font-weight: 600;
      }
    }

    .merchant-info {
      small {
        font-size: 0.8rem;
      }
    }
  }

  .product-actions {
    padding: 0 1rem 1rem;

    .btn {
      font-weight: 600;
      transition: all 0.2s;

      &:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
      }

      &:disabled {
        cursor: not-allowed;
        opacity: 0.6;
      }
    }
  }
}

// RTL Support
[dir="rtl"] {
  .product-card {
    .product-image-wrapper {
      .product-badges {
        right: auto;
        left: 0.75rem;
      }

      .quick-actions {
        left: auto;
        right: 0.75rem;
      }
    }
  }
}
</style>
