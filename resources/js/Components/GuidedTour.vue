<template>
  <div v-if="active" class="fixed inset-0 z-50 bg-black/60 dark:bg-black/70 backdrop-blur-sm transition-opacity" />
  <div
    v-if="active && currentStep"
    class="fixed z-50 glass-overlay rounded-xl p-4 border border-gray-200 dark:border-white/20 shadow-2xl max-w-sm"
    :style="tooltipStyle"
  >
    <h4 class="text-gray-900 dark:text-white font-semibold">{{ currentStep.title }}</h4>
    <p class="text-sm text-gray-600 dark:text-white/70 mt-2">{{ currentStep.content }}</p>
    <div class="flex items-center justify-between mt-4">
      <button @click="prev" :disabled="stepIndex === 0" class="text-xs text-gray-400 dark:text-white/50 hover:text-gray-900 dark:hover:text-white disabled:opacity-30">← Back</button>
      <button v-if="stepIndex < steps.length - 1" @click="next" class="px-3 py-1 bg-blue-600 rounded text-xs text-white">Next</button>
      <button v-else @click="finish" class="px-3 py-1 bg-green-600 rounded text-xs text-white">Finish</button>
      <button @click="skip" class="px-3 py-1 bg-gray-200 dark:bg-white/10 rounded text-xs text-gray-700 dark:text-white/70 hover:text-gray-900 dark:hover:text-white">Skip</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  steps: { type: Array, required: true },
  start: { type: Boolean, default: false },
});

const emit = defineEmits(['finish', 'skip']);

const active = ref(false);
const stepIndex = ref(0);
const tooltipStyle = ref({ top: '50px', left: '50px' });

const currentStep = computed(() => props.steps[stepIndex.value] || null);

function positionForElement(selector) {
  if (!selector) return;
  const el = document.querySelector(selector);
  if (!el) return;
  const rect = el.getBoundingClientRect();
  const tooltipWidth = 320, tooltipHeight = 180, vw = window.innerWidth, vh = window.innerHeight;
  let top = rect.bottom + 10;
  let left = rect.left + rect.width / 2 - tooltipWidth / 2;
  if (left < 10) left = 10;
  if (left + tooltipWidth > vw - 10) left = vw - tooltipWidth - 10;
  if (top + tooltipHeight > vh - 10) top = rect.top - tooltipHeight - 10;
  if (top < 10) { top = 10; left = rect.right + 10; if (left + tooltipWidth > vw - 10) left = rect.left - tooltipWidth - 10; }
  tooltipStyle.value = { top: `${top}px`, left: `${left}px` };
}

let scrollHandler = null, resizeHandler = null;

watch(() => [props.start, stepIndex.value], () => {
  if (props.start && currentStep.value) positionForElement(currentStep.value.element);
});

onMounted(() => {
  scrollHandler = () => { if (active.value && currentStep.value) positionForElement(currentStep.value.element); };
  resizeHandler = () => { if (active.value && currentStep.value) positionForElement(currentStep.value.element); };
  window.addEventListener('scroll', scrollHandler, true);
  window.addEventListener('resize', resizeHandler);
});

onBeforeUnmount(() => {
  window.removeEventListener('scroll', scrollHandler, true);
  window.removeEventListener('resize', resizeHandler);
});

function next() { if (stepIndex.value < props.steps.length - 1) { stepIndex.value++; positionForElement(props.steps[stepIndex.value]?.element); } }
function prev() { if (stepIndex.value > 0) { stepIndex.value--; positionForElement(props.steps[stepIndex.value]?.element); } }
function finish() { active.value = false; emit('finish'); }
function skip() { active.value = false; emit('skip'); }

function onKeydown(e) { if (e.key === 'Escape') skip(); }
onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));
</script>