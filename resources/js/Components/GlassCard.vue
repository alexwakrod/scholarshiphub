<template>
  <div
    :class="[
      'glass-card rounded-3xl transition-all duration-500',
      variantClass
    ]"
  >
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'surface',
    validator: (v) => ['surface', 'elevated', 'hero', 'ultra-hero'].includes(v),
  },
});

const variantClass = computed(() => {
  switch (props.variant) {
    case 'elevated':
      return 'glass-elevated';
    case 'hero':
      return 'glass-hero';
    case 'ultra-hero':
      return 'glass-ultra-hero';
    default:
      return 'glass-surface';
  }
});
</script>

<style scoped>
/* ================================================================
   GLASS CARD – Z‑PLANE DEPTH HIERARCHY
   ================================================================ */

/* Surface – light, subtle glass (default) */
.glass-surface {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.4),
    0 4px 16px rgba(0, 0, 0, 0.06);
}
.dark .glass-surface {
  background: rgba(0, 0, 0, 0.2);
  border-color: rgba(255, 255, 255, 0.06);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.06),
    0 4px 16px rgba(0, 0, 0, 0.3);
}

/* Elevated – thicker glass slab with prominent shadow */
.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.6),
    0 8px 32px rgba(0, 0, 0, 0.12);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.06),
    0 8px 32px rgba(0, 0, 0, 0.5);
}

/* Hero – heavy glass for overlays */
.glass-hero {
  background: rgba(255, 255, 255, 0.45);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(0, 0, 0, 0.12);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.7),
    0 16px 64px rgba(0, 0, 0, 0.2);
}
.dark .glass-hero {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.12);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.08),
    0 16px 64px rgba(0, 0, 0, 0.6);
}

/* Ultra‑Hero – maximum depth, heavy blur */
.glass-ultra-hero {
  background: rgba(255, 255, 255, 0.55);
  backdrop-filter: blur(40px);
  -webkit-backdrop-filter: blur(40px);
  border: 1px solid rgba(0, 0, 0, 0.15);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.8),
    0 24px 80px rgba(0, 0, 0, 0.3);
}
.dark .glass-ultra-hero {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.15);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.1),
    0 24px 80px rgba(0, 0, 0, 0.7);
}
</style>