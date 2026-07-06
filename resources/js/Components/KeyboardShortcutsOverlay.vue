<template>
  <GlassModal :modelValue="visible" @update:modelValue="emit('update:visible', $event)" @close="emit('update:visible', false)">
    <div class="space-y-4">
      <h3 class="text-xl font-bold text-gray-900 dark:text-white">Keyboard Shortcuts</h3>
      <ul class="space-y-2">
        <li v-for="shortcut in shortcuts" :key="shortcut.keys" class="flex items-center justify-between text-sm">
          <span class="text-gray-700 dark:text-white/70">{{ shortcut.description }}</span>
          <kbd class="px-2 py-0.5 rounded bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white text-xs font-mono">{{ shortcut.keys }}</kbd>
        </li>
      </ul>
    </div>
  </GlassModal>
</template>

<script setup>
import GlassModal from './GlassModal.vue';

defineProps({
  visible: Boolean,
  shortcuts: Array,
});

const emit = defineEmits(['update:visible']);
</script>