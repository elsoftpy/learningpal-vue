import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth'; 
import { title } from '@primeuix/themes/aura/card';
import { header } from '@primeuix/themes/aura/accordion';

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
      headerIcon: 'list',
      crud: 'read',
    },
  },
  {
    path: '/settings/users/create',
    name: 'settings.users.create',
    component: () => import('../Pages/settings/users/UserProfilePage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'settings', 
      title: 'Create User',
      headerIcon: 'plus',
      crud: 'create',
    },
  },
  {
    path: '/settings/users/:id/profile',
    name: 'settings.users.profile',
    component: () => import('../Pages/settings/users/UserProfilePage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'settings', 
      title: 'User Profile',
      headerIcon: 'pencil',
      crud: 'edit.auth-user',
    },
  },
  {
    path: '/settings/users/:id/data',
    name: 'settings.users.data.edit',
    component: () => import('../Pages/settings/users/UserProfilePage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'settings', 
      title: 'Edit User',
      headerIcon: 'pencil',
      crud: 'edit',
    },
  },
  {
    path: '/settings/languages',
    name: 'settings.languages.list',
    component: () => import('../Pages/settings/languages/LanguagesListPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'settings', 
      title: 'Languages',
      headerIcon: 'list',
      crud: 'read',
    },
  },
  {
    path: '/settings/languages/create',
    name: 'settings.languages.create',
    component: () => import('../Pages/settings/languages/LanguageFormPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'settings', 
      title: 'Create Language',
      headerIcon: 'plus',
      crud: 'create',
    },
  },
  {
    path: '/settings/languages/:id/data',
    name: 'settings.languages.edit',
    component: () => import('../Pages/settings/languages/LanguageFormPage.vue'),
    meta: { 
      requiresAuth: true,
      module: 'settings', 
      title: 'Edit Language',
      headerIcon: 'pencil',
      crud: 'edit',
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
      headerIcon: 'list',
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
      headerIcon: 'list',
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
