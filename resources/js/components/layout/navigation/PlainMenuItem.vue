<template>
  <li>
    <RouterLink :to="route"
      :class="[
        'flex items-center w-full p-2 text-slate-900 transition duration-75 rounded-lg pl-11 hover:bg-slate-200 dark:hover:bg-slate-700', 
        active ? 'bg-slate-300 dark:text-slate-900 hover:bg-slate-400 dark:hover:bg-slate-600 dark:hover:text-slate-200' : 'dark:text-white'
      ]"
      
    >
      {{ title }}
    </RouterLink>
  </li>
</template>

<script setup>
import { computed } from 'vue'
import { useSidebarStore } from '@/stores/sidebar';

const props = defineProps({
  route: { type: [String, Object], required: true },
  title: { type: String, required: true },
  baseRoute: { type: String, default: '' }
});

const sidebarStore = useSidebarStore();
const active = computed(() => sidebarStore.activeItem?.startsWith(props.baseRoute));

</script>
