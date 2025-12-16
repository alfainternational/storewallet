import { createRouter, createWebHistory } from 'vue-router';
import store from '../store';

// Lazy loading components
const Home = () => import('../pages/Home.vue');
const Products = () => import('../pages/Products.vue');
const ProductDetail = () => import('../pages/ProductDetail.vue');
const Auctions = () => import('../pages/Auctions.vue');
const AuctionDetail = () => import('../pages/AuctionDetail.vue');
const Cart = () => import('../pages/Cart.vue');
const Checkout = () => import('../pages/Checkout.vue');

// Auth pages
const Login = () => import('../pages/auth/Login.vue');
const Register = () => import('../pages/auth/Register.vue');
const ForgotPassword = () => import('../pages/auth/ForgotPassword.vue');

// User Dashboard
const UserDashboard = () => import('../pages/user/Dashboard.vue');
const UserOrders = () => import('../pages/user/Orders.vue');
const UserWallet = () => import('../pages/user/Wallet.vue');
const UserRemittances = () => import('../pages/user/Remittances.vue');
const UserProfile = () => import('../pages/user/Profile.vue');

// Merchant Dashboard
const MerchantDashboard = () => import('../pages/merchant/Dashboard.vue');
const MerchantProducts = () => import('../pages/merchant/Products.vue');
const MerchantOrders = () => import('../pages/merchant/Orders.vue');
const MerchantAuctions = () => import('../pages/merchant/Auctions.vue');

// Shipping Company
const ShipperDashboard = () => import('../pages/shipper/Dashboard.vue');
const ShipperShipments = () => import('../pages/shipper/Shipments.vue');
const ShipperBids = () => import('../pages/shipper/Bids.vue');

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home,
    meta: { title: 'home.title' }
  },
  {
    path: '/products',
    name: 'products',
    component: Products,
    meta: { title: 'products.title' }
  },
  {
    path: '/products/:id',
    name: 'product-detail',
    component: ProductDetail,
    meta: { title: 'products.detail_title' }
  },
  {
    path: '/auctions',
    name: 'auctions',
    component: Auctions,
    meta: { title: 'auctions.title' }
  },
  {
    path: '/auctions/:id',
    name: 'auction-detail',
    component: AuctionDetail,
    meta: { title: 'auctions.detail_title' }
  },
  {
    path: '/cart',
    name: 'cart',
    component: Cart,
    meta: { title: 'cart.title' }
  },
  {
    path: '/checkout',
    name: 'checkout',
    component: Checkout,
    meta: { title: 'checkout.title', requiresAuth: true }
  },

  // Auth routes
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { title: 'auth.login', guest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { title: 'auth.register', guest: true }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPassword,
    meta: { title: 'auth.forgot_password', guest: true }
  },

  // User dashboard routes
  {
    path: '/dashboard',
    name: 'user-dashboard',
    component: UserDashboard,
    meta: { title: 'dashboard.title', requiresAuth: true }
  },
  {
    path: '/my-orders',
    name: 'user-orders',
    component: UserOrders,
    meta: { title: 'orders.my_orders', requiresAuth: true }
  },
  {
    path: '/wallet',
    name: 'user-wallet',
    component: UserWallet,
    meta: { title: 'wallet.title', requiresAuth: true }
  },
  {
    path: '/remittances',
    name: 'user-remittances',
    component: UserRemittances,
    meta: { title: 'remittances.title', requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'user-profile',
    component: UserProfile,
    meta: { title: 'profile.title', requiresAuth: true }
  },

  // Merchant routes
  {
    path: '/merchant/dashboard',
    name: 'merchant-dashboard',
    component: MerchantDashboard,
    meta: { title: 'merchant.dashboard', requiresAuth: true, role: 'merchant' }
  },
  {
    path: '/merchant/products',
    name: 'merchant-products',
    component: MerchantProducts,
    meta: { title: 'merchant.products', requiresAuth: true, role: 'merchant' }
  },
  {
    path: '/merchant/orders',
    name: 'merchant-orders',
    component: MerchantOrders,
    meta: { title: 'merchant.orders', requiresAuth: true, role: 'merchant' }
  },
  {
    path: '/merchant/auctions',
    name: 'merchant-auctions',
    component: MerchantAuctions,
    meta: { title: 'merchant.auctions', requiresAuth: true, role: 'merchant' }
  },

  // Shipping company routes
  {
    path: '/shipper/dashboard',
    name: 'shipper-dashboard',
    component: ShipperDashboard,
    meta: { title: 'shipper.dashboard', requiresAuth: true, role: 'shipping_company' }
  },
  {
    path: '/shipper/shipments',
    name: 'shipper-shipments',
    component: ShipperShipments,
    meta: { title: 'shipper.shipments', requiresAuth: true, role: 'shipping_company' }
  },
  {
    path: '/shipper/bids',
    name: 'shipper-bids',
    component: ShipperBids,
    meta: { title: 'shipper.bids', requiresAuth: true, role: 'shipping_company' }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  }
});

// Navigation guards
router.beforeEach((to, from, next) => {
  const isAuthenticated = store.getters['auth/isAuthenticated'];
  const user = store.state.auth.user;

  // Check if route requires authentication
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!isAuthenticated) {
      next({
        path: '/login',
        query: { redirect: to.fullPath }
      });
    } else if (to.meta.role && user.role !== to.meta.role) {
      // Check role permission
      next('/dashboard');
    } else {
      next();
    }
  } else if (to.matched.some(record => record.meta.guest)) {
    // Guest only routes (login, register)
    if (isAuthenticated) {
      next('/dashboard');
    } else {
      next();
    }
  } else {
    next();
  }
});

// Update page title after navigation
router.afterEach((to) => {
  const locale = store.state.lang.locale;
  const title = to.meta.title || 'home.title';
  document.title = `${title} - StoreWallet`;
});

export default router;
