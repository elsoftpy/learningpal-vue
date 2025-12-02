<template>
    <nav class="fixed md:relative top-0 z-40 w-full bg-blue-200 shadow-lg border-blue-200 dark:bg-blue-800 dark:border-blue-700">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start">
                    <router-link :to="{name: 'dashboard'}" class="flex ms-2 md:me-24">
                        <img :src="brandLogo" class="h-14 me-3" alt="Company Logo">
                        <div class="hidden md:flex align-top">
                            <span class="self-center text-xl font-semibold whitespace-nowrap text-slate-900 dark:text-white">
                                {{ appName }}
                            </span>
                        </div>
                    </router-link>
                    <div class="hidden md:flex ms-4 space-x-2 items-center">
                        <ThemeModeSwitcher />
                    </div>
                </div>
                <div class="hidden sm:flex sm:space-x-2 sm:items-center sm:ms-6">
                    <UserActions v-if="auth.isAuthenticated" />
                </div>
                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="openMobile = !openMobile" class="inline-flex items-center justify-center p-2 rounded-md text-slate-900 dark:text-white hover:text-slate-500 dark:hover:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 focus:outline-none focus:bg-slate-100 dark:focus:bg-slate-900 focus:text-slate-500 dark:focus:text-slate-400 transition duration-150 ease-in-out" >
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path v-if="!openMobile" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <!-- Mobile menu, show/hide based on menu state. -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <div v-show="openMobile" class="sm:hidden mt-2">
                    <ul class="flex justify-end space-y-2 font-medium">
                        <li 
                            @click="handleNavigationClick"
                            class="flex w-full justify-between items-center p-2"
                        >
                            <span class="text-xl font-semibold whitespace-nowrap text-slate-900 dark:text-white">{{ appName }}</span>
                            <ThemeModeSwitcher />
                            <UserActions v-if="auth.isAuthenticated" />
                        </li>
                    </ul>
                    <div
                        @click="handleNavigationClick" 
                        class="pt-2 pb-3 space-y-1"
                    >
                        <NavigationMenu />
                    </div>
                </div>
            </Transition>
        </div>
    </nav>
</template>

<script setup>
import { ref } from 'vue';
import ThemeModeSwitcher from '../ThemeModeSwitcher.vue';
import UserActions from '../UserActions.vue';
import NavigationMenu from './NavigationMenu.vue';
import { useAuthStore } from '@/stores/auth';
import brandLogo from '@/images/brandLogo.png';

const openMobile = ref(false);
const auth = useAuthStore();
const appName = import.meta.env.VITE_APP_NAME || document.title || 'Laravel';

const handleNavigationClick = (event) => {
    const target = event.target.closest('a');

    if (target) {
        openMobile.value = false; // Close the mobile menu on navigation click
    }
}
</script>
