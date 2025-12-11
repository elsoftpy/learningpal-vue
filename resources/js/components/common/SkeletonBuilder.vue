<template>
    <div class="w-full">
        <Skeleton width="100%" height="4rem" class="mb-6" />
        <Skeleton width="100%" height="2rem" class="mb-4" />
        <div v-for="n in props.perPage" :key="n" class="flex items-center mb-3">
            <Skeleton width="40px" height="40px" shape="circle" class="mr-4" />
            <div v-for="m in props.columnCount" :key="m" class="flex space-x-2 w-full">
                <Skeleton :class="widthClass" />
            </div>
        </div>
    </div>
</template>
<script setup>
import { computed } from 'vue';
import Skeleton from 'primevue/skeleton';
const props = defineProps({
    perPage: {
        type: Number,
        default: 10,
    },
    columnCount: {
        type: Number,
        default: 3,
    },
});
console.log('columnCount:', props.columnCount);
console.log('perPage:', props.perPage);
const widthClass = computed(() => {
    return `w-1/${props.columnCount}`;
});
console.log('widthClass:', widthClass.value);
</script>