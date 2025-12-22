<template>
  <div class="auctions-page">
    <div class="page-header bg-primary text-white py-5">
      <div class="container">
        <h1 class="display-4 mb-3">{{ $t('auctions.title') }}</h1>
        <p class="lead">{{ $t('auctions.subtitle') }}</p>
      </div>
    </div>

    <div class="container py-5">
      <div class="row mb-4">
        <div class="col-md-6">
          <div class="input-group">
            <input v-model="searchQuery" @input="handleSearch" type="text" class="form-control" :placeholder="$t('auctions.search_placeholder')" />
            <button class="btn btn-primary" @click="handleSearch">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>
        <div class="col-md-6 text-md-end">
          <div class="btn-group" role="group">
            <button @click="setFilter('all')" :class="['btn', filter === 'all' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('auctions.all') }}</button>
            <button @click="setFilter('active')" :class="['btn', filter === 'active' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('auctions.active') }}</button>
            <button @click="setFilter('ending_soon')" :class="['btn', filter === 'ending_soon' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('auctions.ending_soon') }}</button>
            <button @click="setFilter('won')" :class="['btn', filter === 'won' ? 'btn-primary' : 'btn-outline-primary']" v-if="isAuthenticated">{{ $t('auctions.my_wins') }}</button>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">{{ $t('auctions.filters') }}</h5>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label">{{ $t('auctions.category') }}</label>
                <select v-model="selectedCategory" @change="applyFilters" class="form-select">
                  <option value="">{{ $t('auctions.all_categories') }}</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ currentLocale === 'ar' ? category.name_ar : category.name }}
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('auctions.start_price_range') }}</label>
                <input v-model.number="minPrice" @change="applyFilters" type="number" class="form-control mb-2" :placeholder="$t('auctions.min_price')" />
                <input v-model.number="maxPrice" @change="applyFilters" type="number" class="form-control" :placeholder="$t('auctions.max_price')" />
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('auctions.auction_type') }}</label>
                <select v-model="auctionType" @change="applyFilters" class="form-select">
                  <option value="">{{ $t('auctions.all_types') }}</option>
                  <option value="lowest_bid">{{ $t('auctions.lowest_bid') }}</option>
                  <option value="highest_bid">{{ $t('auctions.highest_bid') }}</option>
                </select>
              </div>
              <button @click="resetFilters" class="btn btn-outline-secondary w-100">{{ $t('auctions.reset_filters') }}</button>
            </div>
          </div>
        </div>

        <div class="col-md-9">
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">{{ $t('common.loading') }}</span>
            </div>
          </div>

          <div v-else-if="filteredAuctions.length > 0" class="row">
            <div v-for="auction in filteredAuctions" :key="auction.id" class="col-md-6 mb-4">
              <AuctionCard :auction="auction" @refresh="fetchAuctions" />
            </div>
          </div>

          <div v-else class="text-center py-5">
            <i class="fas fa-gavel fa-4x text-muted mb-3"></i>
            <h4>{{ $t('auctions.no_auctions') }}</h4>
            <p class="text-muted">{{ $t('auctions.no_auctions_message') }}</p>
          </div>

          <nav v-if="totalPages > 1" class="mt-4">
            <ul class="pagination justify-content-center">
              <li :class="['page-item', currentPage === 1 && 'disabled']">
                <button class="page-link" @click="changePage(currentPage - 1)">{{ $t('common.previous') }}</button>
              </li>
              <li v-for="page in totalPages" :key="page" :class="['page-item', currentPage === page && 'active']">
                <button class="page-link" @click="changePage(page)">{{ page }}</button>
              </li>
              <li :class="['page-item', currentPage === totalPages && 'disabled']">
                <button class="page-link" @click="changePage(currentPage + 1)">{{ $t('common.next') }}</button>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import AuctionCard from '@/components/auction/AuctionCard.vue';

export default {
  name: 'Auctions',
  components: { AuctionCard },
  setup() {
    const store = useStore();
    const { t, locale } = useI18n();

    const auctions = ref([]);
    const categories = ref([]);
    const loading = ref(false);
    const searchQuery = ref('');
    const filter = ref('all');
    const selectedCategory = ref('');
    const minPrice = ref(null);
    const maxPrice = ref(null);
    const auctionType = ref('');
    const currentPage = ref(1);
    const totalPages = ref(1);
    const perPage = 12;

    const currentLocale = computed(() => locale.value);
    const isAuthenticated = computed(() => store.getters['auth/isAuthenticated']);

    const filteredAuctions = computed(() => {
      let result = auctions.value;

      if (searchQuery.value) {
        result = result.filter(auction => {
          const name = currentLocale.value === 'ar' ? auction.product?.name_ar : auction.product?.name;
          return name?.toLowerCase().includes(searchQuery.value.toLowerCase());
        });
      }

      if (filter.value === 'active') {
        result = result.filter(a => a.status === 'active' && new Date(a.end_time) > new Date());
      } else if (filter.value === 'ending_soon') {
        const twentyFourHoursFromNow = new Date(Date.now() + 24 * 60 * 60 * 1000);
        result = result.filter(a => a.status === 'active' && new Date(a.end_time) <= twentyFourHoursFromNow && new Date(a.end_time) > new Date());
      } else if (filter.value === 'won') {
        const userId = store.state.auth.user?.id;
        result = result.filter(a => a.status === 'completed' && a.winner_id === userId);
      }

      if (selectedCategory.value) {
        result = result.filter(a => a.product?.category_id === selectedCategory.value);
      }

      if (minPrice.value !== null) {
        result = result.filter(a => a.start_price >= minPrice.value);
      }

      if (maxPrice.value !== null) {
        result = result.filter(a => a.start_price <= maxPrice.value);
      }

      if (auctionType.value) {
        result = result.filter(a => a.bid_type === auctionType.value);
      }

      return result;
    });

    const fetchAuctions = async () => {
      loading.value = true;
      try {
        const response = await window.axios.get('/auctions', {
          params: { page: currentPage.value, per_page: perPage }
        });
        auctions.value = response.data.auctions;
        totalPages.value = response.data.total_pages || 1;
      } catch (error) {
        console.error('Error fetching auctions:', error);
      }
      loading.value = false;
    };

    const fetchCategories = async () => {
      try {
        const response = await window.axios.get('/categories');
        categories.value = response.data.categories;
      } catch (error) {
        console.error('Error fetching categories:', error);
      }
    };

    const handleSearch = () => {
      currentPage.value = 1;
    };

    const setFilter = (newFilter) => {
      filter.value = newFilter;
      currentPage.value = 1;
    };

    const applyFilters = () => {
      currentPage.value = 1;
    };

    const resetFilters = () => {
      searchQuery.value = '';
      filter.value = 'all';
      selectedCategory.value = '';
      minPrice.value = null;
      maxPrice.value = null;
      auctionType.value = '';
      currentPage.value = 1;
    };

    const changePage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        fetchAuctions();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    };

    onMounted(() => {
      fetchAuctions();
      fetchCategories();
    });

    watch(currentPage, () => {
      fetchAuctions();
    });

    return {
      auctions,
      categories,
      loading,
      searchQuery,
      filter,
      selectedCategory,
      minPrice,
      maxPrice,
      auctionType,
      currentPage,
      totalPages,
      currentLocale,
      isAuthenticated,
      filteredAuctions,
      handleSearch,
      setFilter,
      applyFilters,
      resetFilters,
      changePage,
      fetchAuctions
    };
  }
};
</script>

<style scoped>
.page-header {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}
</style>
