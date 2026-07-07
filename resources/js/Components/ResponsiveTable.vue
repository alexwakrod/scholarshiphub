<template>
  <div class="relative" ref="outerRef">
    <!-- Scroll indicator left -->
    <div
      v-if="hasScrollLeft"
      class="absolute left-0 top-0 bottom-0 w-6 bg-gradient-to-r from-gray-900/30 dark:from-black/30 to-transparent pointer-events-none z-10 rounded-l-xl"
    ></div>

    <!-- Scrollable container -->
    <div
      ref="scrollRef"
      class="overflow-x-auto custom-scrollbar glass-elevated rounded-2xl border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)]"
      @scroll="onScroll"
    >
      <table class="w-full text-sm text-left min-w-[600px]">
        <!-- Sticky Header -->
        <thead class="sticky top-0 z-10">
          <tr class="glass-table-header border-b border-gray-200/30 dark:border-white/5">
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-white/40"
              :class="{ 'sticky left-0 z-20': col.sticky }"
            >
              <slot :name="'header-' + col.key" :column="col">{{ col.label }}</slot>
            </th>
          </tr>
        </thead>
        <!-- Body -->
        <tbody>
          <tr
            v-for="(row, idx) in rows"
            :key="row.id || idx"
            class="glass-table-row border-b border-gray-200/30 dark:border-white/5 transition-all duration-300 hover:bg-white/5 dark:hover:bg-white/5"
            :style="{ '--i': idx }"
          >
            <td
              v-for="col in columns"
              :key="col.key"
              class="px-4 py-3 text-gray-700 dark:text-white/80"
              :class="{ 'sticky left-0 z-10': col.sticky }"
            >
              <slot :name="'cell-' + col.key" :row="row" :column="col">{{ row[col.key] }}</slot>
            </td>
          </tr>
          <!-- Empty State -->
          <tr v-if="!rows.length">
            <td :colspan="columns.length" class="text-center py-12">
              <div class="glass-empty-state rounded-xl p-8 inline-flex flex-col items-center gap-3">
                <svg class="w-10 h-10 text-gray-400 dark:text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <span class="text-sm text-gray-500 dark:text-white/40">No data.</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Scroll indicator right -->
    <div
      v-if="hasScrollRight"
      class="absolute right-0 top-0 bottom-0 w-6 bg-gradient-to-l from-gray-900/30 dark:from-black/30 to-transparent pointer-events-none z-10 rounded-r-xl"
    ></div>

    <!-- Mobile card view -->
    <div class="block sm:hidden mt-4 space-y-3">
      <div
        v-for="(row, idx) in rows"
        :key="'card-' + (row.id || idx)"
        class="glass-elevated rounded-xl p-4 border border-gray-200/60 dark:border-white/5"
      >
        <div v-for="col in columns" :key="col.key" class="flex justify-between py-1.5">
          <span class="text-xs text-gray-500 dark:text-white/40 uppercase tracking-wider">{{ col.label }}</span>
          <span class="text-sm text-gray-900 dark:text-white/80">
            <slot :name="'cell-' + col.key" :row="row" :column="col">{{ row[col.key] }}</slot>
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  columns: { type: Array, required: true },
  rows: { type: Array, required: true },
});

const scrollRef = ref(null);
const hasScrollLeft = ref(false);
const hasScrollRight = ref(false);

function onScroll() {
  if (!scrollRef.value) return;
  const el = scrollRef.value;
  hasScrollLeft.value = el.scrollLeft > 5;
  hasScrollRight.value = el.scrollLeft < el.scrollWidth - el.clientWidth - 5;
}

onMounted(() => {
  if (scrollRef.value) {
    onScroll();
  }
});
</script>

<style scoped>
/* ============================================================
   GLASS RESPONSIVE TABLE – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

/* Elevated glass slab for table container */
.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.05);
}

/* Sticky header row background */
.glass-table-header {
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}
.dark .glass-table-header {
  background: rgba(0, 0, 0, 0.5);
}

/* Table row hover and entrance animation */
.glass-table-row {
  opacity: 0;
  animation: row-fade-in 0.35s ease-out forwards;
  animation-delay: calc(var(--i, 0) * 30ms);
  transform: rotateY(0.5deg) rotateX(0.2deg);
  transition: background 0.2s ease, transform 0.2s ease;
}
.glass-table-row:hover {
  transform: rotateY(0deg) rotateX(0deg) translateY(-1px) scale(1.005);
  background: rgba(255, 255, 255, 0.08);
  z-index: 5;
}
.dark .glass-table-row:hover {
  background: rgba(255, 255, 255, 0.04);
}

@keyframes row-fade-in {
  0% { opacity: 0; transform: rotateY(0.5deg) rotateX(0.2deg) translateY(8px); }
  100% { opacity: 1; transform: rotateY(0.5deg) rotateX(0.2deg) translateY(0); }
}

/* Empty state panel */
.glass-empty-state {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(0, 0, 0, 0.06);
}
.dark .glass-empty-state {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.06);
}

/* Custom scrollbar */
.custom-scrollbar::-webkit-scrollbar { height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.15);
  border-radius: 999px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.15);
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.25);
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.25);
}

/* Mobile & accessibility overrides */
@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .glass-table-row,
  .glass-table-row:hover {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .glass-table-row {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>