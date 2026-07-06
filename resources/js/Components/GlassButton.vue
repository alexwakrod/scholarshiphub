<template>
  <div class="perspective-1000 transform-style-3d">
    <button
      :type="type"
      :disabled="disabled || loading"
      class="group relative w-full py-3.5 rounded-2xl font-semibold overflow-hidden transition-all duration-400 ease-out disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none focus-visible:ring-2 focus-visible:ring-blue-400/60 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent"
      :class="[
        variant === 'primary' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.4)] hover:shadow-[0_0_60px_rgba(59,130,246,0.4)]' : '',
        variant === 'secondary' ? 'glass-secondary text-white/90' : '',
        variant === 'ghost' ? 'glass-ghost' : '',
        size === 'sm' ? 'px-4 py-2 text-sm' : '',
        size === 'lg' ? 'px-8 py-4 text-lg' : ''
      ]"
      @click="emit('click')"
    >
      <span class="relative z-10 flex items-center justify-center gap-2">
        <span v-if="loading" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
        <slot v-else />
      </span>
      <!-- Glow overlay for primary variant -->
      <div v-if="variant === 'primary'" class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 group-hover:scale-110 transition-all blur-xl"></div>
    </button>
  </div>
</template>

<script setup>
defineProps({
  type: { type: String, default: 'button' },
  variant: { type: String, default: 'primary' },
  size: { type: String, default: 'md' },
  disabled: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
});

const emit = defineEmits(['click']);
</script>

<style scoped>
/* ============================================
   PHYSICAL GLASS BUTTON – THEME‑AWARE 3D
   ============================================ */

.perspective-1000 {
  perspective: 1000px;
}
.transform-style-3d {
  transform-style: preserve-3d;
}

/* Base 3D physics for all buttons */
button {
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  transform: translateY(0) rotateX(0) rotateY(0) scale(1);
}

/* Hover – lift and tilt */
button:hover:not(:disabled) {
  transform: translateY(-6px) rotateX(2deg) rotateY(2deg) scale(1.03);
}

/* Active – press down */
button:active:not(:disabled) {
  transform: scale(0.95) translateY(2px) rotateX(0) rotateY(0);
  transition-duration: 0.1s;
}

/* Disabled / Loading */
button:disabled {
  transform: none !important;
}

/* ============================================
   PRIMARY VARIANT (ENERGY BUTTON)
   ============================================ */
.bg-gradient-to-r {
  /* already defined by Tailwind; keep the classes in template */
}

/* ============================================
   SECONDARY VARIANT (GLASS BUTTON)
   ============================================ */
.glass-secondary {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 8px 32px rgba(0, 0, 0, 0.08);
}
.dark .glass-secondary {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.05);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08), 0 8px 32px rgba(0, 0, 0, 0.4);
}
.glass-secondary:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.2);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.3), 0 20px 40px rgba(0, 0, 0, 0.15);
  backdrop-filter: blur(24px);
}
.dark .glass-secondary:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.15);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12), 0 20px 40px rgba(0, 0, 0, 0.5);
}

/* ============================================
   GHOST VARIANT (MINIMALIST)
   ============================================ */
.glass-ghost {
  background: transparent;
  border: 1px solid transparent;
  color: rgba(255, 255, 255, 0.6);
}
.dark .glass-ghost {
  color: rgba(255, 255, 255, 0.4);
}
.glass-ghost:hover:not(:disabled) {
  color: rgba(255, 255, 255, 0.9);
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.1);
  transform: translateY(-2px) scale(1.02);
}

/* ============================================
   MOBILE RESPONSIVE (disable 3D rotations)
   ============================================ */
@media (max-width: 768px) {
  button:hover:not(:disabled) {
    transform: translateY(-2px) scale(1.01);
  }
  button:active:not(:disabled) {
    transform: scale(0.97) translateY(1px);
  }
}

/* ============================================
   REDUCED MOTION
   ============================================ */
@media (prefers-reduced-motion: reduce) {
  button,
  button:hover,
  button:active {
    transition: none !important;
    transform: none !important;
  }
  .perspective-1000 {
    perspective: none !important;
  }
}
</style>