<template>
  <div class="relative" ref="wrapperRef">
    <!-- Trigger Button with 3D hover -->
    <button
      type="button"
      @click="toggle"
      :disabled="disabled"
      class="group relative w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 text-left text-sm"
      :class="[
        open
          ? 'border-blue-400/50 shadow-[0_0_30px_rgba(59,130,246,0.08)]'
          : 'border-gray-300 dark:border-white/10 hover:border-gray-400 dark:hover:border-white/20',
        disabled
          ? 'opacity-50 cursor-not-allowed'
          : 'hover:scale-[1.02] hover:shadow-lg',
        'glass-surface border',
      ]"
    >
      <!-- Glow on hover -->
      <div
        class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-400/5 to-indigo-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
      ></div>

      <span class="relative z-10 flex items-center gap-2.5">
        <!-- Calendar icon -->
        <svg
          class="w-4 h-4 text-gray-400 dark:text-white/30 group-hover:text-gray-600 dark:group-hover:text-white/50 transition-colors"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
          />
        </svg>
        <span
          v-if="displayValue"
          class="text-gray-900 dark:text-white font-medium"
        >
          {{ displayValue }}
        </span>
        <span v-else class="text-gray-400 dark:text-white/30">
          {{ placeholder }}
        </span>
      </span>

      <!-- Chevron with rotation -->
      <svg
        class="relative z-10 w-4 h-4 text-gray-400 dark:text-white/30 transition-transform duration-300"
        :class="{ 'rotate-180': open }"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M19 9l-7 7-7-7"
        />
      </svg>

      <!-- Tooltip trigger -->
      <div
        class="absolute -top-2 -right-2 z-20 w-5 h-5 rounded-full glass-surface flex items-center justify-center text-gray-400 dark:text-white/20 hover:text-gray-600 dark:hover:text-white/50 transition-colors cursor-help"
        @mouseenter="showTooltip = true"
        @mouseleave="showTooltip = false"
        @touchstart="showTooltip = !showTooltip"
      >
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>

        <!-- Tooltip -->
        <div
          v-if="showTooltip"
          class="absolute z-50 bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 p-3 rounded-xl glass-tooltip pointer-events-none"
          style="animation: tooltipFade 0.25s ease-out forwards;"
        >
          <div class="relative" style="transform: rotateX(2deg) rotateY(-2deg);">
            <p class="text-xs text-white/80 leading-relaxed">
              Select your date of birth for eligibility verification.
            </p>
            <p class="text-[10px] text-white/30 mt-1.5 font-mono">
              Example: <span class="text-white/50">15 January 2000</span>
            </p>
            <div
              class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-3 h-3 rotate-45"
              style="background: rgba(0,0,0,0.85); backdrop-filter: blur(12px); border-left: 1px solid rgba(255,255,255,0.1); border-top: 1px solid rgba(255,255,255,0.1);"
            ></div>
          </div>
        </div>
      </div>
    </button>

    <!-- Teleported Dropdown (closes only on day select or outside click) -->
    <Teleport to="body">
      <Transition name="dropdown-fade">
        <div
          v-if="open"
          ref="dropdownRef"
          class="fixed z-[9999] mt-1.5 p-4 rounded-2xl glass-dropdown border border-gray-200 dark:border-white/10 shadow-2xl select-none"
          :style="dropdownStyle"
          @touchstart="onTouchStart"
          @touchmove="onTouchMove"
          @touchend="onTouchEnd"
        >
          <!-- Header -->
          <div class="flex items-center justify-between mb-3">
            <button
              @click="navPrev"
              class="group w-8 h-8 rounded-xl glass-surface hover:glass-elevated transition-all duration-300 hover:scale-105 flex items-center justify-center text-gray-500 dark:text-white/40 hover:text-gray-900 dark:hover:text-white"
            >
              <svg
                class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>

            <div class="flex items-center gap-1 text-sm">
              <button
                v-if="viewMode !== 'years'"
                @click="switchToMonths"
                class="font-medium text-gray-900 dark:text-white/80 hover:text-gray-700 dark:hover:text-white transition-colors px-2 py-1 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5"
              >
                {{ monthNames[selectedMonth] }}
              </button>
              <span v-if="viewMode === 'days'" class="text-gray-500 dark:text-white/20">,</span>
              <button
                @click="switchToYears"
                class="font-medium text-gray-600 dark:text-white/60 hover:text-gray-900 dark:hover:text-white transition-colors px-2 py-1 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5"
              >
                {{ displayYear }}
              </button>
            </div>

            <button
              @click="navNext"
              class="group w-8 h-8 rounded-xl glass-surface hover:glass-elevated transition-all duration-300 hover:scale-105 flex items-center justify-center text-gray-500 dark:text-white/40 hover:text-gray-900 dark:hover:text-white"
            >
              <svg
                class="w-4 h-4 group-hover:translate-x-0.5 transition-transform"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>

          <!-- View switcher -->
          <Transition name="slide-fade" mode="out-in">
            <!-- Years view -->
            <div
              v-if="viewMode === 'years'"
              key="years"
              class="grid grid-cols-3 sm:grid-cols-4 gap-1.5"
            >
              <button
                v-for="year in yearList"
                :key="year"
                @click="selectYear(year)"
                class="py-2.5 rounded-xl text-sm transition-all duration-300 hover:scale-105"
                :class="[
                  year === displayYear
                    ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[0_0_20px_rgba(59,130,246,0.2)]'
                    : 'text-gray-600 dark:text-white/60 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5',
                ]"
              >
                {{ year }}
              </button>
            </div>

            <!-- Months view -->
            <div
              v-else-if="viewMode === 'months'"
              key="months"
              class="grid grid-cols-3 gap-1.5"
            >
              <button
                v-for="(name, idx) in monthNames"
                :key="idx"
                @click="selectMonth(idx)"
                class="py-2.5 rounded-xl text-sm transition-all duration-300 hover:scale-105"
                :class="[
                  idx === selectedMonth
                    ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[0_0_20px_rgba(59,130,246,0.2)]'
                    : 'text-gray-600 dark:text-white/60 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5',
                ]"
              >
                {{ name.substring(0, 3) }}
              </button>
            </div>

            <!-- Days view -->
            <div v-else key="days">
              <!-- Day names -->
              <div class="grid grid-cols-7 gap-0.5 mb-1.5">
                <div
                  v-for="day in dayNames"
                  :key="day"
                  class="text-center text-[10px] text-gray-500 dark:text-white/20 font-medium py-1 tracking-wider uppercase"
                >
                  {{ day }}
                </div>
              </div>

              <!-- Calendar grid -->
              <div class="grid grid-cols-7 gap-0.5">
                <button
                  v-for="(cell, idx) in calendarCells"
                  :key="idx"
                  type="button"
                  :disabled="!cell.isCurrentMonth"
                  @click="selectDate(cell)"
                  class="relative h-9 rounded-xl text-sm flex items-center justify-center transition-all duration-300"
                  :class="cellClasses(cell)"
                >
                  <span class="relative z-10">{{ cell.day }}</span>

                  <!-- Selected glow -->
                  <div
                    v-if="isSelected(cell)"
                    class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 shadow-[0_0_20px_rgba(59,130,246,0.3)]"
                  ></div>

                  <!-- Today ring -->
                  <div
                    v-if="isToday(cell)"
                    class="absolute inset-0 rounded-xl ring-1 ring-blue-400/50"
                  ></div>
                </button>
              </div>

              <!-- Footer actions -->
              <div
                class="mt-3 pt-2 border-t border-gray-200 dark:border-white/5 flex items-center justify-between"
              >
                <button
                  @click="selectToday"
                  class="text-xs text-blue-500 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors px-3 py-1 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-500/10"
                >
                  Today
                </button>
                <button
                  @click="clearDate"
                  class="text-xs text-gray-400 dark:text-white/30 hover:text-gray-700 dark:hover:text-white/60 transition-colors px-3 py-1 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5"
                >
                  Clear
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Select date...' },
  min: { type: String, default: '' },
  max: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue']);

// State
const open = ref(false);
const wrapperRef = ref(null);
const dropdownRef = ref(null);
const viewMode = ref('days');
const selectedMonth = ref(new Date().getMonth());
const displayYear = ref(new Date().getFullYear());
const showTooltip = ref(false);

// Swipe state
const touchStartX = ref(0);
const touchStartY = ref(0);
const touchStartTime = ref(0);

// Constants
const monthNames = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
];
const dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

// Computed
const displayValue = computed(() => {
  if (!props.modelValue) return '';
  const d = new Date(props.modelValue);
  if (isNaN(d.getTime())) return '';
  return d.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
});

const yearList = computed(() => {
  const start = Math.floor(displayYear.value / 12) * 12;
  return Array.from({ length: 12 }, (_, i) => start + i);
});

const calendarCells = computed(() => {
  const year = displayYear.value;
  const month = selectedMonth.value;
  const firstDay = new Date(year, month, 1);
  const startDayOfWeek = firstDay.getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();

  const cells = [];
  const prevMonthDays = new Date(year, month, 0).getDate();

  // Previous month days
  for (let i = startDayOfWeek - 1; i >= 0; i--) {
    cells.push({
      day: prevMonthDays - i,
      isCurrentMonth: false,
      date: new Date(year, month - 1, prevMonthDays - i),
    });
  }

  // Current month days
  for (let d = 1; d <= daysInMonth; d++) {
    cells.push({ day: d, isCurrentMonth: true, date: new Date(year, month, d) });
  }

  // Next month days
  const total = cells.length;
  const remaining = 7 - (total % 7);
  if (remaining < 7) {
    for (let d = 1; d <= remaining; d++) {
      cells.push({
        day: d,
        isCurrentMonth: false,
        date: new Date(year, month + 1, d),
      });
    }
  }
  return cells;
});

// Dropdown positioning
const dropdownStyle = computed(() => {
  if (!wrapperRef.value || !open.value) return { top: '0px', left: '0px', minWidth: '300px', maxWidth: '380px' };
  const rect = wrapperRef.value.getBoundingClientRect();
  const vh = window.innerHeight;
  const vw = window.innerWidth;
  const dropdownHeight = 400;
  let top = rect.bottom + 6;
  if (top + dropdownHeight > vh - 8) top = rect.top - dropdownHeight - 6;
  const isSmall = vw < 640;
  return {
    top: Math.max(8, top) + 'px',
    left: isSmall ? '8px' : Math.max(8, rect.left) + 'px',
    width: isSmall ? 'calc(100vw - 16px)' : Math.min(rect.width, 380) + 'px',
  };
});

// Methods
function isToday(cell) {
  const today = new Date();
  return (
    cell.date.getFullYear() === today.getFullYear() &&
    cell.date.getMonth() === today.getMonth() &&
    cell.date.getDate() === today.getDate()
  );
}

function isSelected(cell) {
  if (!props.modelValue) return false;
  const sel = new Date(props.modelValue);
  if (isNaN(sel.getTime())) return false;
  return (
    cell.date.getFullYear() === sel.getFullYear() &&
    cell.date.getMonth() === sel.getMonth() &&
    cell.date.getDate() === sel.getDate()
  );
}

function cellClasses(cell) {
  let cls = 'transition-all duration-300 hover:scale-105';

  if (!cell.isCurrentMonth) {
    return cls + ' text-gray-300 dark:text-white/10 cursor-default';
  }

  cls += ' text-gray-700 dark:text-white/80 hover:text-gray-900 dark:hover:text-white cursor-pointer';

  if (isToday(cell)) {
    cls += ' ring-1 ring-blue-400/30';
  }

  if (isSelected(cell)) {
    cls += ' text-white font-medium';
  }

  return cls;
}

function navPrev() {
  if (viewMode.value === 'years') {
    displayYear.value -= 12;
  } else if (viewMode.value === 'months') {
    displayYear.value -= 1;
  } else {
    selectedMonth.value -= 1;
    if (selectedMonth.value < 0) {
      selectedMonth.value = 11;
      displayYear.value -= 1;
    }
  }
}

function navNext() {
  if (viewMode.value === 'years') {
    displayYear.value += 12;
  } else if (viewMode.value === 'months') {
    displayYear.value += 1;
  } else {
    selectedMonth.value += 1;
    if (selectedMonth.value > 11) {
      selectedMonth.value = 0;
      displayYear.value += 1;
    }
  }
}

function switchToMonths() {
  viewMode.value = 'months';
}
function switchToYears() {
  viewMode.value = 'years';
}
function selectYear(year) {
  displayYear.value = year;
  viewMode.value = 'months';
}
function selectMonth(idx) {
  selectedMonth.value = idx;
  viewMode.value = 'days';
}

function selectDate(cell) {
  if (!cell.isCurrentMonth) return;
  const y = cell.date.getFullYear();
  const m = String(cell.date.getMonth() + 1).padStart(2, '0');
  const d = String(cell.date.getDate()).padStart(2, '0');
  emit('update:modelValue', `${y}-${m}-${d}`);
  open.value = false;
}

function selectToday() {
  const today = new Date();
  selectedMonth.value = today.getMonth();
  displayYear.value = today.getFullYear();
  viewMode.value = 'days';
  const y = today.getFullYear();
  const m = String(today.getMonth() + 1).padStart(2, '0');
  const d = String(today.getDate()).padStart(2, '0');
  emit('update:modelValue', `${y}-${m}-${d}`);
  open.value = false;
}

function clearDate() {
  emit('update:modelValue', '');
  open.value = false;
}

function toggle() {
  if (props.disabled) return;
  open.value = !open.value;
  if (open.value) {
    viewMode.value = 'days';
    if (props.modelValue) {
      const d = new Date(props.modelValue);
      if (!isNaN(d.getTime())) {
        selectedMonth.value = d.getMonth();
        displayYear.value = d.getFullYear();
      }
    }
  }
}

// ─── Swipe Gesture ────────────────────────────────
const SWIPE_THRESHOLD = 50;

function onTouchStart(e) {
  if (!e.touches || e.touches.length === 0) return;
  touchStartX.value = e.touches[0].clientX;
  touchStartY.value = e.touches[0].clientY;
  touchStartTime.value = Date.now();
}

function onTouchMove(e) {
  // Prevent default to stop page scrolling while swiping on the calendar
  if (e.touches && e.touches.length > 0) {
    const dx = e.touches[0].clientX - touchStartX.value;
    const dy = e.touches[0].clientY - touchStartY.value;
    // Only prevent default if horizontal movement is greater than vertical
    if (Math.abs(dx) > Math.abs(dy)) {
      e.preventDefault();
    }
  }
}

function onTouchEnd(e) {
  if (!e.changedTouches || e.changedTouches.length === 0) return;
  const endX = e.changedTouches[0].clientX;
  const endY = e.changedTouches[0].clientY;
  const dx = endX - touchStartX.value;
  const dy = endY - touchStartY.value;
  const elapsed = Date.now() - touchStartTime.value;

  // Only consider as swipe if movement is fast enough or distance is large enough
  // and horizontal movement dominates vertical
  if (Math.abs(dx) > SWIPE_THRESHOLD && Math.abs(dx) > Math.abs(dy) * 1.5 && elapsed < 300) {
    if (dx > 0) {
      navPrev(); // swipe right → previous
    } else {
      navNext(); // swipe left → next
    }
  }
}
// ─── End Swipe Gesture ────────────────────────────

function handleClickOutside(e) {
  if (!open.value) return;
  // Ignore clicks inside the trigger or the dropdown
  if (wrapperRef.value && wrapperRef.value.contains(e.target)) return;
  if (dropdownRef.value && dropdownRef.value.contains(e.target)) return;
  open.value = false;
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));
</script>

<style scoped>
/* Theme‑aware glass utilities */
.glass-surface {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.dark .glass-surface {
  background: rgba(255, 255, 255, 0.03);
  border-color: rgba(255, 255, 255, 0.06);
}

.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.07);
  border-color: rgba(255, 255, 255, 0.12);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.glass-dropdown {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(0, 0, 0, 0.12);
  box-shadow: 0 24px 80px rgba(0, 0, 0, 0.25);
  touch-action: pan-y; /* allow vertical scroll, we handle horizontal */
}
.dark .glass-dropdown {
  background: rgba(0, 0, 0, 0.85);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 24px 80px rgba(0, 0, 0, 0.5);
}

.glass-tooltip {
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

/* Dropdown transitions */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.dropdown-fade-enter-from {
  opacity: 0;
  transform: translateY(-8px) scale(0.96);
}
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.96);
}

/* View switcher transitions */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.2s ease-out;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateX(12px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(-12px);
}

/* Tooltip animation */
@keyframes tooltipFade {
  0% {
    opacity: 0;
    transform: scale(0.9) translateY(8px) rotateX(6deg);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0) rotateX(0deg);
  }
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .glass-dropdown {
    font-size: 0.875rem;
  }
  .glass-dropdown button {
    min-height: 40px;
    min-width: 40px;
  }
}
</style>