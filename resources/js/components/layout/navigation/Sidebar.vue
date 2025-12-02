<template>
    <aside class="w-64 h-[calc(100vh-4.5rem)] z-30 fixed md:relative top-0 left-0 transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
        <div class="h-full px-3 py-4 overflow-y-auto bg-blue-200 dark:bg-blue-800">
            <NavigationMenu />
        </div>
    </aside>
</template>
<script setup>
import NavigationMenu from './NavigationMenu.vue';

</script>
