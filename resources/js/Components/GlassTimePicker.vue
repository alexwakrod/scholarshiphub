<template>
  <div class="relative" ref="wrapperRef">
    <button
      type="button"
      @click="toggle"
      :disabled="disabled"
      class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl glass-input border transition-all duration-200 text-left text-sm"
      :class="open ? 'border-blue-400' : 'border-gray-300 dark:border-white/20'"
    >
      <span v-if="modelValue" class="text-gray-900 dark:text-white">{{ displayValue }}</span>
      <span v-else class="text-gray-400 dark:text-white/40">{{ placeholder }}</span>
      <svg class="w-4 h-4 text-gray-500 dark:text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </button>

    <div v-if="open" class="absolute z-50 mt-1 p-4 rounded-xl glass-overlay border border-gray-200 dark:border-white/10 shadow-xl w-[220px]">
      <div class="mb-3">
        <p class="text-xs text-gray-500 dark:text-white/50 mb-1">Hour</p>
        <div class="grid grid-cols-6 gap-1">
          <button
            v-for="h in hours"
            :key="h"
            @click="selected.hour = h"
            class="h-8 rounded text-sm flex items-center justify-center"
            :class="selected.hour === h ? 'bg-blue-600 text-white' : 'text-gray-900 dark:text-white/70 hover:bg-gray-100 dark:hover:bg-white/10'"
          >
            {{ String(h).padStart(2, '0') }}
          </button>
        </div>
      </div>
      <div class="mb-3">
        <p class="text-xs text-gray-500 dark:text-white/50 mb-1">Minute</p>
        <div class="grid grid-cols-6 gap-1">
          <button
            v-for="m in minutes"
            :key="m"
            @click="selected.minute = m"
            class="h-8 rounded text-sm flex items-center justify-center"
            :class="selected.minute === m ? 'bg-blue-600 text-white' : 'text-gray-900 dark:text-white/70 hover:bg-gray-100 dark:hover:bg-white/10'"
          >
            {{ String(m).padStart(2, '0') }}
          </button>
        </div>
      </div>
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs text-gray-500 dark:text-white/50">Period</span>
        <div class="flex rounded-lg overflow-hidden border border-gray-300 dark:border-white/20">
          <button
            @click="selected.period = 'AM'"
            class="px-3 py-1 text-xs transition-colors"
            :class="selected.period === 'AM' ? 'bg-blue-600 text-white' : 'text-gray-900 dark:text-white/70 hover:bg-gray-100 dark:hover:bg-white/10'"
          >AM</button>
          <button
            @click="selected.period = 'PM'"
            class="px-3 py-1 text-xs transition-colors"
            :class="selected.period === 'PM' ? 'bg-blue-600 text-white' : 'text-gray-900 dark:text-white/70 hover:bg-gray-100 dark:hover:bg-white/10'"
          >PM</button>
        </div>
      </div>
      <div class="flex justify-end gap-2">
        <button @click="clear" class="text-xs text-gray-500 dark:text-white/40 hover:underline">Clear</button>
        <button @click="apply" class="px-3 py-1 rounded bg-blue-600 text-white text-xs">Apply</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Select time...' },
  disabled: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const wrapperRef = ref(null);
const hours = Array.from({ length: 12 }, (_, i) => i + 1);
const minutes = [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55];

const selected = reactive({ hour: 12, minute: 0, period: 'AM' });

const displayValue = computed(() => {
  if (!props.modelValue) return '';
  const [h, m] = props.modelValue.split(':');
  const hour = parseInt(h, 10);
  const minute = parseInt(m, 10);
  const period = hour >= 12 ? 'PM' : 'AM';
  const displayHour = hour % 12 || 12;
  return `${displayHour}:${String(minute).padStart(2, '0')} ${period}`;
});

function parseInitial() {
  if (props.modelValue) {
    const [h, m] = props.modelValue.split(':');
    const hour24 = parseInt(h, 10);
    const minute = parseInt(m, 10) || 0;
    selected.hour = hour24 % 12 || 12;
    selected.minute = minute;
    selected.period = hour24 >= 12 ? 'PM' : 'AM';
  }
}

function toggle() {
  if (props.disabled) return;
  open.value = !open.value;
  if (open.value) parseInitial();
}

function apply() {
  let hour24 = selected.hour;
  if (selected.period === 'PM' && selected.hour !== 12) hour24 += 12;
  if (selected.period === 'AM' && selected.hour === 12) hour24 = 0;
  const formatted = `${String(hour24).padStart(2, '0')}:${String(selected.minute).padStart(2, '0')}`;
  emit('update:modelValue', formatted);
  open.value = false;
}

function clear() {
  emit('update:modelValue', '');
  open.value = false;
}

function handleClickOutside(e) {
  if (wrapperRef.value && !wrapperRef.value.contains(e.target)) open.value = false;
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));
</script>

<style scoped>
.glass-input { background: rgba(0, 0, 0, 0.05); backdrop-filter: blur(8px); color: #111827; }
.dark .glass-input { background: rgba(255,255,255,0.08); color: #fff; }
.glass-overlay { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); color: #111827; }
.dark .glass-overlay { background: rgba(0,0,0,0.9); color: #f3f4f6; }
</style>