<template>
    <div class="flex flex-col w-full">
        <label
            v-if="label"
            :for="id"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
        >
            {{ label }}
        </label>

        <InputText
            :id="id"
            :name="name"
            v-model="localValue"
            v-maska="maskOptions"
            :placeholder="placeholder"
            class="w-full"
        />
    </div>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import InputText from "primevue/inputtext";
import { useI18n } from "vue-i18n";

const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },
    id: {
        type: String,
        required: true,
    },
    name: {
        type: String,
        default: null,
    },
    label: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: null,
    }
});

const emit = defineEmits(["update:modelValue", "update:unmasked"]);

const { locale } = useI18n();

const usesMonthFirst = (code) => {
    return code === 'en';
};

const digitsToIso = (digits, localeCode) => {
    if (!digits || digits.length !== 8) return null;

    let day, month, year;
    if (usesMonthFirst(localeCode)) {
        // mm-dd-yyyy
        month = digits.substring(0, 2);
        day = digits.substring(2, 4);
        year = digits.substring(4, 8);
    } else {
        // dd/mm/yyyy
        day = digits.substring(0, 2);
        month = digits.substring(2, 4);
        year = digits.substring(4, 8);
    }

    return `${year}-${month}-${day}`;
};

const formatDisplayValue = (value, localeCode) => {
    if (!value) return '';
    
    // Check for ISO format YYYY-MM-DD
    const isoMatch = value.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (isoMatch) {
        const [, year, month, day] = isoMatch;
        if (usesMonthFirst(localeCode)) {
            return `${month}-${day}-${year}`;
        }
        return `${day}/${month}/${year}`;
    }

    // Check for compact format YYYYMMDD (sometimes used)
    if (value.length === 8 && /^\d+$/.test(value)) {
         // Assume it's already in the order implied by locale? 
         // Or assume it's YYYYMMDD? 
         // Actually, if we get raw digits here, it might be from the input itself.
         // But formatDisplayValue is usually for external values.
         // Let's assume external values are ISO or empty.
         return value;
    }

    return value;
};

const localValue = ref(formatDisplayValue(props.modelValue, locale.value));

const maskOptions = computed(() => {
    if (locale.value === "en") {
        return {
            mask: "##-##-####",
            eager: true,
        };
    }
    return {
        mask: "##/##/####",
        eager: true,
    };
});

watch(() => props.modelValue, (newValue) => {
    const display = formatDisplayValue(newValue, locale.value);
    if (display !== localValue.value) {
        localValue.value = display;
    }
});

watch(localValue, (val) => {
    const digits = (val || '').replace(/\D/g, "");
    
    if (digits.length === 8) {
        const iso = digitsToIso(digits, locale.value);
        if (iso) {
            emit("update:modelValue", iso);
        }
    } else if (digits.length === 0) {
        emit("update:modelValue", "");
    }
    
    emit("update:unmasked", digits);
});

watch(locale, (newLocale) => {
    // Re-format the current model value for the new locale
    localValue.value = formatDisplayValue(props.modelValue, newLocale);
});
</script>
