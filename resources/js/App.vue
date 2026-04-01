<template>
  <Toast position="top-right" />
  <div
    v-if="!authStore.ready"
    class="app-boot-screen"
  >
    <div class="app-boot-glow app-boot-glow-left" aria-hidden="true"></div>
    <div class="app-boot-glow app-boot-glow-right" aria-hidden="true"></div>

    <div class="app-boot-panel">
      <div class="app-boot-logo-shell">
        <img
          :src="brandLogo"
          :alt="$t('Welcome')"
          class="app-boot-logo"
        />
      </div>

      <div class="app-boot-copy">
        <p class="app-boot-title">
          IPL LearningPal
        </p>
        <p class="app-boot-subtitle">
          {{ $t('Loading') }}...
        </p>
      </div>

      <div class="app-boot-dots" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </div>
  </div>

  <div
    v-else-if="authStore.isLoggingOut"
    class="fixed inset-0 z-[1000] flex items-center justify-center bg-slate-950/45 backdrop-blur-sm"
  >
    <div class="rounded-2xl bg-white px-8 py-6 shadow-2xl ring-1 ring-slate-200">
      <div class="flex items-center gap-4">
        <ProgressSpinner
          style="width: 42px; height: 42px"
          strokeWidth="4"
          animationDuration="0.8s"
        />
        <div>
          <p class="text-base font-semibold text-slate-900">
            {{ $t('Logging out...') }}
          </p>
          <p class="text-sm text-slate-500">
            {{ $t('Please wait') }}
          </p>
        </div>
      </div>
    </div>
  </div>

  <component v-else :is="layout">
    <router-view v-slot="{ Component }">
      <Transition name="fade" mode="out-in">
        <component :is="Component" />
      </Transition>
    </router-view>
  </component>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useSidebarStore } from '@/stores/sidebar';
import { useI18n } from 'vue-i18n';
import ProgressSpinner from 'primevue/progressspinner';
import Toast from 'primevue/toast';
import AppLayout from '@/Layouts/AppLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import brandLogo from '@/images/brandLogo.png';

const route = useRoute();
const authStore = useAuthStore();
const sidebarStore = useSidebarStore();
const { t: $t } = useI18n();

const layout = computed(() => {
  if (route.meta.requiresAuth) {
    return AppLayout;
  }

  if (route.meta.guestOnly) {
    return GuestLayout;
  }

  return authStore.isAuthenticated ? AppLayout : GuestLayout;
});

watch(
  () => route.fullPath,
  () => {
    sidebarStore.updateFromRoute(route);
  },
  { immediate: true }
);
</script>
<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.app-boot-screen {
  position: relative;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: 2rem;
  background:
    radial-gradient(circle at top, rgba(96, 165, 250, 0.26), transparent 34%),
    linear-gradient(160deg, #1e3a8a 0%, #172554 100%);
}

.app-boot-glow {
  position: absolute;
  border-radius: 9999px;
  background: rgba(147, 197, 253, 0.2);
  filter: blur(6px);
  animation: appBootFloat 6s ease-in-out infinite;
}

.app-boot-glow-left {
  width: 18rem;
  height: 18rem;
  top: -4rem;
  left: -5rem;
}

.app-boot-glow-right {
  width: 14rem;
  height: 14rem;
  right: -3rem;
  bottom: -2rem;
  animation-delay: -3s;
}

.app-boot-panel {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
  width: min(100%, 22rem);
  text-align: center;
}

.app-boot-logo-shell {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 10rem;
  height: 10rem;
  border-radius: 2rem;
  border: 1px solid rgba(191, 219, 254, 0.5);
  background: #ffffff;
  box-shadow: 0 25px 80px rgba(15, 23, 42, 0.34);
  animation: appBootRise 1.2s ease-out forwards, appBootDrift 4.2s ease-in-out infinite 1.2s;
}

.app-boot-logo-shell::before {
  content: "";
  position: absolute;
  inset: -0.9rem;
  border-radius: 2.7rem;
  border: 1px solid rgba(191, 219, 254, 0.34);
  animation: appBootPulse 2.4s ease-out infinite;
}

.app-boot-logo {
  width: 6.3rem;
  height: auto;
  object-fit: contain;
  filter: drop-shadow(0 12px 24px rgba(15, 23, 42, 0.22));
}

.app-boot-copy {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
}

.app-boot-title {
  margin: 0;
  color: rgba(255, 255, 255, 0.96);
  font-size: 1.35rem;
  font-weight: 700;
  letter-spacing: 0.01em;
}

.app-boot-subtitle {
  margin: 0;
  color: rgba(219, 234, 254, 0.78);
  font-size: 0.96rem;
  line-height: 1.55;
}

.app-boot-dots {
  display: inline-flex;
  gap: 0.4rem;
  justify-content: center;
  align-items: center;
}

.app-boot-dots span {
  width: 0.52rem;
  height: 0.52rem;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.86);
  animation: appBootBlink 1.25s ease-in-out infinite;
}

.app-boot-dots span:nth-child(2) {
  animation-delay: 0.18s;
}

.app-boot-dots span:nth-child(3) {
  animation-delay: 0.36s;
}

@keyframes appBootRise {
  from {
    opacity: 0;
    transform: translateY(16px) scale(0.96);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes appBootDrift {
  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(-8px);
  }
}

@keyframes appBootPulse {
  0% {
    opacity: 0.72;
    transform: scale(0.92);
  }

  70%,
  100% {
    opacity: 0;
    transform: scale(1.12);
  }
}

@keyframes appBootBlink {
  0%,
  80%,
  100% {
    opacity: 0.28;
    transform: translateY(0);
  }

  40% {
    opacity: 1;
    transform: translateY(-3px);
  }
}

@keyframes appBootFloat {
  0%,
  100% {
    transform: translate3d(0, 0, 0);
  }

  50% {
    transform: translate3d(0, 14px, 0);
  }
}
</style>
