<template>
  <div class="relative inline">
    <span
      ref="textRef"
      class="truncate-text"
      :class="{ 'cursor-pointer': expandable }"
      @mouseenter="onMouseEnter"
      @mouseleave="onMouseLeave"
      @click="toggleExpand"
    >
      {{ text }}
    </span>
    <GlassTooltip
      v-if="isTruncated && tooltipVisible && !expanded"
      :visible="tooltipVisible"
      :targetRef="textRef"
    >
      {{ text }}
    </GlassTooltip>
    <span v-if="expandable && expanded" class="block text-gray-700 dark:text-white/70 text-sm mt-1">{{ text }}</span>
    <button
      v-if="isTruncated && expandable"
      @click="toggleExpand"
      class="text-xs text-blue-500 dark:text-blue-400 hover:underline ml-1"
    >
      {{ expanded ? 'Show less' : '...' }}
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import GlassTooltip from './GlassTooltip.vue';

const props = defineProps({
  text: { type: String, default: '' },
  maxLines: { type: Number, default: 2 },
  expandable: { type: Boolean, default: true },
});

const textRef = ref(null);
const isTruncated = ref(false);
const tooltipVisible = ref(false);
const expanded = ref(false);

function checkTruncation() {
  if (!textRef.value) return;
  const el = textRef.value;
  const computedStyle = window.getComputedStyle(el);
  const lineHeight = parseFloat(computedStyle.lineHeight);
  if (isNaN(lineHeight)) { isTruncated.value = false; return; }
  const maxHeight = lineHeight * props.maxLines;
  isTruncated.value = el.scrollHeight > maxHeight + 2;
}

onMounted(() => nextTick(checkTruncation));

function onMouseEnter() { if (isTruncated.value && !expanded.value) tooltipVisible.value = true; }
function onMouseLeave() { tooltipVisible.value = false; }
function toggleExpand() { expanded.value = !expanded.value; tooltipVisible.value = false; }
</script>

<style scoped>
.truncate-text {
  display: -webkit-box;
  -webkit-line-clamp: v-bind(maxLines);
  -webkit-box-orient: vertical;
  overflow: hidden;
  max-height: calc(1.5em * v-bind(maxLines));
}
</style>