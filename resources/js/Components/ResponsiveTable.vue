<template>
  <div class="relative" ref="outerRef">
    <!-- Scroll indicator left -->
    <div
      v-if="hasScrollLeft"
      class="absolute left-0 top-0 bottom-0 w-6 bg-gradient-to-r from-black/30 to-transparent pointer-events-none z-10 rounded-l-xl"
    ></div>
    <!-- Scrollable container -->
    <div
      ref="scrollRef"
      class="overflow-x-auto custom-scrollbar"
      @scroll="onScroll"
    >
      <table class="w-full text-sm text-left min-w-[600px]">
        <thead class="text-xs text-white/50 uppercase border-b border-white/10">
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-4 py-3 bg-gray-900"
              :class="{ 'sticky left-0 z-20': col.sticky }"
            >
              <slot :name="'header-' + col.key" :column="col">{{ col.label }}</slot>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, idx) in rows"
            :key="row.id || idx"
            class="border-b border-white/5 hover:bg-white/5 transition-colors"
          >
            <td
              v-for="col in columns"
              :key="col.key"
              class="px-4 py-3"
              :class="{ 'sticky left-0 z-10 bg-gray-900': col.sticky }"
            >
              <slot :name="'cell-' + col.key" :row="row" :column="col">{{ row[col.key] }}</slot>
            </td>
          </tr>
          <tr v-if="!rows.length">
            <td :colspan="columns.length" class="text-center py-8 text-white/50">No data.</td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- Scroll indicator right -->
    <div
      v-if="hasScrollRight"
      class="absolute right-0 top-0 bottom-0 w-6 bg-gradient-to-l from-black/30 to-transparent pointer-events-none z-10 rounded-r-xl"
    ></div>

    <!-- Mobile card view -->
    <div class="block sm:hidden mt-2 space-y-3">
      <div
        v-for="(row, idx) in rows"
        :key="'card-' + (row.id || idx)"
        class="glass-surface rounded-lg p-3"
      >
        <div v-for="col in columns" :key="col.key" class="flex justify-between py-1">
          <span class="text-xs text-white/50">{{ col.label }}</span>
          <span class="text-sm text-white">
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
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 3px; }
</style>