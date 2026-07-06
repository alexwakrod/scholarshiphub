<template>
  <AppLayout>
    <div class="p-6 max-w-2xl">
      <h1 class="text-2xl font-bold text-white mb-6">Feature Flags</h1>

      <GlassCard variant="elevated" class="p-6">
        <div class="space-y-4">
          <div
            v-for="(enabled, feature) in localFlags"
            :key="feature"
            class="flex items-center justify-between p-3 rounded-lg bg-white/5"
          >
            <div>
              <p class="text-white font-medium capitalize">{{ formatFeatureName(feature) }}</p>
              <p class="text-xs text-white/50">{{ feature }}</p>
            </div>
            <GlassToggle
              :model-value="enabled"
              @update:model-value="(val) => toggleFlag(feature, val)"
            />
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button
            @click="resetDefaults"
            class="px-4 py-2 rounded-lg bg-white/10 text-white text-sm hover:bg-white/20 transition-colors"
          >
            Reset to defaults
          </button>
        </div>
      </GlassCard>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import GlassToggle from '@/Components/GlassToggle.vue';

const props = defineProps({
  flags: { type: Object, required: true },
});

const localFlags = ref({ ...props.flags });

async function toggleFlag(feature, enabled) {
  // Optimistic update
  localFlags.value[feature] = enabled;

  try {
    const response = await axios.post('/admin/feature-flags/toggle', {
      feature,
      enabled,
    });
    // Sync with server response if needed
    if (response.data.flags) {
      localFlags.value = { ...response.data.flags };
    }
  } catch (error) {
    console.error('Toggle feature flag failed:', error);
    // Revert on error
    localFlags.value[feature] = !enabled;
  }
}

async function resetDefaults() {
  // Reset all to true (or fetch defaults from API). We'll toggle each to true.
  const updated = {};
  Object.keys(localFlags.value).forEach(k => {
    updated[k] = true;
  });
  localFlags.value = updated;
  try {
    await Promise.all(
      Object.entries(updated).map(([feature, enabled]) =>
        axios.post('/admin/feature-flags/toggle', { feature, enabled })
      )
    );
    // Refresh page to ensure Inertia shared props update? The shared props are read from cache on each request,
    // but the admin page may not refresh automatically. We'll trigger a reload.
    location.reload();
  } catch (error) {
    console.error('Reset feature flags failed:', error);
  }
}

function formatFeatureName(key) {
  return key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}
</script>