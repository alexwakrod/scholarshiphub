import { ref, onBeforeUnmount } from 'vue';
import { useUndo } from './useUndo'; // reusing the undo composable to manage the toast

/**
 * Composable for destructive actions with an undo window.
 * When execute() is called, the actual delete callback is scheduled after `delay` ms.
 * An undo toast is shown; if the user clicks "Undo", the callback is cancelled.
 */
export function useDeferredDelete() {
  const { undo, setUndoAction } = useUndo();
  let timer = null;

  /**
   * Schedule a deletion.
   * @param {Function} deleteFn - async function that performs the actual delete.
   * @param {number} delay - milliseconds before execution (default 5000).
   * @param {string} message - toast message.
   */
  function scheduleDelete(deleteFn, delay = 5000, message = 'Item deleted.') {
    if (timer) {
      clearTimeout(timer);
    }

    setUndoAction(() => {
      clearTimeout(timer);
      timer = null;
    }, delay, message);

    timer = setTimeout(async () => {
      try {
        await deleteFn();
      } catch (e) {
        console.error('[DeferredDelete] delete execution failed:', e);
      } finally {
        timer = null;
      }
    }, delay);
  }

  /**
   * Immediately cancel any pending deletion.
   */
  function cancelDelete() {
    if (timer) {
      clearTimeout(timer);
      timer = null;
    }
  }

  onBeforeUnmount(() => {
    cancelDelete();
  });

  return { scheduleDelete, cancelDelete, undo };
}