<template>
    <div class="flex flex-col w-full">
        <!-- Loading Skeleton -->
        <SkeletonBuilder 
            v-if="isLoading" 
            :perPage="rows" 
            :count="skeletonCount" 
        />
        
        <!-- Actual Table Content -->
        <slot v-else />
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
