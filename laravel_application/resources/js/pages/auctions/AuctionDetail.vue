<template>
  <div class="auction-detail-page" v-if="auction">
    <div class="container py-5">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><router-link to="/">{{ $t('nav.home') }}</router-link></li>
          <li class="breadcrumb-item"><router-link to="/auctions">{{ $t('nav.auctions') }}</router-link></li>
          <li class="breadcrumb-item active">{{ auction.title }}</li>
        </ol>
      </nav>

      <div class="row">
        <div class="col-lg-8">
          <div class="card mb-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <h2>{{ currentLocale === 'ar' ? auction.title_ar : auction.title }}</h2>
                <span :class="['badge', getStatusBadge(auction.status)]">
                  {{ $t(`auctions.status_${auction.status}`) }}
                </span>
              </div>

              <div class="auction-image mb-4">
                <img :src="auction.product?.main_image || '/images/placeholder.jpg'" :alt="auction.title" class="img-fluid rounded" />
              </div>

              <div class="auction-meta mb-4">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <strong>{{ $t('auctions.auction_type') }}:</strong>
                    <span class="badge bg-info ms-2">
                      {{ auction.bid_type === 'lowest_bid' ? $t('auctions.lowest_bid_wins') : $t('auctions.highest_bid_wins') }}
                    </span>
                  </div>
                  <div class="col-md-6 mb-3">
                    <strong>{{ $t('auctions.start_price') }}:</strong>
                    <span class="text-primary ms-2">{{ formatMoney(auction.start_price) }}</span>
                  </div>
                  <div class="col-md-6 mb-3">
                    <strong>{{ $t('auctions.current_price') }}:</strong>
                    <span class="text-success fw-bold ms-2">{{ formatMoney(auction.current_bid || auction.start_price) }}</span>
                  </div>
                  <div class="col-md-6 mb-3">
                    <strong>{{ $t('auctions.total_bids') }}:</strong>
                    <span class="badge bg-secondary ms-2">{{ auction.bids_count || 0 }}</span>
                  </div>
                </div>
              </div>

              <div class="countdown-timer mb-4" v-if="auction.status === 'active'">
                <h5>{{ $t('auctions.time_remaining') }}</h5>
                <div class="timer-display d-flex gap-3 justify-content-center p-4 bg-light rounded">
                  <div class="timer-unit text-center">
                    <div class="display-4 text-primary">{{ timeRemaining.days }}</div>
                    <small class="text-muted">{{ $t('auctions.days') }}</small>
                  </div>
                  <div class="display-4 text-muted">:</div>
                  <div class="timer-unit text-center">
                    <div class="display-4 text-primary">{{ timeRemaining.hours }}</div>
                    <small class="text-muted">{{ $t('auctions.hours') }}</small>
                  </div>
                  <div class="display-4 text-muted">:</div>
                  <div class="timer-unit text-center">
                    <div class="display-4 text-primary">{{ timeRemaining.minutes }}</div>
                    <small class="text-muted">{{ $t('auctions.minutes') }}</small>
                  </div>
                  <div class="display-4 text-muted">:</div>
                  <div class="timer-unit text-center">
                    <div class="display-4 text-primary">{{ timeRemaining.seconds }}</div>
                    <small class="text-muted">{{ $t('auctions.seconds') }}</small>
                  </div>
                </div>
              </div>

              <div class="description mb-4">
                <h5>{{ $t('auctions.description') }}</h5>
                <p>{{ currentLocale === 'ar' ? auction.description_ar : auction.description }}</p>
              </div>

              <div class="terms mb-4" v-if="auction.terms">
                <h5>{{ $t('auctions.terms_conditions') }}</h5>
                <p class="text-muted">{{ auction.terms }}</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">{{ $t('auctions.bid_history') }}</h5>
            </div>
            <div class="card-body">
              <div v-if="bids.length > 0" class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>{{ $t('auctions.bidder') }}</th>
                      <th>{{ $t('auctions.amount') }}</th>
                      <th>{{ $t('auctions.time') }}</th>
                      <th>{{ $t('auctions.status') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="bid in bids" :key="bid.id" :class="{ 'table-success': bid.is_winning }">
                      <td>
                        <i class="fas fa-user-circle me-2"></i>
                        {{ bid.user?.first_name }} {{ bid.user?.last_name?.charAt(0) }}.
                      </td>
                      <td class="fw-bold">{{ formatMoney(bid.amount) }}</td>
                      <td>{{ formatDate(bid.created_at) }}</td>
                      <td>
                        <span v-if="bid.is_winning" class="badge bg-success">
                          <i class="fas fa-trophy"></i> {{ $t('auctions.winning') }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center py-5">
                <i class="fas fa-gavel fa-3x text-muted mb-3"></i>
                <p class="text-muted">{{ $t('auctions.no_bids_yet') }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card sticky-top" style="top: 20px;">
            <div class="card-body">
              <h5 class="card-title mb-4">{{ $t('auctions.place_bid') }}</h5>

              <div v-if="auction.status === 'active' && isAuthenticated">
                <div class="mb-3">
                  <label class="form-label">{{ $t('auctions.your_bid') }}</label>
                  <div class="input-group">
                    <span class="input-group-text">SDG</span>
                    <input v-model.number="bidAmount" type="number" class="form-control" step="0.01" :min="getMinBidAmount()" />
                  </div>
                  <small class="text-muted">
                    {{ auction.bid_type === 'lowest_bid' ? $t('auctions.min_bid_note_low') : $t('auctions.min_bid_note_high') }}
                  </small>
                </div>

                <div class="alert alert-info" v-if="auction.bid_type === 'lowest_bid'">
                  <i class="fas fa-info-circle me-2"></i>
                  {{ $t('auctions.lowest_bid_info') }}
                </div>

                <button @click="placeBid" class="btn btn-primary w-100 btn-lg mb-3" :disabled="bidLoading || !bidAmount">
                  <i class="fas fa-gavel me-2"></i>
                  {{ bidLoading ? $t('auctions.placing_bid') : $t('auctions.place_bid') }}
                </button>

                <div class="quick-bid-buttons">
                  <p class="small text-muted mb-2">{{ $t('auctions.quick_bids') }}</p>
                  <div class="d-grid gap-2">
                    <button @click="setQuickBid(10)" class="btn btn-sm btn-outline-primary">+10 SDG</button>
                    <button @click="setQuickBid(50)" class="btn btn-sm btn-outline-primary">+50 SDG</button>
                    <button @click="setQuickBid(100)" class="btn btn-sm btn-outline-primary">+100 SDG</button>
                  </div>
                </div>
              </div>

              <div v-else-if="auction.status === 'completed'">
                <div class="alert alert-success" v-if="auction.winner_id === user?.id">
                  <i class="fas fa-trophy me-2"></i>
                  {{ $t('auctions.you_won') }}
                </div>
                <div class="alert alert-secondary" v-else>
                  <i class="fas fa-flag-checkered me-2"></i>
                  {{ $t('auctions.auction_ended') }}
                </div>
              </div>

              <div v-else-if="!isAuthenticated">
                <div class="alert alert-warning">
                  {{ $t('auctions.login_to_bid') }}
                </div>
                <router-link :to="{ path: '/login', query: { redirect: $route.fullPath } }" class="btn btn-primary w-100">
                  {{ $t('auth.login') }}
                </router-link>
              </div>

              <hr />

              <div class="auction-info">
                <h6>{{ $t('auctions.auction_details') }}</h6>
                <ul class="list-unstyled">
                  <li class="mb-2">
                    <i class="fas fa-calendar me-2 text-muted"></i>
                    <strong>{{ $t('auctions.start_date') }}:</strong><br />
                    <small>{{ formatDate(auction.start_time) }}</small>
                  </li>
                  <li class="mb-2">
                    <i class="fas fa-calendar-times me-2 text-muted"></i>
                    <strong>{{ $t('auctions.end_date') }}:</strong><br />
                    <small>{{ formatDate(auction.end_time) }}</small>
                  </li>
                  <li class="mb-2">
                    <i class="fas fa-store me-2 text-muted"></i>
                    <strong>{{ $t('auctions.seller') }}:</strong><br />
                    <small>{{ auction.merchant?.shop_name }}</small>
                  </li>
                </ul>
              </div>
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';

export default {
  name: 'AuctionDetail',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const store = useStore();
    const { t, locale } = useI18n();

    const auction = ref(null);
    const bids = ref([]);
    const bidAmount = ref(0);
    const bidLoading = ref(false);
    const timeRemaining = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 });
    let countdownInterval = null;

    const currentLocale = computed(() => locale.value);
    const isAuthenticated = computed(() => store.getters['auth/isAuthenticated']);
    const user = computed(() => store.state.auth.user);

    const formatMoney = (amount) => {
      return new Intl.NumberFormat(locale.value, {
        style: 'currency',
        currency: 'SDG',
        minimumFractionDigits: 2
      }).format(amount);
    };

    const formatDate = (date) => {
      return new Date(date).toLocaleString(locale.value, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    const getStatusBadge = (status) => {
      const badges = {
        active: 'bg-success',
        completed: 'bg-secondary',
        cancelled: 'bg-danger',
        pending: 'bg-warning'
      };
      return badges[status] || 'bg-secondary';
    };

    const calculateTimeRemaining = () => {
      if (!auction.value || auction.value.status !== 'active') return;

      const now = new Date().getTime();
      const end = new Date(auction.value.end_time).getTime();
      const distance = end - now;

      if (distance < 0) {
        timeRemaining.value = { days: 0, hours: 0, minutes: 0, seconds: 0 };
        if (countdownInterval) clearInterval(countdownInterval);
        fetchAuction();
        return;
      }

      timeRemaining.value = {
        days: Math.floor(distance / (1000 * 60 * 60 * 24)),
        hours: Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
        minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
        seconds: Math.floor((distance % (1000 * 60)) / 1000)
      };
    };

    const fetchAuction = async () => {
      try {
        const response = await window.axios.get(`/auctions/${route.params.id}`);
        auction.value = response.data.auction;
        bids.value = response.data.bids || [];
        bidAmount.value = getMinBidAmount();
      } catch (error) {
        console.error('Error fetching auction:', error);
        alert(t('errors.auction_not_found'));
        router.push('/auctions');
      }
    };

    const getMinBidAmount = () => {
      if (!auction.value) return 0;
      const currentBid = auction.value.current_bid || auction.value.start_price;
      if (auction.value.bid_type === 'lowest_bid') {
        return Math.max(0.01, currentBid - 10);
      } else {
        return currentBid + 10;
      }
    };

    const setQuickBid = (increment) => {
      const currentBid = auction.value.current_bid || auction.value.start_price;
      if (auction.value.bid_type === 'lowest_bid') {
        bidAmount.value = Math.max(0.01, currentBid - increment);
      } else {
        bidAmount.value = currentBid + increment;
      }
    };

    const placeBid = async () => {
      if (!isAuthenticated.value) {
        router.push({ path: '/login', query: { redirect: route.fullPath } });
        return;
      }

      bidLoading.value = true;
      try {
        const response = await window.axios.post(`/auctions/${auction.value.id}/bid`, {
          amount: bidAmount.value
        });

        if (response.data.success) {
          alert(t('auctions.bid_placed_success'));
          await fetchAuction();
        }
      } catch (error) {
        console.error('Error placing bid:', error);
        alert(error.response?.data?.message || t('auctions.bid_failed'));
      }
      bidLoading.value = false;
    };

    onMounted(() => {
      fetchAuction();
      countdownInterval = setInterval(calculateTimeRemaining, 1000);
    });

    onUnmounted(() => {
      if (countdownInterval) clearInterval(countdownInterval);
    });

    return {
      auction,
      bids,
      bidAmount,
      bidLoading,
      timeRemaining,
      currentLocale,
      isAuthenticated,
      user,
      formatMoney,
      formatDate,
      getStatusBadge,
      getMinBidAmount,
      setQuickBid,
      placeBid
    };
  }
};
</script>

<style scoped>
.timer-display {
  background: linear-gradient(135deg, #f5f7fa 0%, #e3e8f0 100%);
}

.sticky-top {
  z-index: 1020;
}
</style>
