<template>
    <div class="relative" ref="wrap">
        <button 
            @click="show = true"
            class="flex items-center gap-2"
        >
            <span class="md:inline text-slate-900 dark:text-white">
                {{ auth.user.name }}
            </span>    
            <img
                :src="auth.user?.profile_photo_url || defaultAvatar"
                alt="User Photo"
                class="w-8 h-8 rounded-full object-cover"
            />
            
            <ArrowIcon :open="show" class="dark:text-white"/>
        </button>

        <div 
            @mouseleave="show = false" 
            v-if="show" 
            class="absolute right-0 mt-2 w-48 py-1 bg-blue-200 dark:bg-blue-700 rounded shadow-lg z-50"
        >
            <router-link
                :to="{ name: 'settings.users.profile', params: { userId: auth.user.id } }"
                class="block px-4 py-2 text-sm text-slate-900 dark:text-white hover:bg-blue-300 dark:hover:bg-blue-600"
            >
                <div class="flex space-x-2 items-center">
                    <svg class="w-5 h-5 text-slate-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ $t('Profile') }}</span>
                </div>
            </router-link>
            <button @click="logout" class="w-full px-4 py-2 text-sm text-slate-900 dark:text-white hover:bg-blue-300 dark:hover:bg-blue-600">
                <div class="flex space-x-2 items-center">
                    <svg class="w-5 h-5 text-slate-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>{{ $t('Log out') }}</span>
                </div>
            </button>
        </div>
    </div>
</template>
<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import ArrowIcon from '@/components/common/ArrowIcon.vue';
import defaultAvatar from '@/images/default-avatar.png';

// const avatar = auth.user.profile_photo_url || 'images/default-avatar.png';
const auth = useAuthStore();
const router = useRouter();
const show = ref(false);

console.log('UserActions auth.user:', auth.user);

function logout() {
    auth.logout().then(() => {
        router.replace({ name: 'login' });
    });
}
</script>