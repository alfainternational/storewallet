export default {
  namespaced: true,

  state: {
    locale: localStorage.getItem('locale') || 'ar',
    availableLocales: ['ar', 'en'],
    rtlLocales: ['ar']
  },

  getters: {
    locale: state => state.locale,
    isRTL: state => state.rtlLocales.includes(state.locale),
    direction: (state, getters) => getters.isRTL ? 'rtl' : 'ltr',
    availableLocales: state => state.availableLocales
  },

  mutations: {
    SET_LOCALE(state, locale) {
      if (state.availableLocales.includes(locale)) {
        state.locale = locale;
        localStorage.setItem('locale', locale);
        document.documentElement.lang = locale;
        document.documentElement.dir = state.rtlLocales.includes(locale) ? 'rtl' : 'ltr';
      }
    }
  },

  actions: {
    changeLocale({ commit }, locale) {
      commit('SET_LOCALE', locale);
      // Reload page to apply language changes
      window.location.reload();
    },

    toggleLocale({ commit, state }) {
      const newLocale = state.locale === 'ar' ? 'en' : 'ar';
      commit('SET_LOCALE', newLocale);
      window.location.reload();
    }
  }
};
