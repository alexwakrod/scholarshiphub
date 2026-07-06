<template>
  <div ref="selectRef" class="relative w-full">
    <!-- Trigger Button -->
    <button
      type="button"
      @click="toggleOpen"
      :disabled="disabled"
      :title="(multiple && Array.isArray(modelValue)) ? selectedLabelsFull : displayLabel"
      class="group relative w-full flex items-start justify-between px-4 py-2.5 rounded-2xl transition-all duration-300 text-left glass-select-trigger"
      :class="[
        open
          ? 'border-blue-500/50 shadow-[0_0_30px_rgba(59,130,246,0.1)] dark:shadow-[0_0_30px_rgba(59,130,246,0.2)] ring-1 ring-blue-500/20'
          : 'hover:border-gray-400 dark:hover:border-white/20',
        disabled ? 'opacity-40 cursor-not-allowed' : 'hover:scale-[1.01] hover:shadow-lg',
        'border min-h-[44px]',
      ]"
    >
      <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-blue-400/5 to-indigo-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

      <div class="relative z-10 flex-1 flex items-start min-w-0">
        <template v-if="!multiple">
          <span class="text-sm text-gray-900 dark:text-white font-medium truncate block w-full">
            {{ displayLabel || placeholder }}
          </span>
        </template>
        <template v-else-if="Array.isArray(modelValue)">
          <div class="flex flex-wrap items-center gap-1 w-full">
            <span
              v-for="val in modelValue"
              :key="val"
              class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[11px] font-medium leading-none glass-select-tag"
            >
              <span>{{ getLabelByValue(val) }}</span>
              <button
                @click.stop="removeTag(val)"
                class="ml-0.5 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors flex-shrink-0 tag-dismiss"
              >
                <XMarkIcon class="w-3 h-3" />
              </button>
            </span>
            <span v-if="!modelValue || modelValue.length === 0" class="text-sm text-gray-400 dark:text-white/30 truncate w-full">
              {{ placeholder }}
            </span>
          </div>
        </template>
        <span v-else class="text-sm text-gray-400 dark:text-white/30 truncate w-full">
          {{ placeholder }}
        </span>
      </div>

      <svg
        class="relative z-10 w-4 h-4 text-gray-400 dark:text-white/30 transition-transform duration-300 flex-shrink-0 ml-2"
        :class="[multiple ? 'mt-1' : '', open ? 'rotate-180' : '']"
        fill="none" stroke="currentColor" viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Dropdown -->
    <Teleport to="body">
      <Transition name="dropdown-slide">
        <div
          v-if="open"
          ref="dropdownRef"
          class="fixed z-[9999] glass-select-dropdown rounded-2xl border shadow-2xl overflow-y-auto custom-scrollbar"
          :style="dropdownStyle"
          @click.stop
        >
          <div
            v-if="searchable"
            class="sticky top-0 z-10 p-2.5 border-b border-gray-200/50 dark:border-white/5 glass-search-header"
          >
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 dark:text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                ref="searchInput"
                v-model="searchText"
                @input="onSearchInput"
                placeholder="Search..."
                class="w-full pl-8 pr-3 py-1.5 rounded-xl glass-select-search text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/30 outline-none focus:ring-1 focus:ring-blue-500/50 transition-all"
              />
            </div>
          </div>

          <div v-if="filteredOptions.length === 0" class="p-4 text-sm text-gray-500 dark:text-gray-400 text-center glass-empty-state">
            <svg class="w-8 h-8 mx-auto mb-2 text-gray-300 dark:text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            No options found
          </div>

          <div v-else>
            <button
              v-for="(option, index) in filteredOptions"
              :key="option.value"
              @click="selectOption(option)"
              class="group w-full flex items-center gap-3 text-left px-4 py-2.5 text-sm transition-all duration-200 glass-select-option stagger-item"
              :style="{ '--i': index }"
              :class="[
                isSelected(option)
                  ? 'bg-blue-500/10 dark:bg-blue-400/15 border-l-2 border-blue-500'
                  : 'border-l-2 border-transparent',
              ]"
            >
              <span class="w-4 h-4 flex items-center justify-center flex-shrink-0">
                <svg
                  v-if="!isSelected(option)"
                  class="w-4 h-4 text-gray-400 dark:text-gray-500 transition-colors group-hover:text-gray-600 dark:group-hover:text-gray-300"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <circle cx="12" cy="12" r="10" stroke-width="1.5" />
                </svg>
                <svg
                  v-else
                  class="w-4 h-4 text-blue-500 dark:text-blue-400 group-hover:scale-110 transition-transform"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <circle cx="12" cy="12" r="10" fill="currentColor" opacity="0.15" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
              </span>
              <span
                v-if="searchable && searchText"
                class="text-gray-900 dark:text-white group-hover:text-gray-700 dark:group-hover:text-white/90 transition-colors"
                v-html="highlightMatch(option.label, searchText)"
              ></span>
              <span v-else class="text-gray-900 dark:text-white group-hover:text-gray-700 dark:group-hover:text-white/90 transition-colors">
                {{ option.label }}
              </span>
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
  modelValue: { type: [String, Number, Array], default: '' },
  options: { type: Array, required: true },
  placeholder: { type: String, default: 'Select...' },
  searchable: { type: Boolean, default: false },
  multiple: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const searchText = ref('');
const selectRef = ref(null);
const dropdownRef = ref(null);
const searchInput = ref(null);

const dropdownStyle = ref({ top: '0px', left: '0px', width: '200px', maxHeight: '320px' });

const safeOptions = computed(() => (Array.isArray(props.options) ? props.options : []));

// ---------- POSITIONING (dynamic height) ----------
function placeDropdown() {
  if (!selectRef.value || !open.value) return;
  const trigger = selectRef.value.getBoundingClientRect();
  const vh = window.innerHeight;
  const vw = window.innerWidth;
  const gap = 4;

  // Measure actual dropdown height after it's rendered (fallback 320)
  let actualHeight = 320;
  if (dropdownRef.value) {
    actualHeight = dropdownRef.value.scrollHeight;
    // Clamp to a max reasonable height
    actualHeight = Math.min(actualHeight, vh - 16);
  }

  // Try placing below
  let top = trigger.bottom + gap;
  let left = trigger.left;
  let width = trigger.width;

  // If not enough space below, flip above
  if (top + actualHeight > vh - 8) {
    top = trigger.top - actualHeight - gap;
    // If still not enough, clamp to viewport top with scroll
    if (top < 8) top = 8;
  }

  // Prevent horizontal overflow
  if (left + width > vw - 8) {
    left = vw - width - 8;
  }
  if (left < 8) left = 8;

  dropdownStyle.value = {
    top: `${top}px`,
    left: `${left}px`,
    width: `${width}px`,
    maxHeight: `${Math.min(actualHeight, vh - top - 16)}px`,
  };
}

function onScrollOrResize() {
  if (open.value) placeDropdown();
}

let resizeObserver = null;
watch(open, (val) => {
  if (val) {
    nextTick(() => {
      // Wait for the dropdown to render so we can measure its height
      nextTick(() => {
        placeDropdown();
        searchInput.value?.focus();
      });
    });
    window.addEventListener('scroll', onScrollOrResize, true);
    window.addEventListener('resize', onScrollOrResize);
    if (selectRef.value) {
      resizeObserver = new ResizeObserver(() => placeDropdown());
      resizeObserver.observe(selectRef.value);
    }
  } else {
    window.removeEventListener('scroll', onScrollOrResize, true);
    window.removeEventListener('resize', onScrollOrResize);
    if (resizeObserver) {
      resizeObserver.disconnect();
      resizeObserver = null;
    }
  }
});

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
  window.removeEventListener('scroll', onScrollOrResize, true);
  window.removeEventListener('resize', onScrollOrResize);
  if (resizeObserver) resizeObserver.disconnect();
});

// ---------- INTERACTION ----------
function toggleOpen() {
  if (props.disabled) return;
  open.value = !open.value;
  if (open.value) searchText.value = '';
}

function selectOption(option) {
  if (props.multiple) {
    const current = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
    const idx = current.indexOf(option.value);
    if (idx >= 0) current.splice(idx, 1);
    else current.push(option.value);
    emit('update:modelValue', current);
  } else {
    emit('update:modelValue', option.value);
    open.value = false;
  }
}

function removeTag(value) {
  if (!props.multiple) return;
  const current = Array.isArray(props.modelValue) ? props.modelValue.filter(v => v !== value) : [];
  emit('update:modelValue', current);
}

function isSelected(option) {
  if (props.multiple) return Array.isArray(props.modelValue) && props.modelValue.includes(option.value);
  return props.modelValue === option.value;
}

function getLabelByValue(value) {
  const found = safeOptions.value.find(o => o.value === value);
  return found ? found.label : value;
}

function highlightMatch(label, query) {
  const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const regex = new RegExp(`(${escaped})`, 'gi');
  return label.replace(regex, '<mark class="bg-yellow-200/80 dark:bg-yellow-500/40 text-yellow-900 dark:text-yellow-200 rounded px-0.5">$1</mark>');
}

function onSearchInput() {}

function handleClickOutside(e) {
  if (open.value && selectRef.value && !selectRef.value.contains(e.target)) {
    if (dropdownRef.value && dropdownRef.value.contains(e.target)) return;
    open.value = false;
  }
}

const filteredOptions = computed(() => {
  if (!searchText.value) return safeOptions.value;
  const q = searchText.value.toLowerCase();
  return safeOptions.value.filter(o => o.label.toLowerCase().includes(q));
});

const displayLabel = computed(() => {
  if (props.multiple) return '';
  const found = safeOptions.value.find(o => o.value === props.modelValue);
  return found ? found.label : '';
});

const selectedLabelsFull = computed(() => {
  if (!props.multiple) return '';
  const arr = Array.isArray(props.modelValue) ? props.modelValue : [];
  if (arr.length === 0) return '';
  return arr.map(v => getLabelByValue(v)).join(', ');
});
</script>

<style scoped>
/* ============================================
   GLASS SELECT – PRECISE POSITION
   ============================================ */
.glass-select-trigger {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-color: rgba(0, 0, 0, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.6);
  transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease, background 0.3s ease;
}
.dark .glass-select-trigger {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.06);
}
.glass-select-trigger:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.45);
  box-shadow: 0 12px 32px rgba(59, 130, 246, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.7);
}
.dark .glass-select-trigger:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.08);
  box-shadow: 0 12px 32px rgba(59, 130, 246, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.1);
}
.glass-select-trigger:active:not(:disabled) {
  transform: scale(0.97) translateY(1px);
  transition-duration: 0.1s;
}

.glass-select-dropdown {
  background: rgba(255, 255, 255, 0.65);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border-color: rgba(0, 0, 0, 0.12);
  box-shadow: 0 24px 80px rgba(0, 0, 0, 0.25);
  isolation: isolate;
}
.dark .glass-select-dropdown {
  background: rgba(0, 0, 0, 0.75);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 24px 80px rgba(0, 0, 0, 0.6);
}

.glass-select-search {
  background: rgba(0, 0, 0, 0.04);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.dark .glass-select-search {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.1);
}

.glass-select-option {
  background: transparent;
  transition: all 0.2s ease;
  opacity: 0;
  animation: stagger-fade-in 0.3s ease forwards;
  animation-delay: calc(var(--i, 0) * 30ms);
}
.glass-select-option:hover {
  background: rgba(0, 0, 0, 0.04);
  transform: translateY(-1px) scale(1.01);
}
.dark .glass-select-option:hover {
  background: rgba(255, 255, 255, 0.08);
}

@keyframes stagger-fade-in {
  0% { opacity: 0; transform: translateY(8px); }
  100% { opacity: 1; transform: translateY(0); }
}

.dropdown-slide-enter-active {
  transition: opacity 0.2s ease, transform 0.25s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.dropdown-slide-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.dropdown-slide-enter-from {
  opacity: 0;
  transform: translateY(-8px) scale(0.98);
}
.dropdown-slide-leave-to {
  opacity: 0;
  transform: translateY(-4px) scale(0.98);
}

.glass-select-tag {
  background: rgba(59, 130, 246, 0.15);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(59, 130, 246, 0.25);
  color: #1e40af;
  white-space: nowrap;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
}
.dark .glass-select-tag {
  background: rgba(59, 130, 246, 0.2);
  border-color: rgba(59, 130, 246, 0.4);
  color: #93c5fd;
}

.tag-dismiss::after {
  content: '';
  position: absolute;
  inset: -6px;
}

.glass-empty-state {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(0, 0, 0, 0.06);
  border-radius: 0.75rem;
}
.dark .glass-empty-state {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.06);
}

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 999px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); }

*:focus-visible {
  outline: none;
  box-shadow: 0 0 0 2px #3b82f6;
  border-radius: 0.75rem;
}

input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
input:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0 30px transparent inset !important;
  box-shadow: 0 0 0 30px transparent inset !important;
  -webkit-text-fill-color: inherit !important;
  transition: background-color 5000s ease-in-out 0s;
}

@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .glass-select-trigger,
  .glass-select-trigger:hover,
  .glass-select-trigger:active,
  .glass-select-option,
  .glass-select-option:hover {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>