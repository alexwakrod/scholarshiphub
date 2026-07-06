import { ref, onMounted, onBeforeUnmount } from 'vue';

export const shortcuts = ref([
  { keys: 'Alt+S', action: null, description: 'Save current form' },
  { keys: 'Alt+N', action: null, description: 'Create new item' },
  { keys: 'Alt+F', action: null, description: 'Focus search' },
  { keys: 'Escape', action: null, description: 'Close modal / cancel' },
  { keys: '?', action: null, description: 'Show keyboard shortcuts' },
]);

export function useGlobalShortcuts() {
  let onSave = null;
  let onCreate = null;
  let onSearch = null;

  function setSaveAction(fn) { onSave = fn; }
  function setCreateAction(fn) { onCreate = fn; }
  function setSearchAction(fn) { onSearch = fn; }

  const showOverlay = ref(false);

  function handler(event) {
    // Ignore if typing in an input/textarea
    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;

    try {
      const key = event.key;
      const alt = event.altKey;
      if (alt && key === 's') {
        event.preventDefault();
        onSave?.();
        return;
      }
      if (alt && key === 'n') {
        event.preventDefault();
        onCreate?.();
        return;
      }
      if (alt && key === 'f') {
        event.preventDefault();
        onSearch?.();
        return;
      }
      if (key === 'Escape') {
        event.preventDefault();
        // handled by modal focus trap; but we can emit
        return;
      }
      if (key === '?' && !event.shiftKey && !event.altKey && !event.ctrlKey && !event.metaKey) {
        event.preventDefault();
        showOverlay.value = !showOverlay.value;
        return;
      }
    } catch (e) {
      console.error('[useGlobalShortcuts] handler error:', e);
    }
  }

  onMounted(() => window.addEventListener('keydown', handler));
  onBeforeUnmount(() => window.removeEventListener('keydown', handler));

  return { showOverlay, setSaveAction, setCreateAction, setSearchAction, shortcuts };
}