import { createRouter, createWebHistory } from 'vue-router';

export default createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', redirect: '/login' },
        { path: '/login', component: () => import('../pages/auth/LoginPage.vue') },
        { path: '/register', component: () => import('../pages/auth/RegisterPage.vue') },
        { 
            path: '/dashboard',
            name: 'dashboard', 
            component: () => import('../pages/DashboardPage.vue'),
         },
    ],
});
