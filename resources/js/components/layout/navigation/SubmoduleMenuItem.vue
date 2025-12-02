<template>
    <li>
        <button 
            type="button" 
            @click="toggle()"
            :class="[
                'flex items-center w-full p-2 text-base text-slate-900 transition duration-75 rounded-lg group',
                active ? 'bg-blue-200 text-slate-500 dark:text-slate-900 hover:bg-blue-100 dark:hover:bg-blue-700 dark:hover:text-blue-200' 
                : 'dark:text-white hover:bg-blue-200 dark:hover:bg-blue-700'
            ]"
        >
            <IconWrapper 
                :name="icon" 
                class="w-5 h-5"
                />
            <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">{{ submoduleName }}</span>
            <ArrowIcon :open="open" />
        </button>
        <SmoothHeight :show="open">
            <ul 
                class="p-2 space-y-2 overflow-hidden text-sm font-medium text-accent-900 rounded-md shadow-inner dark:text-white bg-accent-50 dark:bg-slate-900" 
            >
                <slot name="items" />
            </ul>
        </SmoothHeight>
    </li>

</template>

<script setup>
import { computed } from 'vue';
import { useSidebarStore } from '@/stores/sidebar';
import IconWrapper from '@/components/common/IconWrapper.vue';
import ArrowIcon from '@/components/common/ArrowIcon.vue';
import SmoothHeight from '@/components/transitions/SmoothHeight.vue';

const props = defineProps({
  submodule: { type: String, required: true },
  submoduleName: { type: String, required: true },
  icon: { type: String, default: 'gear' }
});

const sidebarStore = useSidebarStore();
const open = computed(() => sidebarStore.openSubmodule === props.submodule);
const active = computed(() => sidebarStore.activeSubmodule === props.submodule);


function toggle() {
    console.log('SubmoduleMenuItem active:', active);
    console.log('SubmoduleMenuItem open:', open);
    console.log('SubmoduleMenuItem submodule:', props.submodule);
    console.log('sidebarStore activeSubmodule:', sidebarStore.activeSubmodule);

    sidebarStore.toggleSubmodule(props.submodule);
}
</script>
