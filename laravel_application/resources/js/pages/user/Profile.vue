<template>
  <div class="profile-page container py-5">
    <h2 class="mb-4">{{ $t('profile.title') }}</h2>

    <div class="row">
      <div class="col-lg-4 mb-4">
        <div class="card text-center">
          <div class="card-body">
            <div class="avatar-upload mb-3">
              <img :src="avatarPreview || user?.avatar || '/images/avatar-placeholder.png'" alt="Avatar" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;" />
              <input ref="avatarInput" @change="handleAvatarChange" type="file" accept="image/*" style="display: none;" />
              <button @click="$refs.avatarInput.click()" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-camera me-2"></i>{{ $t('profile.change_avatar') }}
              </button>
            </div>
            <h4>{{ user?.first_name }} {{ user?.last_name }}</h4>
            <p class="text-muted mb-2">{{ user?.email }}</p>
            <p class="text-muted"><i class="fas fa-phone me-2"></i>{{ user?.phone }}</p>
            <div class="mt-3">
              <span :class="['badge', user?.email_verified_at ? 'bg-success' : 'bg-warning']">
                <i :class="['fas', user?.email_verified_at ? 'fa-check-circle' : 'fa-clock', 'me-1']"></i>
                {{ user?.email_verified_at ? $t('profile.email_verified') : $t('profile.email_not_verified') }}
              </span>
              <span :class="['badge ms-2', user?.phone_verified_at ? 'bg-success' : 'bg-warning']">
                <i :class="['fas', user?.phone_verified_at ? 'fa-check-circle' : 'fa-clock', 'me-1']"></i>
                {{ user?.phone_verified_at ? $t('profile.phone_verified') : $t('profile.phone_not_verified') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">{{ $t('profile.personal_info') }}</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="updateProfile">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('auth.first_name') }}</label>
                  <input v-model="form.first_name" type="text" class="form-control" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('auth.last_name') }}</label>
                  <input v-model="form.last_name" type="text" class="form-control" required />
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('auth.email') }}</label>
                <input v-model="form.email" type="email" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('auth.phone') }}</label>
                <input v-model="form.phone" type="tel" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('profile.address') }}</label>
                <textarea v-model="form.address" class="form-control" rows="3"></textarea>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('profile.city') }}</label>
                  <select v-model="form.city_id" class="form-select">
                    <option value="">{{ $t('profile.select_city') }}</option>
                    <option v-for="city in cities" :key="city.id" :value="city.id">
                      {{ currentLocale === 'ar' ? city.name_ar : city.name }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">{{ $t('profile.country_of_residence') }}</label>
                  <input v-model="form.country_of_residence" type="text" class="form-control" />
                </div>
              </div>
              <div class="form-check mb-3">
                <input v-model="form.is_expatriate" class="form-check-input" type="checkbox" id="expatriate" />
                <label class="form-check-label" for="expatriate">
                  {{ $t('profile.is_expatriate') }}
                </label>
              </div>
              <button type="submit" class="btn btn-primary" :disabled="profileLoading">
                <i class="fas fa-save me-2"></i>
                {{ profileLoading ? $t('common.saving') : $t('profile.save_changes') }}
              </button>
            </form>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">{{ $t('profile.change_password') }}</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="changePassword">
              <div class="mb-3">
                <label class="form-label">{{ $t('profile.current_password') }}</label>
                <input v-model="passwordForm.current_password" type="password" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('profile.new_password') }}</label>
                <input v-model="passwordForm.new_password" type="password" class="form-control" required minlength="8" />
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('profile.confirm_password') }}</label>
                <input v-model="passwordForm.confirm_password" type="password" class="form-control" required />
              </div>
              <button type="submit" class="btn btn-primary" :disabled="passwordLoading">
                <i class="fas fa-lock me-2"></i>
                {{ passwordLoading ? $t('common.updating') : $t('profile.update_password') }}
              </button>
            </form>
          </div>
        </div>

        <div class="card border-danger">
          <div class="card-header bg-danger text-white">
            <h5 class="mb-0">{{ $t('profile.danger_zone') }}</h5>
          </div>
          <div class="card-body">
            <p class="text-muted">{{ $t('profile.delete_account_warning') }}</p>
            <button @click="deleteAccount" class="btn btn-danger">
              <i class="fas fa-trash me-2"></i>{{ $t('profile.delete_account') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

export default {
  name: 'Profile',
  setup() {
    const store = useStore();
    const router = useRouter();
    const { t, locale } = useI18n();

    const form = ref({
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      address: '',
      city_id: '',
      country_of_residence: '',
      is_expatriate: false
    });

    const passwordForm = ref({
      current_password: '',
      new_password: '',
      confirm_password: ''
    });

    const avatarInput = ref(null);
    const avatarPreview = ref(null);
    const profileLoading = ref(false);
    const passwordLoading = ref(false);
    const cities = ref([]);

    const user = computed(() => store.state.auth.user);
    const currentLocale = computed(() => locale.value);

    const fetchCities = async () => {
      try {
        const response = await window.axios.get('/cities');
        cities.value = response.data.cities;
      } catch (error) {
        console.error('Error fetching cities:', error);
      }
    };

    const loadUserData = () => {
      if (user.value) {
        form.value = {
          first_name: user.value.first_name || '',
          last_name: user.value.last_name || '',
          email: user.value.email || '',
          phone: user.value.phone || '',
          address: user.value.address || '',
          city_id: user.value.city_id || '',
          country_of_residence: user.value.country_of_residence || '',
          is_expatriate: user.value.is_expatriate || false
        };
      }
    };

    const handleAvatarChange = (event) => {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          avatarPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
        uploadAvatar(file);
      }
    };

    const uploadAvatar = async (file) => {
      const formData = new FormData();
      formData.append('avatar', file);

      try {
        const response = await window.axios.post('/user/avatar', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        if (response.data.success) {
          store.commit('auth/SET_USER', response.data.user);
          alert(t('profile.avatar_updated'));
        }
      } catch (error) {
        console.error('Error uploading avatar:', error);
        alert(t('profile.avatar_failed'));
      }
    };

    const updateProfile = async () => {
      profileLoading.value = true;
      try {
        const response = await window.axios.put('/user/profile', form.value);
        if (response.data.success) {
          store.commit('auth/SET_USER', response.data.user);
          alert(t('profile.update_success'));
        }
      } catch (error) {
        console.error('Error updating profile:', error);
        alert(error.response?.data?.message || t('profile.update_failed'));
      }
      profileLoading.value = false;
    };

    const changePassword = async () => {
      if (passwordForm.value.new_password !== passwordForm.value.confirm_password) {
        alert(t('profile.password_mismatch'));
        return;
      }

      passwordLoading.value = true;
      try {
        const response = await window.axios.put('/user/password', {
          current_password: passwordForm.value.current_password,
          new_password: passwordForm.value.new_password
        });
        if (response.data.success) {
          alert(t('profile.password_updated'));
          passwordForm.value = { current_password: '', new_password: '', confirm_password: '' };
        }
      } catch (error) {
        console.error('Error changing password:', error);
        alert(error.response?.data?.message || t('profile.password_failed'));
      }
      passwordLoading.value = false;
    };

    const deleteAccount = async () => {
      const confirmText = t('profile.confirm_delete');
      if (!confirm(confirmText)) return;

      const doubleConfirm = prompt(t('profile.type_delete_to_confirm'));
      if (doubleConfirm !== 'DELETE') return;

      try {
        const response = await window.axios.delete('/user/account');
        if (response.data.success) {
          await store.dispatch('auth/logout');
          router.push('/');
        }
      } catch (error) {
        console.error('Error deleting account:', error);
        alert(t('profile.delete_failed'));
      }
    };

    onMounted(() => {
      fetchCities();
      loadUserData();
    });

    return {
      form,
      passwordForm,
      avatarInput,
      avatarPreview,
      profileLoading,
      passwordLoading,
      cities,
      user,
      currentLocale,
      handleAvatarChange,
      updateProfile,
      changePassword,
      deleteAccount
    };
  }
};
</script>
