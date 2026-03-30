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
            <div class="flex w-full flex-wrap gap-2">
              <button
                v-for="option in sessionStatusOptions"
                :key="option.value"
                type="button"
                class="rounded-full border px-3 py-1 text-xs font-semibold transition"
                :class="selectedSessionStatus === option.value
                  ? 'border-blue-600 bg-blue-600 text-white'
                  : 'border-slate-300 bg-white text-slate-600 hover:border-blue-400 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500 dark:hover:text-blue-300'"
                @click="toggleSessionStatusFilter(option.value)"
              >
                {{ option.label }}
              </button>
            </div>
            <ScheduleXCalendar class="w-full" :calendar-app="calendarApp" />
            <p v-if="sessionsLoading" class="w-full text-xs text-blue-500">Loading sessions…</p>
            <p v-else-if="sessionsError" class="w-full text-xs text-red-500">{{ sessionsError }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { useI18n } from 'vue-i18n';
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
import { useThemeStore } from '@/stores/theme';
import "@schedule-x/theme-default/dist/index.css";
import 'temporal-polyfill/global';
import api from '@/axios';

const { t: $t } = useI18n();
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
const calendarSessions = ref([]);
const selectedSessionStatus = ref('all');

let activeSessionsController;
let calendarAppRef = null;

const sessionStatusOptions = computed(() => ([
  { value: 'all', label: $t('All') },
  { value: 'scheduled', label: $t('Scheduled') },
  { value: 'pending', label: $t('Pending') },
  { value: 'ongoing', label: $t('Ongoing') },
  { value: 'reprogramed', label: $t('Reprogramed') },
  { value: 'completed', label: $t('Completed') },
  { value: 'canceled', label: $t('Canceled') },
]));

const filteredCalendarSessions = computed(() => {
  if (selectedSessionStatus.value === 'all') {
    return calendarSessions.value;
  }

  return calendarSessions.value.filter((session) => session.status === selectedSessionStatus.value);
});

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

const normalizeTime = (timeString) => {
  if (!timeString) return null;
  return timeString.length === 5 ? `${timeString}:00` : timeString;
};

const formatDisplayTime = (timeString) => {
  const normalized = normalizeTime(timeString);
  if (!normalized) return '';
  const [hours = '', minutes = ''] = normalized.split(':');
  return hours && minutes ? `${hours}:${minutes}` : normalized;
};

const APP_TIMEZONE = import.meta.env.VITE_APP_TIMEZONE || 'UTC';
console.log('APP_TIMEZONE:', APP_TIMEZONE); // Add this line to debug

const buildZonedDateTime = (dateString, timeString) => {
  if (!dateString) {
    return null;
  }

  try {
    const plainDate = Temporal.PlainDate.from(dateString);
    const plainTime = Temporal.PlainTime.from(normalizeTime(timeString) ?? '00:00:00');
    const dateTime = plainDate.toPlainDateTime(plainTime);
    
    console.log('Converting:', { dateString, timeString, timezone: APP_TIMEZONE }); // Debug
    const result = dateTime.toZonedDateTime(APP_TIMEZONE);
    console.log('Result:', result.toString()); // Debug
    
    return result;
  } catch (error) {
    console.error('Unable to build zoned date time for session', { dateString, timeString, error });
    return null;
  }
};

const sessionToCalendarEvent = (session) => {
  const eventDate = session.rescheduled_date ?? session.date;
  const startTimeValue = session.rescheduled_start_time ?? session.start_time;
  const endTimeValue = session.rescheduled_end_time ?? session.end_time;

  const start = buildZonedDateTime(eventDate, startTimeValue) ?? buildZonedDateTime(eventDate, null);
  const end = endTimeValue
    ? buildZonedDateTime(eventDate, endTimeValue) ?? start
    : start;

  const displayStartTime = formatDisplayTime(startTimeValue);
  const displayEndTime = formatDisplayTime(endTimeValue);
  const displayTime = displayStartTime && displayEndTime
    ? `${displayStartTime} - ${displayEndTime}`
    : displayStartTime || displayEndTime || '';
  const eventTitle = session.display_course ?? 'Class session';
  const titleWithTime = eventTitle;
  const statusLabel = session.display_status ?? '';

  const customContent = `
  ${session.chat_room_url 
    ? `<a href="${session.chat_room_url}" target="_blank" rel="noopener noreferrer">` 
    : ''}
    <div class="sx-event-content">
      <div class="sx-event-row">
        <strong class="sx-event-time" title="${eventTitle}">${eventTitle}</strong>
        <div class="sx-event-text" title="${displayTime} ${statusLabel}">
          ${displayTime} 
          ${statusLabel ? `<span class="sx-event-status">${statusLabel}</span>` : ''}
        </div>
      </div>
    </div>
  ${session.chat_room_url ? `</a>` : ''}
`.trim();

  return {
    id: session.id,
    calendarId: resolveCalendarId({ courseName: session.display_course, courseId: session.course_id }),
    title: titleWithTime,
    start,
    end,
    description: [displayTime, statusLabel, session.description].filter(Boolean).join(' • '),
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

    calendarSessions.value = Array.isArray(data.sessions) ? data.sessions : [];
    applyCalendarEvents(calendarInstance);
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

function applyCalendarEvents(calendarInstance = calendarAppRef) {
  if (!calendarInstance) {
    return;
  }

  const events = filteredCalendarSessions.value.map(sessionToCalendarEvent);
  calendarInstance.events.set(events);
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

function toggleSessionStatusFilter(status) {
  selectedSessionStatus.value = selectedSessionStatus.value === status ? 'all' : status;
}

const calendarApp = createCalendar({
  defaultView:  viewMonthGrid.name,
  isDark: themeStore.isDark,
  selectedDate: Temporal.PlainDate.from(selectedDate),
  locale: appLocale,
  timezone: APP_TIMEZONE,
  views: [
    createViewMonthGrid(),
    createViewMonthAgenda(),
    createViewDay(),
    createViewWeek(),
  ],
  calendars: defaultCalendars,
  callbacks: {
    onEventClick(info) {
      if (!info.event?.chatRoomUrl) {
        return;
      }
      window.open(info.event.chatRoomUrl, '_blank', 'noopener');
    },
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

watch(selectedSessionStatus, () => {
  applyCalendarEvents();
});

onBeforeUnmount(() => {
  abortActiveSessionsRequest();
});
</script>
<style scoped>

:deep(.sx__month-grid-day.is-leading-or-trailing) {
  background-color: #ededed !important;
}

:deep(.sx__calendar-wrapper.is-dark
  .sx__month-grid-day.is-leading-or-trailing) {
  background-color: #302f2f !important;
}


:deep(.sx__month-grid-day:not(.is-leading-or-trailing)) {
  background-color: #e7f0ff !important;
}

:deep(.sx__calendar-wrapper.is-dark
  .sx__month-grid-day:not(.is-leading-or-trailing)) {
  background-color: #00081e !important;
}

/* Month grid cell height adjustment */
:deep(.sx__month-grid-cell) {
  height: 3rem !important;
}

/* Month grid event - ensure container has width constraints */
:deep(.sx__month-grid-event) {
  overflow: hidden !important;
}

:deep(.sx__month-grid-event > div) {
  width: 100% !important;
  overflow: hidden !important;
}

:deep(.sx__month-grid-event a) {
  display: block !important;
  width: 100% !important;
  overflow: hidden !important;
}

:deep(.sx__month-grid-event .sx-event-content) {
  padding: 4px 8px !important;
  overflow: hidden !important;
  width: 100% !important;
}

:deep(.sx__month-grid-event .sx-event-row) {
  display: flex !important;
  flex-direction: column !important;
  gap: 2px !important;
  overflow: hidden !important;
  width: 100% !important;
}

/* Month grid event text truncation */
:deep(.sx__month-grid-event .sx-event-time) {
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  display: block !important;
  max-width: 100% !important;
}

:deep(.sx__month-grid-event .sx-event-text) {
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  font-size: 0.75rem !important;
}

/* Fix for week/day view time grid events - minimum height */
:deep(.sx__time-grid-event) {
  min-height: 3rem !important;
}

:deep(.sx__time-grid-event-inner) {
  min-height: 3rem !important;
  display: flex !important;
  flex-direction: column !important;
  justify-content: center !important;
}

/* Truncate long event titles with ellipsis */
:deep(.sx__time-grid-event .sx-event-time) {
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  display: block !important;
  max-width: 100% !important;
}

/* Keep event time/status text on one line */
:deep(.sx__time-grid-event .sx-event-text) {
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  font-size: 0.75rem !important;
  line-height: 1.2 !important;
}

:deep(.sx-event-content) {
  padding: 4px 8px !important;
  overflow: hidden !important;
}

:deep(.sx-event-row) {
  display: flex !important;
  flex-direction: column !important;
  gap: 2px !important;
  overflow: hidden !important;
}
</style>
