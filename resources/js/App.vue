<template>
  <div v-if="!authStore.ready" class="min-h-screen flex items-center bg-gray-100">
    <div class="text-center">
      <ProgressSpinner 
        style="width: 50px; height: 50px"
        strokeWidth="4"
        animationDuration="1s"
      />
      <p class="mt-4 text-gray-600">
        {{ $t('Loading') }}...
      </p>
    </div>
  </div>

  <component v-else :is="layout">
    <router-view v-slot="{ Component }">
      <Transition name="fade" mode="out-in">
        <component :is="Component" />
      </Transition>
    </router-view>
  </component>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useSidebarStore } from '@/stores/sidebar';
import { useI18n } from 'vue-i18n';
import ProgressSpinner from 'primevue/progressspinner';
import AppLayout from '@/Layouts/AppLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const route = useRoute();
const authStore = useAuthStore();
const sidebarStore = useSidebarStore();
const { t: $t } = useI18n();

const layout = computed(() => {
  if (route.meta.requiresAuth) {
    return AppLayout;
  }

  if (route.meta.guestOnly) {
    return GuestLayout;
  }

  return authStore.isAuthenticated ? AppLayout : GuestLayout;
});

watch(
  () => route.fullPath,
  () => {
    sidebarStore.updateFromRoute(route);
  },
  { immediate: true }
);
</script>
<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>