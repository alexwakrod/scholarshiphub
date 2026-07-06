<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-[2000] flex items-center justify-center p-4"
        @click.self="handleBackdropClick"
        @keydown.escape="close"
      >
        <!-- Backdrop with blur -->
        <div
          class="absolute inset-0 bg-black/40 dark:bg-black/60 backdrop-blur-md transition-opacity duration-500"
        ></div>

        <!-- Modal panel -->
        <div
          ref="modalRef"
          class="relative w-full max-w-lg glass-modal-panel rounded-3xl p-6 md:p-8 transition-all duration-500 ease-out"
          :class="entering ? 'modal-entering' : 'modal-entered'"
          role="dialog"
          aria-modal="true"
          tabindex="-1"
          @focusin="trapFocus"
        >
          <!-- Close button -->
          <button
            @click="close"
            class="absolute top-4 right-4 z-10 p-1.5 rounded-xl glass-modal-close text-gray-500 dark:text-white/60 hover:text-gray-900 dark:hover:text-white hover:scale-110 active:scale-90 transition-all duration-300 focus-visible:ring-2 focus-visible:ring-white/50"
            aria-label="Close"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Content slot -->
          <slot />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, nextTick, onBeforeUnmount } from 'vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'close']);

const modalRef = ref(null);
const entering = ref(true);
let previouslyFocused = null;

watch(
  () => props.modelValue,
  (val) => {
    if (val) {
      previouslyFocused = document.activeElement;
      entering.value = true;
      document.body.style.overflow = 'hidden';
      nextTick(() => {
        requestAnimationFrame(() => {
          entering.value = false;
          modalRef.value?.focus();
        });
      });
    } else {
      document.body.style.overflow = '';
      if (previouslyFocused) previouslyFocused.focus();
    }
  },
  { immediate: true }
);

function close() {
  emit('update:modelValue', false);
  emit('close');
}

function handleBackdropClick() {
  close();
}

function trapFocus(event) {
  const modal = modalRef.value;
  if (!modal) return;
  const focusable = modal.querySelectorAll(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
  );
  if (focusable.length === 0) return;
  const first = focusable[0];
  const last = focusable[focusable.length - 1];
  if (event.target === first && event.shiftKey) {
    last.focus();
    event.preventDefault();
  } else if (event.target === last && !event.shiftKey) {
    first.focus();
    event.preventDefault();
  }
}

onBeforeUnmount(() => {
  document.body.style.overflow = '';
});
</script>

<style scoped>
/* ================================================================
   GLASS MODAL – ULTRA‑HERO DEPTH WITH THEME‑AWARE PHYSICS
   ================================================================ */

/* Modal panel */
.glass-modal-panel {
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(40px);
  -webkit-backdrop-filter: blur(40px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow:
    0 24px 80px rgba(0, 0, 0, 0.25),
    inset 0 1px 0 rgba(255, 255, 255, 0.8);
  color: #111827;
}
.dark .glass-modal-panel {
  background: rgba(0, 0, 0, 0.75);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow:
    0 24px 80px rgba(0, 0, 0, 0.6),
    inset 0 1px 0 rgba(255, 255, 255, 0.08);
  color: #f3f4f6;
}

/* Close button */
.glass-modal-close {
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.1);
}
.dark .glass-modal-close {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.1);
}

/* Entrance animation states */
.modal-entering {
  transform: translateY(20px) scale(0.95);
  opacity: 0;
}
.modal-entered {
  transform: translateY(0) scale(1);
  opacity: 1;
}

/* Transition for backdrop and panel */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.modal-fade-enter-to,
.modal-fade-leave-from {
  opacity: 1;
}

/* Mobile: full width */
@media (max-width: 640px) {
  .glass-modal-panel {
    margin: 1rem;
    width: calc(100% - 2rem);
    max-height: 90vh;
    overflow-y: auto;
  }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .modal-entering,
  .modal-entered {
    transition: none !important;
    transform: none !important;
  }
  .modal-fade-enter-active,
  .modal-fade-leave-active {
    transition: none !important;
  }
}
</style>