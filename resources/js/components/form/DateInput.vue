<template>
    <div class="flex flex-col w-full">
        <label
            v-if="label"
            :for="id"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
        >
            {{ label }}
            <span v-if="mandatory" class="text-red-500">*</span>
        </label>

        <InputText
            :id="id"
            :name="name"
            v-model="localValue"
            v-maska="maskOptions"
            :placeholder="placeholder"
            class="w-full text-right"
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
    },
    mandatory: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue", "update:unmasked"]);

const { locale } = useI18n();

const localValue = ref(props.modelValue);

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
    if (newValue !== localValue.value) {
        localValue.value = newValue;
    }
});

watch(localValue, (val) => {
    emit("update:modelValue", val);
    const digits = (val || '').replace(/\D/g, "");
    emit("update:unmasked", digits);
});
</script>
