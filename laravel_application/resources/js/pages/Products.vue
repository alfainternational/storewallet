<template>
  <div class="products-page">
    <div class="container py-5">
      <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
          <div class="filters-sidebar">
            <h5 class="mb-4">{{ $t('products.filter') }}</h5>

            <!-- Search -->
            <div class="filter-group mb-4">
              <input
                type="text"
                v-model="searchQuery"
                @input="applyFilters"
                class="form-control"
                :placeholder="$t('common.search')"
              />
            </div>

            <!-- Categories -->
            <div class="filter-group mb-4">
              <h6>{{ $t('products.filter.category') }}</h6>
              <select
                v-model="filters.category"
                @change="applyFilters"
                class="form-select"
              >
                <option :value="null">{{ $t('common.all') }}</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <!-- Price Range -->
            <div class="filter-group mb-4">
              <h6>{{ $t('products.filter.priceRange') }}</h6>
              <div class="row g-2">
                <div class="col-6">
                  <input
                    type="number"
                    v-model="filters.minPrice"
                    @change="applyFilters"
                    class="form-control"
                    :placeholder="$t('products.filter.minPrice')"
                  />
                </div>
                <div class="col-6">
                  <input
                    type="number"
                    v-model="filters.maxPrice"
                    @change="applyFilters"
                    class="form-control"
                    :placeholder="$t('products.filter.maxPrice')"
                  />
                </div>
              </div>
            </div>

            <!-- City -->
            <div class="filter-group mb-4">
              <h6>{{ $t('products.filter.city') }}</h6>
              <select
                v-model="filters.city"
                @change="applyFilters"
                class="form-select"
              >
                <option :value="null">{{ $t('common.all') }}</option>
                <option v-for="city in cities" :key="city.id" :value="city.id">
                  {{ city.name }}
                </option>
              </select>
            </div>

            <!-- Reset Filters -->
            <button @click="resetFilters" class="btn btn-outline-secondary w-100">
              {{ $t('common.reset') }}
            </button>
          </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
          <!-- Header -->
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>{{ $t('products.allProducts') }}</h2>

            <!-- Sort -->
            <select v-model="sortBy" @change="applySort" class="form-select w-auto">
              <option value="newest">{{ $t('products.filter.newest') }}</option>
              <option value="price_asc">{{ $t('products.filter.priceAsc') }}</option>
              <option value="price_desc">{{ $t('products.filter.priceDesc') }}</option>
              <option value="popular">{{ $t('products.filter.popular') }}</option>
            </select>
          </div>

          <!-- Loading -->
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
          </div>

          <!-- Products Grid -->
          <div v-else-if="products.length > 0" class="row g-4">
            <div
              v-for="product in products"
              :key="product.id"
              class="col-md-6 col-lg-4"
            >
              <ProductCard :product="product" />
            </div>
          </div>

          <!-- No Results -->
          <div v-else class="text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted">{{ $t('products.noProducts') }}</p>
          </div>

          <!-- Pagination -->
          <nav v-if="pagination.total > pagination.perPage" class="mt-5">
            <ul class="pagination justify-content-center">
              <li class="page-item" :class="{ disabled: pagination.currentPage === 1 }">
                <a class="page-link" @click.prevent="changePage(pagination.currentPage - 1)">
                  {{ $t('common.previous') }}
                </a>
              </li>
              <li
                v-for="page in totalPages"
                :key="page"
                class="page-item"
                :class="{ active: page === pagination.currentPage }"
              >
                <a class="page-link" @click.prevent="changePage(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: pagination.currentPage === totalPages }">
                <a class="page-link" @click.prevent="changePage(pagination.currentPage + 1)">
                  {{ $t('common.next') }}
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useRoute, useRouter } from 'vue-router';
import ProductCard from '../components/product/ProductCard.vue';

export default {
  name: 'Products',
  components: { ProductCard },
  setup() {
    const store = useStore();
    const route = useRoute();
    const router = useRouter();

    const loading = ref(false);
    const searchQuery = ref('');
    const sortBy = ref('newest');
    const filters = ref({
      category: null,
      minPrice: null,
      maxPrice: null,
      city: null
    });

    const products = computed(() => store.getters['products/products']);
    const pagination = computed(() => store.getters['products/pagination']);

    const categories = ref([
      { id: 1, name: 'Electronics' },
      { id: 2, name: 'Fashion' }
    ]);

    const cities = ref([
      { id: 1, name: 'Khartoum' },
      { id: 2, name: 'Omdurman' }
    ]);

    const totalPages = computed(() =>
      Math.ceil(pagination.value.total / pagination.value.perPage)
    );

    const fetchProducts = async (page = 1) => {
      loading.value = true;
      await store.dispatch('products/fetchProducts', page);
      loading.value = false;
    };

    const applyFilters = () => {
      store.dispatch('products/applyFilters', {
        ...filters.value,
        search: searchQuery.value
      });
    };

    const applySort = () => {
      // TODO: Implement sorting
      fetchProducts();
    };

    const resetFilters = () => {
      searchQuery.value = '';
      filters.value = {
        category: null,
        minPrice: null,
        maxPrice: null,
        city: null
      };
      store.dispatch('products/resetFilters');
    };

    const changePage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        fetchProducts(page);
        window.scrollTo(0, 0);
      }
    };

    onMounted(() => {
      if (route.query.category) {
        filters.value.category = parseInt(route.query.category);
      }
      fetchProducts();
    });

    return {
      loading,
      searchQuery,
      sortBy,
      filters,
      products,
      pagination,
      categories,
      cities,
      totalPages,
      applyFilters,
      applySort,
      resetFilters,
      changePage
    };
  }
};
</script>

<style scoped lang="scss">
.products-page {
  min-height: 100vh;
  background-color: #f8fafc;

  .filters-sidebar {
    background: white;
    padding: 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 90px;

    .filter-group {
      h6 {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #1e293b;
      }
    }
  }
}
</style>
