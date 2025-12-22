<template>
  <div class="merchant-products container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>{{ $t('merchant.my_products') }}</h2>
      <router-link to="/merchant/products/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>{{ $t('merchant.add_product') }}
      </router-link>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="input-group">
              <input v-model="searchQuery" @input="handleSearch" type="text" class="form-control" :placeholder="$t('merchant.search_products')" />
              <button class="btn btn-outline-secondary">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>
          <div class="col-md-6 text-end">
            <div class="btn-group">
              <button @click="filterProducts('all')" :class="['btn btn-sm', filter === 'all' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('merchant.all') }}</button>
              <button @click="filterProducts('active')" :class="['btn btn-sm', filter === 'active' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('merchant.active') }}</button>
              <button @click="filterProducts('inactive')" :class="['btn btn-sm', filter === 'inactive' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('merchant.inactive') }}</button>
              <button @click="filterProducts('low_stock')" :class="['btn btn-sm', filter === 'low_stock' ? 'btn-primary' : 'btn-outline-primary']">{{ $t('merchant.low_stock') }}</button>
            </div>
          </div>
        </div>

        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary"></div>
        </div>

        <div v-else-if="filteredProducts.length > 0" class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>{{ $t('products.image') }}</th>
                <th>{{ $t('products.name') }}</th>
                <th>{{ $t('products.category') }}</th>
                <th>{{ $t('products.price') }}</th>
                <th>{{ $t('products.stock') }}</th>
                <th>{{ $t('products.sales') }}</th>
                <th>{{ $t('products.status') }}</th>
                <th>{{ $t('common.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in filteredProducts" :key="product.id">
                <td>
                  <img :src="product.main_image || '/images/placeholder.jpg'" :alt="product.name" style="width: 50px; height: 50px; object-fit: cover;" class="rounded" />
                </td>
                <td>
                  <strong>{{ currentLocale === 'ar' ? product.name_ar : product.name }}</strong>
                  <br />
                  <small class="text-muted">SKU: {{ product.sku }}</small>
                </td>
                <td>{{ product.category?.name }}</td>
                <td>
                  <strong class="text-primary">{{ formatMoney(product.price) }}</strong>
                  <br />
                  <small v-if="product.original_price > product.price" class="text-decoration-line-through text-muted">{{ formatMoney(product.original_price) }}</small>
                </td>
                <td>
                  <span :class="['badge', product.stock > 10 ? 'bg-success' : product.stock > 0 ? 'bg-warning' : 'bg-danger']">
                    {{ product.stock }}
                  </span>
                </td>
                <td>{{ product.sales_count || 0 }}</td>
                <td>
                  <span :class="['badge', product.is_active ? 'bg-success' : 'bg-secondary']">
                    {{ product.is_active ? $t('merchant.active') : $t('merchant.inactive') }}
                  </span>
                </td>
                <td>
                  <div class="btn-group">
                    <router-link :to="`/merchant/products/${product.id}/edit`" class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-edit"></i>
                    </router-link>
                    <button @click="toggleStatus(product)" class="btn btn-sm btn-outline-warning">
                      <i :class="['fas', product.is_active ? 'fa-eye-slash' : 'fa-eye']"></i>
                    </button>
                    <button @click="deleteProduct(product.id)" class="btn btn-sm btn-outline-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="text-center py-5">
          <i class="fas fa-box fa-4x text-muted mb-3"></i>
          <h4>{{ $t('merchant.no_products') }}</h4>
          <p class="text-muted">{{ $t('merchant.no_products_message') }}</p>
          <router-link to="/merchant/products/create" class="btn btn-primary">
            {{ $t('merchant.add_first_product') }}
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

export default {
  name: 'MerchantProducts',
  setup() {
    const { t, locale } = useI18n();

    const products = ref([]);
    const loading = ref(false);
    const searchQuery = ref('');
    const filter = ref('all');

    const currentLocale = computed(() => locale.value);

    const filteredProducts = computed(() => {
      let result = products.value;

      if (searchQuery.value) {
        result = result.filter(p =>
          p.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          p.name_ar.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
      }

      if (filter.value === 'active') {
        result = result.filter(p => p.is_active);
      } else if (filter.value === 'inactive') {
        result = result.filter(p => !p.is_active);
      } else if (filter.value === 'low_stock') {
        result = result.filter(p => p.stock < 10);
      }

      return result;
    });

    const formatMoney = (amount) => {
      return new Intl.NumberFormat(locale.value, {
        style: 'currency',
        currency: 'SDG',
        minimumFractionDigits: 2
      }).format(amount);
    };

    const fetchProducts = async () => {
      loading.value = true;
      try {
        const response = await window.axios.get('/merchant/products');
        products.value = response.data.products;
      } catch (error) {
        console.error('Error fetching products:', error);
      }
      loading.value = false;
    };

    const handleSearch = () => {
      // Filtering is reactive
    };

    const filterProducts = (newFilter) => {
      filter.value = newFilter;
    };

    const toggleStatus = async (product) => {
      try {
        await window.axios.put(`/merchant/products/${product.id}`, {
          is_active: !product.is_active
        });
        product.is_active = !product.is_active;
      } catch (error) {
        console.error('Error toggling status:', error);
        alert(t('errors.update_failed'));
      }
    };

    const deleteProduct = async (productId) => {
      if (!confirm(t('merchant.confirm_delete'))) return;

      try {
        await window.axios.delete(`/merchant/products/${productId}`);
        products.value = products.value.filter(p => p.id !== productId);
        alert(t('merchant.delete_success'));
      } catch (error) {
        console.error('Error deleting product:', error);
        alert(t('merchant.delete_failed'));
      }
    };

    onMounted(() => {
      fetchProducts();
    });

    return {
      products,
      loading,
      searchQuery,
      filter,
      currentLocale,
      filteredProducts,
      formatMoney,
      handleSearch,
      filterProducts,
      toggleStatus,
      deleteProduct
    };
  }
};
</script>
