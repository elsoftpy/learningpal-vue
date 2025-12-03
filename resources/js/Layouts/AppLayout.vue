<template>
  <div v-if="auth.ready">
    <div class="flex h-screen bg-slate-200 dark:bg-slate-600"
         :class="{
           'overflow-hidden': sidebarOpen,
         }"
    >
    <div class="flex flex-col h-full w-full min-h-0">
        <TopNav />
        <div class="flex flex-1 min-h-0">
          <Sidebar class="hidden md:block" />
        <main class="flex-1 overflow-y-auto mt-20 md:mt-0">
          <!-- page container -->
          <div class="w-full mt-2 px-2">
            <router-view />
          </div>
        </main>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import Sidebar from '@/components/layout/navigation/Sidebar.vue';
import TopNav from '@/components/layout/navigation/TopNav.vue';

const auth = useAuthStore();
const sidebarOpen = ref(false);

onMounted( async () => {
  if (!auth.ready) {
    await auth.checkAuth();
  }
});
</script>

