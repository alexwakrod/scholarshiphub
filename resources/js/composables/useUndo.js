import { ref, onBeforeUnmount } from 'vue';

/**
 * Composable for undo actions with a configurable timeout.
 * Example:
 *   const { undo, setUndoAction } = useUndo();
 *   setUndoAction(() => deleteItem(id), 5000, 'Item deleted');
 *   // Later undo() will execute the callback.
 */
export function useUndo() {
  const undoAction = ref(null);
  const undoMessage = ref('');
  let timer = null;

  function clearUndo() {
    undoAction.value = null;
    undoMessage.value = '';
    if (timer) {
      clearTimeout(timer);
      timer = null;
    }
  }

  function setUndoAction(callback, timeout = 5000, message = 'Action undone') {
    clearUndo();
    undoAction.value = callback;
    undoMessage.value = message;
    timer = setTimeout(() => {
      clearUndo();
    }, timeout);
  }

  function undo() {
    if (undoAction.value) {
      try {
        undoAction.value();
      } catch (e) {
        console.error('[useUndo] undo action error:', e);
      }
      clearUndo();
    }
  }

  onBeforeUnmount(() => {
    clearUndo();
  });

  return { undo, setUndoAction, undoAction, undoMessage };
}