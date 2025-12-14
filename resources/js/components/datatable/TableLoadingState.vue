<template>
    <div class="flex flex-col w-full gap-4" :aria-busy="isLoading">
        <!-- Loading Skeleton -->
        <SkeletonBuilder 
            v-if="isLoading" 
            :perPage="rows" 
            :count="skeletonCount" 
        />

        <!-- Actual Table Content stays mounted for focus retention -->
        <div
            class="w-full"
            :class="isLoading ? 'hidden' : 'block'"
        >
            <slot />
        </div>
    </div>
</template>

<script setup>
import SkeletonBuilder from '@/components/common/SkeletonBuilder.vue';

defineProps({
    isLoading: {
        type: Boolean,
        required: true,
    },
    rows: {
        type: Number,
        default: 5,
    },    
    skeletonCount: {
        type: Number,
        default: 5,
    },
});
</script>
