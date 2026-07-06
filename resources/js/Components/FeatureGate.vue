<template>
  <div v-if="isEnabled">
    <slot />
  </div>
  <div v-else>
    <slot name="fallback" />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  feature: { type: String, required: true },
});

const isEnabled = computed(() => {
  try {
    const flags = usePage().props.featureFlags;
    if (!flags) return true; // default show if feature flags not loaded
    return flags[props.feature] !== false;
  } catch (e) {
    console.error('[FeatureGate] computed isEnabled error:', e);
    return true;
  }
});
</script>