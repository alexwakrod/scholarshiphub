<template>
  <div class="space-y-1.5 min-w-0">
    <div class="relative group min-w-0">
      <!-- Glass shell -->
      <div
        class="glass-textarea-wrapper rounded-2xl transition-all duration-300 min-w-0"
        :class="[
          error
            ? 'border-red-500/50 shadow-[0_0_30px_rgba(239,68,68,0.08)] dark:shadow-[0_0_30px_rgba(239,68,68,0.15)]'
            : 'border-gray-300 dark:border-white/10',
          focused && !error
            ? 'border-blue-500/60 dark:border-blue-400/60 !border-2'
            : 'border',
          disabled ? 'opacity-40 cursor-not-allowed' : ''
        ]"
      >
        <!-- Custom contenteditable textarea -->
        <div
          ref="editableRef"
          :contenteditable="!disabled"
          :aria-placeholder="placeholder"
          class="glass-textarea-field w-full bg-transparent text-gray-900 dark:text-white transition-colors duration-300 outline-none px-4 py-3.5 text-sm whitespace-pre-wrap break-words"
          :data-placeholder="!modelValue && placeholder ? placeholder : ''"
          :style="{ maxHeight: maxHeight + 'px', overflowY: 'auto', maxWidth: '100%' }"
          @input="handleInput"
          @focus="onFocus"
          @blur="onBlur"
        ></div>
      </div>

      <!-- Error feedback -->
      <div v-if="error" class="mt-1.5 text-xs text-red-400 flex items-center gap-1.5 animate-shake">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ error }}
      </div>

      <!-- Character count (optional) -->
      <div v-if="showCharCount" class="flex justify-end mt-0.5">
        <span class="text-xs" :class="charCountColor">
          {{ modelValue?.length || 0 }}{{ maxlength ? ' / ' + maxlength : '' }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted, computed } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  error: { type: String, default: '' },
  maxlength: { type: [Number, String], default: undefined },
  showCharCount: { type: Boolean, default: true },
  maxHeight: { type: Number, default: 200 },
});

const emit = defineEmits(['update:modelValue', 'focus', 'blur']);

const focused = ref(false);
const editableRef = ref(null);
const maxTextareaHeight = props.maxHeight || 200;

const charCountColor = computed(() => {
  const len = String(props.modelValue || '').length;
  const max = Number(props.maxlength || Infinity);
  if (len >= max) return 'text-red-400';
  if (len >= max * 0.9) return 'text-amber-400';
  return 'text-gray-400 dark:text-white/30';
});

// ---- Write text to div (fixes initial data and validation resets) ----
function writeTextToDiv() {
  const el = editableRef.value;
  if (!el) return;
  const expected = String(props.modelValue ?? '');
  if (el.textContent !== expected) {
    el.textContent = expected;
  }
}

onMounted(() => {
  writeTextToDiv();
  nextTick(autoResize);
});

watch(() => props.modelValue, () => {
  writeTextToDiv();
  nextTick(autoResize);
});

// ---- Auto‑resize ----
function autoResize() {
  const el = editableRef.value;
  if (!el) return;
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, maxTextareaHeight) + 'px';
  el.style.overflowY = el.scrollHeight > maxTextareaHeight ? 'auto' : 'hidden';
}

// ---- Input handling ----
function handleInput(event) {
  const el = event.target;
  let text = el.textContent || '';

  // Enforce maxlength
  if (props.maxlength && text.length > Number(props.maxlength)) {
    text = text.slice(0, Number(props.maxlength));
    el.textContent = text;
    // Place cursor at end
    const range = document.createRange();
    range.selectNodeContents(el);
    range.collapse(false);
    const sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);
    return;
  }

  emit('update:modelValue', text);
}

function onFocus() {
  focused.value = true;
  emit('focus');
}

function onBlur() {
  focused.value = false;
  emit('blur');
}
</script>

<style scoped>
/* ============================================
   PREMIUM GLASS TEXTAREA – THEME‑AWARE
   ============================================ */

.glass-textarea-wrapper {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  transition: border-color 0.3s ease, background 0.3s ease, box-shadow 0.3s ease;
}
.dark .glass-textarea-wrapper {
  background: rgba(255, 255, 255, 0.05);
}

.glass-textarea-wrapper:hover {
  background: rgba(255, 255, 255, 0.35);
}
.dark .glass-textarea-wrapper:hover {
  background: rgba(255, 255, 255, 0.08);
}

.glass-textarea-wrapper:focus-within {
  background: rgba(255, 255, 255, 0.4);
}
.dark .glass-textarea-wrapper:focus-within {
  background: rgba(255, 255, 255, 0.1);
}

.glass-textarea-field[data-placeholder]:empty::before {
  content: attr(data-placeholder);
  color: rgba(0, 0, 0, 0.4);
  pointer-events: none;
}
.dark .glass-textarea-field[data-placeholder]:empty::before {
  color: rgba(255, 255, 255, 0.4);
}

/* Error shake */
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-2px); }
  75% { transform: translateX(2px); }
}
.animate-shake {
  animation: shake 0.3s ease-out;
}

/* Mobile and reduced motion */
@media (max-width: 768px) {
  .glass-textarea-wrapper:focus-within {
    border-width: 2px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .glass-textarea-wrapper,
  .glass-textarea-wrapper:focus-within {
    transition: none !important;
  }
}
</style>