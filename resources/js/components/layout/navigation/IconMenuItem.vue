<template>
  <li>
    <RouterLink 
        :to="route" 
        :class="[
          'flex items-center p-2 text-gray-900 dark:text-white rounded-lg  hover:bg-gray-100 dark:hover:bg-gray-700 group', 
          active ? 'bg-blue-300 dark:text-slate-900!' : ''
        ]"
        
    >
        <IconWrapper 
            :name="icon" 
            class="h-5 w-5"
        />
        <span class="ms-3">{{ title }}</span>
    </RouterLink>
  </li>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { useSidebarStore } from '@/stores/sidebar';
import IconWrapper from '@/components/common/IconWrapper.vue';

const props = defineProps({
  route: { type: [String, Object], default: () => ({ name: 'dashboard' }) },
  title: { type: String, required: true },
  icon: { type: String, default: 'house-line' }
});


const sidebarStore = useSidebarStore();
const active = computed(() => sidebarStore.activeItem === props.route.name);

</script>
