<template>
  <component
    :is="iconComponent"
    :size="size"
    :weight="weight"
    :aria-label="ariaLabel || props.name"
    role="img"
    class="inline-block align-middle text-current"
    v-bind="$attrs"
  />
</template>
<script setup>
import * as PhosphorIcons from '@phosphor-icons/vue'
import { computed } from 'vue'

const props = defineProps({
  name: { type: String, required: true },
  size: { type: [Number, String], default: 20 },
  weight: { type: String, default: 'regular' },
  ariaLabel: { type: String, default: '' }
});

const iconComponent = computed(() => {
  const key = `Ph${toPascalCase(props.name)}`;
  return PhosphorIcons[key] || PhosphorIcons.PhSealQuestion
});

function toPascalCase(str) {
  return str
    .split('-')
    .map(part => part[0]?.toUpperCase() + part.slice(1))
    .join('');
}
</script>