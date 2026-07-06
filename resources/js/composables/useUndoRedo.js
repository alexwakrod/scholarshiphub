import { ref, computed } from 'vue';

/**
 * Multi‑level undo/redo stack.
 * Each entry is an object: { undoFn, redoFn, description }
 */
const stack = ref([]);
const currentIndex = ref(-1);

export function useUndoRedo() {
  const canUndo = computed(() => currentIndex.value >= 0);
  const canRedo = computed(() => currentIndex.value < stack.value.length - 1);

  function push(undoFn, redoFn, description = 'Action') {
    // Discard any redo entries ahead of current index
    stack.value = stack.value.slice(0, currentIndex.value + 1);
    stack.value.push({ undoFn, redoFn, description, timestamp: Date.now() });
    currentIndex.value = stack.value.length - 1;
  }

  function undo() {
    if (!canUndo.value) return;
    const entry = stack.value[currentIndex.value];
    try {
      entry.undoFn();
      currentIndex.value--;
    } catch (e) {
      console.error('[UndoRedo] undo error:', e);
    }
  }

  function redo() {
    if (!canRedo.value) return;
    currentIndex.value++;
    const entry = stack.value[currentIndex.value];
    try {
      entry.redoFn();
    } catch (e) {
      console.error('[UndoRedo] redo error:', e);
    }
  }

  function clear() {
    stack.value = [];
    currentIndex.value = -1;
  }

  return { push, undo, redo, canUndo, canRedo, clear };
}