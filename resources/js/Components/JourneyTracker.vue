<template>
  <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 relative">
    <!-- step items -->
    <div
      v-for="(step, index) in steps"
      :key="step.id"
      class="flex-1 w-full"
    >
      <button
        @click="toggleStep(step.id)"
        class="w-full flex flex-col items-center cursor-pointer group"
      >
        <div
          class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
          :class="step.completed ? 'bg-green-500 text-white' : 'bg-white/10 text-white/50 group-hover:bg-white/20'"
        >
          {{ step.completed ? '✓' : index + 1 }}
        </div>
        <span class="text-xs mt-1 text-white/70">{{ step.label }}</span>
      </button>
      <div v-if="expanded === step.id" class="mt-2 ml-4 pl-4 border-l border-white/20">
        <ul class="space-y-1">
          <li v-for="task in step.tasks" :key="task.task" class="flex items-center gap-2 text-sm">
            <input
              type="checkbox"
              :checked="task.completed"
              @change="toggleTask(step.id, task.task)"
              class="rounded border-white/30"
            />
            <span :class="task.completed ? 'line-through text-white/40' : 'text-white/80'">{{ task.task }}</span>
          </li>
        </ul>
      </div>
    </div>
    <!-- connecting line (desktop) -->
    <div class="hidden sm:block absolute top-4 left-0 w-full h-0.5 bg-white/10 -z-10"></div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  steps: {
    type: Array,
    required: true,
    // each step: { id, label, completed (boolean), tasks: [{task, completed}] }
  },
});

const emit = defineEmits(['update:steps']);

const expanded = ref(null);

function toggleStep(id) {
  try {
    expanded.value = expanded.value === id ? null : id;
  } catch (e) {
    console.error('[JourneyTracker] toggleStep error:', e);
  }
}

function toggleTask(stepId, taskName) {
  try {
    const newSteps = props.steps.map(step => {
      if (step.id !== stepId) return step;
      return {
        ...step,
        tasks: step.tasks.map(t => t.task === taskName ? { ...t, completed: !t.completed } : t),
        completed: step.tasks.every(t => t.task === taskName ? !t.completed : t.completed) ? false : step.completed,
      };
    });
    // recompute overall step completion
    newSteps.forEach(step => {
      step.completed = step.tasks.every(t => t.completed);
    });
    emit('update:steps', newSteps);
  } catch (e) {
    console.error('[JourneyTracker] toggleTask error:', e);
  }
}
</script>