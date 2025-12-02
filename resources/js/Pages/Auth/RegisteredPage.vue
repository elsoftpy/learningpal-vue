<template>
  <AppLayout :title="$t('Dashboard')">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <h2 class="text-2xl font-bold mb-4">{{ $t('Welcome') }}, {{ userName }}!</h2>
            <p>{{ $t("You're registered!") }}</p>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
          <Card>
            <template #title>
              <div class="flex items-center justify-between">
                <span>{{ $t('Total Users') }}</span>
                <i class="pi pi-users text-blue-500 text-2xl"></i>
              </div>
            </template>
            <template #content>
              <p class="text-3xl font-bold">1,234</p>
              <p class="text-sm text-gray-500">+12% from last month</p>
            </template>
          </Card>

          <Card>
            <template #title>
              <div class="flex items-center justify-between">
                <span>{{ $t('Active Students') }}</span>
                <i class="pi pi-graduation-cap text-green-500 text-2xl"></i>
              </div>
            </template>
            <template #content>
              <p class="text-3xl font-bold">567</p>
              <p class="text-sm text-gray-500">+8% from last month</p>
            </template>
          </Card>

          <Card>
            <template #title>
              <div class="flex items-center justify-between">
                <span>{{ $t('Lessons This Week') }}</span>
                <i class="pi pi-book text-purple-500 text-2xl"></i>
              </div>
            </template>
            <template #content>
              <p class="text-3xl font-bold">89</p>
              <p class="text-sm text-gray-500">+5% from last week</p>
            </template>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuth } from '@/composables/useAuth'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from 'primevue/card'

const { t: $t } = useI18n()
const { userName } = useAuth()
</script>