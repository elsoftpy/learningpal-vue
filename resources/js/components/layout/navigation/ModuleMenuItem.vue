<template>
  <li>
    <button 
        type="button" 
        @click="toggle" 
        :class="[
            'flex items-center w-full p-2 text-base hover:bg-blue-300 transition duration-75 rounded-lg group', 
            active ? 'bg-blue-300 hover:bg-blue-400 dark:text-slate-900!' : 'dark:text-white dark:hover:text-slate-900'
        ]"
    >
        <IconWrapper 
            :name="icon" 
            class="w-5 h-5"
        />
        <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">{{ moduleName }}</span>
        <ArrowIcon :open="open" />
    </button>   

    <SmoothHeight :show="open">
      <ul 
        class="p-2 space-y-2 overflow-hidden text-sm font-medium text-accent-900 rounded-md shadow-inner dark:text-white bg-slate-50 dark:bg-slate-900" 
    >
        <slot name="items" />
      </ul>
    </SmoothHeight>
  </li>
</template>

<script setup>
import { computed } from 'vue'
import { useSidebarStore } from '@/stores/sidebar'
import IconWrapper from '@/components/common/IconWrapper.vue'
import ArrowIcon from '@/components/common/ArrowIcon.vue'
import SmoothHeight from '@/components/transitions/SmoothHeight.vue'

const props = defineProps({
  module: { type: String, required: true },
  moduleName: { type: String, required: true },
  icon: { type: String, default: 'gear' }
})

const sidebarStore = useSidebarStore();
const open = computed(() => sidebarStore.openModule === props.module);
const active = computed(() => sidebarStore.activeModule === props.module);

function toggle() {
  sidebarStore.toggleModule(props.module);
}
</script>
