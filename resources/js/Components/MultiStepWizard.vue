<template>
  <div class="space-y-6">
    <!-- Progress indicator -->
    <div class="flex items-center gap-2">
      <div
        v-for="(step, idx) in steps"
        :key="idx"
        class="flex-1 h-2 rounded-full"
        :class="idx <= currentStep ? 'bg-blue-500' : 'bg-gray-200 dark:bg-white/10'"
      ></div>
    </div>

    <!-- Step title -->
    <h2 v-if="steps[currentStep]" class="text-xl font-semibold text-gray-900 dark:text-white">
      {{ steps[currentStep].title }}
    </h2>

    <!-- Step content slot -->
    <slot :name="'step-' + (currentStep + 1)" :step="steps[currentStep]" :next="next" :prev="prev" :goTo="goToStep" />

    <!-- Navigation buttons -->
    <div class="flex justify-between pt-4">
      <button
        v-if="currentStep > 0"
        @click="prev"
        class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-white text-sm hover:bg-gray-300 dark:hover:bg-white/20"
      >
        Back
      </button>
      <div v-else></div>

      <div class="flex gap-3">
        <button
          v-if="showSkip && currentStep < steps.length - 1"
          @click="$emit('skip')"
          class="px-4 py-2 rounded-lg text-gray-500 dark:text-white/50 text-sm hover:text-gray-900 dark:hover:text-white"
        >
          Skip
        </button>
        <button
          v-if="currentStep < steps.length - 1"
          @click="next"
          :disabled="!canProceed"
          class="px-6 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50"
        >
          Next
        </button>
        <button
          v-else
          @click="finish"
          :disabled="!canProceed"
          class="px-6 py-2 rounded-lg bg-green-600 text-white text-sm hover:bg-green-700 disabled:opacity-50"
        >
          {{ finishLabel }}
        </button>
      </div>
    </div>

    <!-- Error message -->
    <p v-if="errorMessage" class="text-red-500 dark:text-red-400 text-sm">{{ errorMessage }}</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  steps: { type: Array, required: true },
  finishLabel: { type: String, default: 'Finish' },
  showSkip: { type: Boolean, default: false },
});

const emit = defineEmits(['finish', 'skip', 'step-change']);

const currentStep = ref(0);
const errorMessage = ref('');

const canProceed = computed(() => {
  const step = props.steps[currentStep.value];
  if (step && typeof step.validate === 'function') {
    try {
      const valid = step.validate();
      if (!valid) {
        errorMessage.value = 'Please complete the required fields before proceeding.';
        return false;
      }
      errorMessage.value = '';
      return true;
    } catch (e) {
      errorMessage.value = e.message || 'Validation failed.';
      return false;
    }
  }
  errorMessage.value = '';
  return true;
});

function next() {
  if (!canProceed.value) return;
  if (currentStep.value < props.steps.length - 1) {
    currentStep.value++;
    emit('step-change', currentStep.value);
    errorMessage.value = '';
  }
}

function prev() {
  if (currentStep.value > 0) {
    currentStep.value--;
    emit('step-change', currentStep.value);
    errorMessage.value = '';
  }
}

function finish() {
  if (!canProceed.value) return;
  emit('finish');
}

function goToStep(index) {
  if (index >= 0 && index < props.steps.length) {
    currentStep.value = index;
    emit('step-change', index);
    errorMessage.value = '';
  }
}

onMounted(() => {
  emit('step-change', 0);
});
</script>