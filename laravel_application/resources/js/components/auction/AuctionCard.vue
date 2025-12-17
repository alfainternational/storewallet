<template>
  <div class="auction-card">
    <router-link :to="`/auctions/${auction.id}`" class="text-decoration-none">
      <!-- Auction Image or Icon -->
      <div class="auction-image-wrapper">
        <img
          v-if="auction.image"
          :src="auction.image"
          :alt="auction.title"
          class="auction-image"
        />
        <div v-else class="auction-icon-placeholder">
          <i :class="getAuctionIcon(auction.type)"></i>
        </div>

        <!-- Type Badge -->
        <div class="auction-type-badge">
          <span :class="`badge bg-${getAuctionTypeColor(auction.type)}`">
            {{ getAuctionTypeLabel(auction.type) }}
          </span>
        </div>

        <!-- Time Badge -->
        <div class="auction-time-badge" v-if="timeRemaining">
          <span class="badge bg-dark">
            <i class="fas fa-clock me-1"></i>
            {{ timeRemaining }}
          </span>
        </div>
      </div>

      <!-- Auction Info -->
      <div class="auction-info">
        <h5 class="auction-title">{{ auction.title }}</h5>

        <!-- Current Bid -->
        <div class="bid-info mb-2">
          <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small">
              {{ isReverse ? $t('auctions.lowestBid') : $t('auctions.currentBid') }}
            </span>
            <span class="bid-amount">
              {{ formatPrice(auction.current_bid || auction.starting_bid) }}
            </span>
          </div>
        </div>

        <!-- Bid Count -->
        <div class="bid-stats" v-if="auction.bid_count">
          <small class="text-muted">
            <i class="fas fa-gavel me-1"></i>
            {{ auction.bid_count }} {{ $t('auctions.bidCount') }}
          </small>
        </div>

        <!-- Location (for delivery/international) -->
        <div class="location-info mt-2" v-if="showLocation">
          <small class="text-muted">
            <i class="fas fa-map-marker-alt me-1"></i>
            {{ auction.from_city }} → {{ auction.to_city }}
          </small>
        </div>

        <!-- Status -->
        <div class="auction-status mt-2">
          <span :class="`badge bg-${getStatusColor(auction.status)}`">
            {{ getStatusLabel(auction.status) }}
          </span>
        </div>
      </div>
    </router-link>

    <!-- Quick Bid Button -->
    <div class="auction-actions mt-3" v-if="auction.status === 'active'">
      <button
        @click="quickBid"
        class="btn btn-primary w-100"
        :disabled="!isAuthenticated"
      >
        <i class="fas fa-gavel me-2"></i>
        {{ $t('auctions.placeBid') }}
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';

export default {
  name: 'AuctionCard',
  props: {
    auction: {
      type: Object,
      required: true
    }
  },
  setup(props) {
    const store = useStore();
    const router = useRouter();

    const timeRemaining = ref('');
    const timer = ref(null);

    const currentLocale = computed(() => store.state.lang.locale);
    const isAuthenticated = computed(() => store.getters['auth/isAuthenticated']);
    const isReverse = computed(() => ['delivery', 'international'].includes(props.auction.type));
    const showLocation = computed(() => ['delivery', 'international'].includes(props.auction.type));

    const formatPrice = (price) => {
      return new Intl.NumberFormat(currentLocale.value, {
        style: 'currency',
        currency: 'SDG',
        minimumFractionDigits: 0
      }).format(price);
    };

    const getAuctionIcon = (type) => {
      const icons = {
        product: 'fas fa-box',
        delivery: 'fas fa-truck',
        international: 'fas fa-plane'
      };
      return icons[type] || 'fas fa-gavel';
    };

    const getAuctionTypeColor = (type) => {
      const colors = {
        product: 'primary',
        delivery: 'success',
        international: 'info'
      };
      return colors[type] || 'secondary';
    };

    const getAuctionTypeLabel = (type) => {
      const labels = {
        product: currentLocale.value === 'ar' ? 'منتج' : 'Product',
        delivery: currentLocale.value === 'ar' ? 'توصيل' : 'Delivery',
        international: currentLocale.value === 'ar' ? 'دولي' : 'International'
      };
      return labels[type] || type;
    };

    const getStatusColor = (status) => {
      const colors = {
        active: 'success',
        pending: 'warning',
        completed: 'secondary',
        cancelled: 'danger'
      };
      return colors[status] || 'secondary';
    };

    const getStatusLabel = (status) => {
      const labels = {
        active: currentLocale.value === 'ar' ? 'نشط' : 'Active',
        pending: currentLocale.value === 'ar' ? 'معلق' : 'Pending',
        completed: currentLocale.value === 'ar' ? 'مكتمل' : 'Completed',
        cancelled: currentLocale.value === 'ar' ? 'ملغي' : 'Cancelled'
      };
      return labels[status] || status;
    };

    const updateTimeRemaining = () => {
      if (!props.auction.end_time) return;

      const now = new Date();
      const endTime = new Date(props.auction.end_time);
      const diff = endTime - now;

      if (diff <= 0) {
        timeRemaining.value = currentLocale.value === 'ar' ? 'انتهى' : 'Ended';
        if (timer.value) {
          clearInterval(timer.value);
        }
        return;
      }

      const days = Math.floor(diff / (1000 * 60 * 60 * 24));
      const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

      if (days > 0) {
        timeRemaining.value = currentLocale.value === 'ar'
          ? `${days} يوم`
          : `${days}d`;
      } else if (hours > 0) {
        timeRemaining.value = currentLocale.value === 'ar'
          ? `${hours} ساعة`
          : `${hours}h ${minutes}m`;
      } else {
        timeRemaining.value = currentLocale.value === 'ar'
          ? `${minutes} دقيقة`
          : `${minutes}m`;
      }
    };

    const quickBid = () => {
      if (!isAuthenticated.value) {
        router.push('/login');
        return;
      }
      router.push(`/auctions/${props.auction.id}`);
    };

    onMounted(() => {
      updateTimeRemaining();
      timer.value = setInterval(updateTimeRemaining, 60000); // Update every minute
    });

    onUnmounted(() => {
      if (timer.value) {
        clearInterval(timer.value);
      }
    });

    return {
      timeRemaining,
      isAuthenticated,
      isReverse,
      showLocation,
      formatPrice,
      getAuctionIcon,
      getAuctionTypeColor,
      getAuctionTypeLabel,
      getStatusColor,
      getStatusLabel,
      quickBid
    };
  }
};
</script>

<style scoped lang="scss">
.auction-card {
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

  .auction-image-wrapper {
    position: relative;
    overflow: hidden;
    padding-top: 66.67%; // 3:2 aspect ratio
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

    .auction-image {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .auction-icon-placeholder {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;

      i {
        font-size: 4rem;
        color: white;
        opacity: 0.8;
      }
    }

    .auction-type-badge {
      position: absolute;
      top: 0.75rem;
      right: 0.75rem;
      z-index: 2;

      .badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.6rem;
      }
    }

    .auction-time-badge {
      position: absolute;
      bottom: 0.75rem;
      right: 0.75rem;
      z-index: 2;

      .badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.6rem;
      }
    }
  }

  .auction-info {
    padding: 1rem;
    flex: 1;

    .auction-title {
      font-size: 1rem;
      font-weight: 600;
      color: #1e293b;
      margin-bottom: 0.75rem;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }

    .bid-info {
      .bid-amount {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2563eb;
      }
    }

    .bid-stats,
    .location-info {
      small {
        font-size: 0.8rem;
      }
    }

    .auction-status {
      .badge {
        font-size: 0.75rem;
        font-weight: 600;
      }
    }
  }

  .auction-actions {
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
  .auction-card {
    .auction-image-wrapper {
      .auction-type-badge,
      .auction-time-badge {
        right: auto;
        left: 0.75rem;
      }
    }
  }
}
</style>
