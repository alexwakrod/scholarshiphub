<template>
  <div class="space-y-1.5 min-w-0">
    <div class="relative group min-w-0">
      <!-- Glass shell -->
      <div
        class="glass-input-wrapper rounded-2xl transition-all duration-300 min-w-0"
        :class="[
          error
            ? 'border-red-500/50 shadow-[0_0_30px_rgba(239,68,68,0.08)] dark:shadow-[0_0_30px_rgba(239,68,68,0.15)]'
            : 'border-gray-300 dark:border-white/10',
          focused && !error
            ? 'border-blue-500/60 dark:border-blue-400/60 !border-2'
            : 'border',
          disabled ? 'opacity-40 cursor-not-allowed' : '',
          isFilter ? 'filter-glass' : ''
        ]"
      >
        <div class="relative flex items-start">
          <!-- Icon (left) -->
          <div
            v-if="icon"
            class="absolute left-4 top-3.5 text-gray-400 dark:text-white/30 group-focus-within:text-blue-400 dark:group-focus-within:text-blue-300 transition-colors duration-300 shrink-0"
          >
            <svg v-if="icon === 'user'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <svg v-else-if="icon === 'email'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <svg v-else-if="icon === 'lock'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>

          <!-- Custom contenteditable field (single‑line or multi‑line) -->
          <div
            ref="editableRef"
            :contenteditable="!disabled"
            :aria-placeholder="placeholder"
            class="glass-input-field bg-transparent text-gray-900 dark:text-white transition-colors duration-300 outline-none"
            :class="[
              icon ? 'pl-11 pr-4' : 'px-4',
              type === 'textarea'
                ? 'py-3.5 whitespace-pre-wrap break-words'
                : 'py-3.5 whitespace-nowrap overflow-hidden text-ellipsis',
              type === 'password' ? 'password-mask' : ''
            ]"
            :data-placeholder="!modelValue && placeholder ? placeholder : ''"
            :style="type === 'textarea' ? { maxHeight: maxTextareaHeight + 'px', overflowY: 'auto', maxWidth: '100%' } : { maxWidth: '100%' }"
            @input="handleInput"
            @focus="onFocus"
            @blur="onBlur"
            @keydown.enter.exact="handleEnter"
          ></div>
        </div>
      </div>

      <!-- Success feedback -->
      <div v-if="!error && modelValue && showSuccess" class="mt-1.5 text-xs text-green-400 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        Looks good!
      </div>

      <!-- Error feedback -->
      <div v-if="error" class="mt-1.5 text-xs text-red-400 flex items-center gap-1.5 animate-shake">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted } from 'vue';

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  type: { type: String, default: 'text' },   // 'text', 'email', 'password', 'textarea'
  placeholder: { type: String, default: '' },
  error: { type: String, default: '' },
  icon: { type: String, default: null },
  disabled: { type: Boolean, default: false },
  showSuccess: { type: Boolean, default: true },
  isFilter: { type: Boolean, default: false },
  maxlength: { type: [Number, String], default: undefined },
});

const emit = defineEmits(['update:modelValue', 'focus', 'blur']);

const focused = ref(false);
const editableRef = ref(null);
const maxTextareaHeight = 200;

// ---- Write text to div ----
function writeTextToDiv() {
  const el = editableRef.value;
  if (!el) return;
  const expected = props.type === 'password'
    ? '•'.repeat(String(props.modelValue || '').length)
    : String(props.modelValue ?? '');
  if (el.textContent !== expected) {
    el.textContent = expected;
  }
}

onMounted(() => {
  writeTextToDiv();
  if (props.type === 'textarea') {
    nextTick(autoResize);
  }
});

watch(() => props.modelValue, () => {
  writeTextToDiv();
  if (props.type === 'textarea') {
    nextTick(autoResize);
  }
});

// ---- Auto‑resize for textarea ----
function autoResize() {
  const el = editableRef.value;
  if (!el || props.type !== 'textarea') return;
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, maxTextareaHeight) + 'px';
  el.style.overflowY = el.scrollHeight > maxTextareaHeight ? 'auto' : 'hidden';
}

// ---- Input handling ----
function handleInput(event) {
  const el = event.target;
  let text = el.textContent || '';

  if (props.maxlength && text.length > Number(props.maxlength)) {
    text = text.slice(0, Number(props.maxlength));
    el.textContent = text;
    // Move cursor to end
    const range = document.createRange();
    range.selectNodeContents(el);
    range.collapse(false);
    const sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);
    return;
  }

  if (props.type === 'password') {
    handlePasswordInput(event);
    return;
  }

  emit('update:modelValue', text);
}

// ---- Password masking ----
const realPassword = ref('');

function handlePasswordInput(event) {
  event.preventDefault();
  const el = editableRef.value;
  const inputType = event.inputType;

  if (inputType === 'insertText' || inputType === 'insertFromComposition') {
    realPassword.value += event.data || '';
  } else if (inputType === 'deleteContentBackward') {
    realPassword.value = realPassword.value.slice(0, -1);
  } else if (inputType === 'deleteContentForward') {
    realPassword.value = realPassword.value.slice(1);
  } else if (inputType === 'insertFromPaste') {
    const pasted = (event.clipboardData || window.clipboardData)?.getData('text/plain') || '';
    realPassword.value += pasted;
  } else {
    realPassword.value += event.data || '';
  }

  if (props.maxlength && realPassword.value.length > Number(props.maxlength)) {
    realPassword.value = realPassword.value.slice(0, Number(props.maxlength));
  }

  el.textContent = '•'.repeat(realPassword.value.length);
  const range = document.createRange();
  range.selectNodeContents(el);
  range.collapse(false);
  const sel = window.getSelection();
  sel.removeAllRanges();
  sel.addRange(range);

  emit('update:modelValue', realPassword.value);
}

// ---- Focus / Blur ----
function onFocus() {
  focused.value = true;
  emit('focus');
}

function onBlur() {
  focused.value = false;
  emit('blur');
}

function handleEnter(event) {
  if (props.type !== 'textarea') {
    event.preventDefault();
    editableRef.value?.blur();
  }
}
</script>

<style scoped>
/* ============================================
   PREMIUM GLASS INPUT – THEME‑AWARE (NO HORIZONTAL GROW)
   ============================================ */

.glass-input-wrapper {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  transition: border-color 0.3s ease, background 0.3s ease, box-shadow 0.3s ease;
}
.dark .glass-input-wrapper {
  background: rgba(255, 255, 255, 0.05);
}

.glass-input-wrapper:hover {
  background: rgba(255, 255, 255, 0.35);
}
.dark .glass-input-wrapper:hover {
  background: rgba(255, 255, 255, 0.08);
}

.glass-input-wrapper:focus-within {
  background: rgba(255, 255, 255, 0.4);
}
.dark .glass-input-wrapper:focus-within {
  background: rgba(255, 255, 255, 0.1);
}

.filter-glass {
  background: rgba(255, 255, 255, 0.15) !important;
  backdrop-filter: blur(24px);
  border-color: rgba(0, 0, 0, 0.06);
}
.dark .filter-glass {
  background: rgba(255, 255, 255, 0.04) !important;
  border-color: rgba(255, 255, 255, 0.06);
}
.filter-glass:focus-within {
  background: rgba(255, 255, 255, 0.25) !important;
  border-color: rgba(59, 130, 246, 0.5);
}
.dark .filter-glass:focus-within {
  background: rgba(255, 255, 255, 0.08) !important;
  border-color: rgba(59, 130, 246, 0.6);
}

.glass-input-field[data-placeholder]:empty::before {
  content: attr(data-placeholder);
  color: rgba(0, 0, 0, 0.4);
  pointer-events: none;
}
.dark .glass-input-field[data-placeholder]:empty::before {
  color: rgba(255, 255, 255, 0.4);
}

.password-mask {
  letter-spacing: 2px;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}
.animate-shake {
  animation: shake 0.3s ease-in-out;
}
</style>