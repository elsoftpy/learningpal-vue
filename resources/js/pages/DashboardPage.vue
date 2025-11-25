<template>
  <GuestLayout :title="$t('Dashboard')">
    Hello, world! This is the dashboard page.
    </GuestLayout>
</template>

<script setup>
const $t = (key) => {
  const translations = {
    'Dashboard': 'Dashboard'
  }
  return translations[key] || key
}// Add any necessary script logic here
</script>