import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth'; 
import { title } from '@primeuix/themes/aura/card';

const routes = [
  /* Default Route */
  {
    path: '/',
    redirect : '/dashboard',
    name: 'home',
    meta: {
      module: null,
    },
  },
  /* Guest Routes */
  {
    path: '/login',
    name: 'login',
    component: () => import('../Pages/Auth/LoginPage.vue'),
    meta: { 
      guestOnly: true,
      title: 'Login',
    },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../Pages/Auth/RegisterPage.vue'),
    meta: { 
      guestOnly: true, 
      title: 'Register', 
    },
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('../Pages/Auth/ForgotPasswordPage.vue'),
    meta: { 
      guestOnly: true, 
      title: 'Forgot Password', 
    },
  },
  /* Protected Routes */
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('../Pages/DashboardPage.vue'),
    meta: { 
      requiresAuth: true,
      module: null,
    },
  },
  /* Settings Module */
  {
    path: '/settings/users',
    name: 'settings.users.list',
    component: () => import('../Pages/settings/users/UsersListPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'settings', 
      title: 'Users List',
      crud: 'read',
    },
  },
  {
    path: '/settings/users/:userId/profile',
    name: 'settings.users.profile',
    component: () => import('../Pages/settings/users/UserProfilePage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'settings', 
      title: 'User Profile',
      crud: 'edit.auth-user',
    },
  },
  /* Academic Module */
  {
    path: '/academic/settings/language-levels',
    name: 'academic.settings.language-levels.list',
    component: () => import('../Pages/academic/settings/language-levels/LanguageLevelsList.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academic', 
      submodule: 'settings', 
      title: 'Language Levels',
      crud: 'read',
    },
  },
  {
    path: '/academic/classes/class-schedules',
    name: 'academic.classes.class-schedules.list',
    component: () => import('../Pages/academic/classes/class-schedules/ClassSchedulesList.vue'),
    meta: { 
      requiresAuth: true,
      module: 'academic', 
      submodule: 'classes', 
      title: 'Class Schedules',
      crud: 'read',
    },
  },
  /* Fallback Route */
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../Pages/NotFoundPage.vue'),
  }
];

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore(); 

  
  const isAuthenticated = authStore.isAuthenticated;

  if (to.meta.requiresAuth && !isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } });
  }

  if (to.meta.guestOnly && isAuthenticated) {
    const redirect = to.query.redirect || { name: 'dashboard' };
    return next(redirect);
  }

  next();
  
});

export default router;
