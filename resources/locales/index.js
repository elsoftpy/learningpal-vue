import { createI18n } from 'vue-i18n';
import es from './es.json';
import en from './en.json';
import pt from './pt.json';

// Browser language detection
function getBrowserLocale() {
  const browserLang = navigator.language || navigator.userLanguage;
  return browserLang.split('-')[0]; // Get base language (e.g., 'es' from 'es-ES')
}

const i18n = createI18n({
  legacy: false, // Use Composition API mode (required for Vue 3)
  locale: getBrowserLocale() || 'es', // Default to Spanish, fallback to browser language
  fallbackLocale: 'en', // Fallback to English if translation missing
  messages: {
    es,
    en,
    pt
  }
});

export default i18n;