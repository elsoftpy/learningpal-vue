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

const localValue = ref(props.modelValue);

/**
 * Locale-dependent mask
 * en   -> mm-dd-yyyy
 * es/pt -> dd/mm/yyyy
 */
const maskOptions = computed(() => {
    if (locale.value === "en") {
        return {
            mask: "##-##-####",   // MM-DD-YYYY
            eager: true,
            tokens: {}
        };
    }

    return {
        mask: "##/##/####",       // DD/MM/YYYY
        eager: true,
        tokens: {}
    };
});

/**
 * Watch input change → emit values
 */
watch(localValue, (val) => {
    emit("update:modelValue", val || "");

    // Emit unmasked value (only digits)
    const unmasked = val.replace(/\D/g, "");
    emit("update:unmasked", unmasked);
});

/**
 * Sync when external modelValue changes
 */
watch(
    () => props.modelValue,
    (v) => {
        if (v !== localValue.value) localValue.value = v;
    }
);
</script>
