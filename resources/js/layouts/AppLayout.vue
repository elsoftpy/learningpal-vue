<template>
  <div :class="{ 'dark bg-gray-700': darkTheme, 'bg-white': !darkTheme }" class="flex h-screen">
    <!-- Sidebar -->
    <Sidebar />

    <div class="flex flex-col flex-1 w-full">
      <!-- Navbar -->
      <Navbar />

      <!-- Main content -->
      <main class="h-full overflow-y-auto">
        <div class="container relative mx-auto px-4 sm:ps-56 grid pt-16">
          <slot />
        </div>
      </main>
    </div>

    <!-- Toasts -->
    <ToastManager />
    <DialogManager />
  </div>
</template>

<script>
import Sidebar from '../components/Sidebar.vue'
import Navbar from '../components/Navbar.vue'
import ToastManager from '../components/ToastManager.vue'
import DialogManager from '../components/DialogManager.vue'
import { useTheme } from '../composables/useTheme.js'

export default {
  name: 'AppLayout',
  components: { Sidebar, Navbar, ToastManager, DialogManager },
  setup() {
    const { darkTheme } = useTheme()
    return { darkTheme }
  }
}
</script>
