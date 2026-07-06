import { onMounted, onBeforeUnmount } from 'vue';

/**
 * Register global keyboard shortcuts. Each shortcut is an object:
 * { keys: string (e.g., 'Alt+S', 'Ctrl+N'), action: function, description: string }
 */
export function useKeyboardShortcuts(shortcuts) {
  function handler(event) {
    for (const shortcut of shortcuts) {
      const { keys, action } = shortcut;
      if (matchKeys(event, keys)) {
        event.preventDefault();
        action();
        return;
      }
    }
  }

  function matchKeys(event, shortcut) {
    const parts = shortcut.split('+');
    const modifiers = parts.slice(0, -1).map(p => p.toLowerCase());
    const mainKey = parts[parts.length - 1].toLowerCase();

    const ctrlRequired = modifiers.includes('ctrl') || modifiers.includes('control');
    const altRequired = modifiers.includes('alt');
    const shiftRequired = modifiers.includes('shift');
    const metaRequired = modifiers.includes('meta') || modifiers.includes('cmd');

    const ctrlPressed = event.ctrlKey || event.metaKey;
    const altPressed = event.altKey;
    const shiftPressed = event.shiftKey;
    const metaPressed = event.metaKey;

    if (ctrlRequired !== ctrlPressed) return false;
    if (altRequired !== altPressed) return false;
    if (shiftRequired !== shiftPressed) return false;
    if (metaRequired !== metaPressed) return false;

    return event.key.toLowerCase() === mainKey;
  }

  onMounted(() => window.addEventListener('keydown', handler));
  onBeforeUnmount(() => window.removeEventListener('keydown', handler));
}