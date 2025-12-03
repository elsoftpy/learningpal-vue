<template>
      <div class="flex w-full bg-slate-50 dark:bg-slate-900 shadow-md rounded-md">
          <div class="w-full p-2">
              <div class="flex-col space-y-2">
                  <div class="flex w-full justify-between items-center bg-blue-500 dark:bg-blue-800 rounded-md p-4">
                      <div class="text-xl text-slate-50">
                          {{ appName }}
                      </div>
                      <div class="text-xs text-slate-300">
                          {{ appVersion }}
                      </div>
                  </div>
                  <div class="flex flex-col items-center justify-center">
                      <ScheduleXCalendar class="w-full" :calendar-app="calendarApp" />
                  </div>
              </div>
          </div>
      </div>
</template>

<script setup>
import { watch } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ScheduleXCalendar } from "@schedule-x/vue";
import {
  createCalendar,
  createViewDay,
  createViewMonthAgenda,
  createViewMonthGrid,
  createViewWeek,
  viewMonthAgenda,
  viewMonthGrid,
} from "@schedule-x/calendar";
import "@schedule-x/theme-default/dist/index.css";
import 'temporal-polyfill/global';
import { useThemeStore } from '@/stores/theme';

const appName = import.meta.env.VITE_APP_NAME || 'LearningPal';
const appVersion = import.meta.env.VITE_APP_VERSION || 'N/A';
const appLocale = import.meta.env.VITE_APP_LOCALE + '-ES'|| 'en-US';
const today = new Date();
const selectedDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;


const themeStore = useThemeStore();

const calendarApp = createCalendar({
  defaultView:  viewMonthGrid.name,
  isDark: themeStore.isDark,
  selectedDate: Temporal.PlainDate.from(selectedDate),
  locale: appLocale,
  views: [
    createViewMonthGrid(),
    createViewMonthAgenda(),
    createViewDay(),
    createViewWeek(),
  ],
  calendars: {
    personal: {
      colorName: "personal",
      lightColors: {
        main: "#3b82f6",
        container: "#dbeafe",
        onContainer: "#1e40af",
      },
      darkColors: {
        main: "#60a5fa",
        container: "#1e40af",
        onContainer: "#dbeafe", 
      }
    },
    work: {
      colorName: "work",
      lightColors: {
        main: "#10b981",
        container: "#d1fae5",
        onContainer: "#065f46",
      },
      darkColors: {
        main: "#34d399",
        container: "#065f46",
        onContainer: "#d1fae5", 
      }
    },
  },
  events: [
    {
      id: 1,
      calendarId: "personal",
      title: 'Event 1',
      start: Temporal.PlainDate.from('2025-12-19'),
      end: Temporal.PlainDate.from('2025-12-19'),
    },
    {
      id: 2,
      calendarId: "personal",
      title: 'Event 2',
      start: Temporal.ZonedDateTime.from('2025-12-20T12:00:00+09:00[Asia/Tokyo]'),
      end: Temporal.ZonedDateTime.from('2025-12-20T13:00:00+09:00[Asia/Tokyo]'),
    },
    {
      id: 3,
      calendarId: "work",
      title: 'Event 2',
      start: Temporal.PlainDate.from('2025-12-19'),
      end: Temporal.PlainDate.from('2025-12-19'),
    },
  ],
});

defineOptions({
  layout: AppLayout,
});

watch(
  () => themeStore.isDark,
  (newIsDark) => {
    if (newIsDark) calendarApp.setTheme('dark');
    else calendarApp.setTheme('light');
  }
);
</script>
