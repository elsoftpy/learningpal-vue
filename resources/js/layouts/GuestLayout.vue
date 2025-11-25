<template>
    <div class="font-sans text-gray-900 antialiased">
        <div class="flex items-center min-h-screen p-6 bg-blue-900">
            <div class="flex-1 h-full max-w-4xl mx-auto overflow-hidden bg-white rounded-lg shadow-xl">
                <div class="flex flex-col overflow-y-auto md:flex-row">
                    <!-- Left side with image -->
                    <div class="h-32 md:h-auto md:w-1/2 bg-blue-200">
                        <img
                            aria-hidden="true"
                            class="object-contain w-full h-full"
                            :src="brandLogo"
                            alt="Office"
                        />
                    </div>
                    
                    <!-- Right side with form -->
                    <div class="flex items-center justify-center p-6 sm:p-12 md:w-1/2">
                        <div class="w-full">
                            <h1 class="mb-4 text-xl font-semibold text-gray-700">
                                {{ title }}
                            </h1>
                            <slot />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import brandLogo from '@/images/brandLogo.png';
defineProps({
    title: String
});
</script>