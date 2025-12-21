<template>
  <div class="flex flex-col space-y-4">
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
          <div class="flex flex-col items-center justify-center space-y-3">
            <ScheduleXCalendar class="w-full" :calendar-app="calendarApp" />
            <p v-if="sessionsLoading" class="w-full text-xs text-blue-500">Loading sessions…</p>
            <p v-else-if="sessionsError" class="w-full text-xs text-red-500">{{ sessionsError }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="flex w-full bg-slate-50 dark:bg-slate-900 shadow-md rounded-md">
      <div class="w-full p-4 space-y-3">
        <div class="flex items-center justify-between">
          <div class="text-lg font-semibold text-slate-700 dark:text-slate-200">
            {{ $t('Ongoing and Pending Sessions') }}
          </div>
          <button
            v-if="ongoingAndPendingSessions.length"
            type="button"
            class="text-xs text-blue-500 hover:underline"
            @click="fetchOngoingAndPendingSessions"
          >
            {{ $t('Refresh') }}
          </button>
        </div>
        <p v-if="ongoingLoading" class="text-xs text-blue-500">Loading ongoing and pending sessions…</p>
        <p v-else-if="ongoingError" class="text-xs text-red-500">{{ ongoingError }}</p>
        <div v-else-if="!ongoingAndPendingSessions.length" class="text-xs text-slate-500">No ongoing and pending sessions.</div>
        <div v-else class="flex flex-wrap gap-2">
          <Tag
            v-for="session in ongoingAndPendingSessions"
            :key="session.id"
            class="text-xs"
            :style="getCourseTagStyle({ courseName: session.display_course, courseId: session.course_id })"
          >
            <div class="flex flex-col">
              <span class="font-semibold">{{ session.display_course ?? 'Ongoing session' }}</span>
              <span v-if="session.rescheduled_date" class="text-xs">
                {{ session.rescheduled_date }} {{ session.rescheduled_start_time }} - {{ session.rescheduled_end_time }}
              </span>
            </div>
          </Tag>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from "vue";
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
import Tag from 'primevue/tag';
import api from '@/axios';

const appName = import.meta.env.VITE_APP_NAME || 'LearningPal';
const appVersion = import.meta.env.VITE_APP_VERSION || 'N/A';
const appLocale = import.meta.env.VITE_APP_LOCALE + '-ES'|| 'en-US';
const today = new Date();
const selectedDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
const defaultCalendarId = 'classes';
const defaultCalendars = {
  [defaultCalendarId]: {
    colorName: defaultCalendarId,
    lightColors: {
      main: "#2563eb",
      container: "#dbeafe",
      onContainer: "#1e3a8a",
    },
    darkColors: {
      main: "#3b82f6",
      container: "#1e3a8a",
      onContainer: "#dbeafe",
    }
  },
};


const themeStore = useThemeStore();
const sessionsLoading = ref(false);
const sessionsError = ref('');
const courseLookupByName = ref({});
const courseLookupById = ref({});
const calendarDefinitions = ref({ ...defaultCalendars });
const ongoingAndPendingSessions = ref([]);
const ongoingLoading = ref(false);
const ongoingError = ref('');

let activeSessionsController;
let calendarAppRef = null;

const browserTimeZone = (() => {
  try {
    return Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
  } catch (error) {
    console.warn('Unable to resolve browser timezone, defaulting to UTC.', error);
    return 'UTC';
  }
})();

const calendarTimeZone = browserTimeZone;

const slugifyCourseName = (value = '', fallbackIndex = 0) => {
  const slug = value
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
  return slug || `course-${fallbackIndex}`;
};

const ensureColorSet = (colors = {}, defaults) => ({
  main: colors.main || defaults.main,
  container: colors.container || defaults.container,
  onContainer: colors.onContainer || defaults.onContainer,
});

const mapCalendarsResponse = (payload = {}) => {
  const calendarsConfig = {};
  const lookupByName = {};
  const lookupById = {};

  const rawEntries = Array.isArray(payload)
    ? payload
    : Array.isArray(payload.calendars)
      ? payload.calendars
      : [payload];

  let runningIndex = 0;

  rawEntries.forEach((entry) => {
    if (!entry || typeof entry !== 'object') {
      return;
    }

    Object.entries(entry).forEach(([displayName, colors]) => {
      const calendarId = slugifyCourseName(displayName, runningIndex++);
      lookupByName[displayName] = calendarId;
      if (colors?.course_id) {
        lookupById[colors.course_id] = calendarId;
      }
      calendarsConfig[calendarId] = {
        colorName: calendarId,
        lightColors: ensureColorSet(colors?.lightColors, defaultCalendars[defaultCalendarId].lightColors),
        darkColors: ensureColorSet(colors?.darkColors, defaultCalendars[defaultCalendarId].darkColors),
      };
    });
  });

  if (!Object.keys(calendarsConfig).length) {
    calendarsConfig[defaultCalendarId] = defaultCalendars[defaultCalendarId];
  }

  return { calendarsConfig, lookup: { byName: lookupByName, byId: lookupById } };
};

const resolveCalendarId = ({ courseName, courseId }) => {
  if (courseId && courseLookupById.value[courseId]) {
    return courseLookupById.value[courseId];
  }

  if (courseName && courseLookupByName.value[courseName]) {
    return courseLookupByName.value[courseName];
  }

  return defaultCalendarId;
};

const getCourseColors = ({ courseName, courseId }) => {
  const calendarId = resolveCalendarId({ courseName, courseId });
  return calendarDefinitions.value[calendarId] ?? defaultCalendars[defaultCalendarId];
};

const getCourseTagStyle = ({ courseName, courseId }) => {
  const colors = getCourseColors({ courseName, courseId });
  const palette = (themeStore.isDark ? colors.darkColors : colors.lightColors) || colors.lightColors;

  return {
    backgroundColor: palette.container,
    color: palette.onContainer,
    border: `1px solid ${palette.main}`,
  };
};

const normalizeTime = (timeString) => {
  if (!timeString) return null;
  return timeString.length === 5 ? `${timeString}:00` : timeString;
};

const sessionToCalendarEvent = (session) => {
  const sessionDate = Temporal.PlainDate.from(session.date);
  const startTime = normalizeTime(session.start_time);
  const endTime = normalizeTime(session.end_time);

  const start = startTime
    ? sessionDate.toZonedDateTime({ plainTime: Temporal.PlainTime.from(startTime), timeZone: calendarTimeZone })
    : sessionDate;
  const end = endTime
    ? sessionDate.toZonedDateTime({ plainTime: Temporal.PlainTime.from(endTime), timeZone: calendarTimeZone })
    : sessionDate;

  const customContent = session.chat_room_url
    ? `<a href="${session.chat_room_url}" target="_blank" rel="noopener noreferrer">${session.display_course ?? 'Class session'}</a>`
    : undefined;

  return {
    id: session.id,
    calendarId: resolveCalendarId({ courseName: session.display_course, courseId: session.course_id }),
    title: session.display_course ?? 'Class session',
    start,
    end,
    chatRoomUrl: session.chat_room_url,
    _customContent: {
      monthGrid: customContent,
      monthAgenda: customContent,
      dateGrid: customContent,
      timeGrid: customContent,
    },
  };
};

const mapViewNameToPayloadView = (viewName) => {
  if (viewName === 'day') return 'day';
  if (viewName === 'week') return 'week';
  return 'month';
};

const buildRangePayload = (range, calendarInstance) => {
  const currentViewName = calendarInstance?.$app?.calendarState?.view?.value ?? viewMonthGrid.name;

  return {
    start_date: range.start.toPlainDate().toString(),
    end_date: range.end.toPlainDate().toString(),
    view: mapViewNameToPayloadView(currentViewName),
  };
};

const abortActiveSessionsRequest = () => {
  if (activeSessionsController) {
    activeSessionsController.abort();
    activeSessionsController = null;
  }
};

async function fetchSessionsForRange(range, calendarInstance = calendarAppRef) {
  if (!range || !calendarInstance) return;

  abortActiveSessionsRequest();

  const controller = new AbortController();
  activeSessionsController = controller;
  sessionsLoading.value = true;
  sessionsError.value = '';

  try {
    const { data } = await api.post('/lists/sessions', buildRangePayload(range, calendarInstance), {
      signal: controller.signal,
    });

    if (controller.signal.aborted) {
      return;
    }

    const sessions = Array.isArray(data.sessions) ? data.sessions : [];
    const events = sessions.map(sessionToCalendarEvent);
    calendarInstance.events.set(events);
  } catch (error) {
    if (controller.signal.aborted) {
      return;
    }

    console.error('Unable to load calendar sessions', error);
    sessionsError.value = error?.response?.data?.message ?? 'Unable to load sessions.';
  } finally {
    if (!controller.signal.aborted) {
      sessionsLoading.value = false;
    }

    if (activeSessionsController === controller) {
      activeSessionsController = null;
    }
  }
}

async function fetchCalendarsConfig(range, calendarInstance = calendarAppRef) {
  if (!range || !calendarInstance) return;

  try {
    const { data } = await api.post('/lists/calendars', buildRangePayload(range, calendarInstance));
    const { calendarsConfig, lookup } = mapCalendarsResponse(data);

    courseLookupByName.value = lookup.byName;
    courseLookupById.value = lookup.byId;
    calendarDefinitions.value = calendarsConfig;
    calendarInstance.$app?.config?.calendars && (calendarInstance.$app.config.calendars.value = calendarsConfig);
    const theme = themeStore.isDark ? 'dark' : 'light';
    calendarInstance.setTheme(theme);
  } catch (error) {
    console.error('Unable to load calendar color map', error);
  }
}

async function fetchOngoingAndPendingSessions() {
  ongoingLoading.value = true;
  ongoingError.value = '';

  try {
    const { data } = await api.post('/lists/ongoing_and_pending_sessions');
    ongoingAndPendingSessions.value = Array.isArray(data.ongoing_and_pending_sessions) ? data.ongoing_and_pending_sessions : [];
  } catch (error) {
    console.error('Unable to load ongoing and pending sessions', error);
    ongoingError.value = error?.response?.data?.message ?? 'Unable to load ongoing and pending sessions.';
  } finally {
    ongoingLoading.value = false;
  }
}

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
  calendars: defaultCalendars,
  callbacks: {
    onRangeUpdate(range) {
      fetchCalendarsConfig(range);
      fetchSessionsForRange(range);
    },
  },
  events: [],
});

calendarAppRef = calendarApp;

const initialRange = calendarApp.$app?.calendarState?.range?.value;
if (initialRange) {
  fetchCalendarsConfig(initialRange);
  fetchSessionsForRange(initialRange);
}

fetchOngoingAndPendingSessions();

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

onBeforeUnmount(() => {
  abortActiveSessionsRequest();
});
</script>
