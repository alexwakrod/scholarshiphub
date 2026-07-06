import { ref } from 'vue';

export function useHoverDelay(delay = 300) {
  const isHovered = ref(false);
  let timeout = null;

  function onMouseEnter() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      isHovered.value = true;
    }, delay);
  }

  function onMouseLeave() {
    clearTimeout(timeout);
    isHovered.value = false;
  }

  // For touch: long‑press to show
  let touchTimer = null;
  function onTouchStart() {
    touchTimer = setTimeout(() => {
      isHovered.value = true;
    }, 500);
  }
  function onTouchEnd() {
    clearTimeout(touchTimer);
    isHovered.value = false;
  }

  return {
    isHovered,
    onMouseEnter,
    onMouseLeave,
    onTouchStart,
    onTouchEnd,
  };
}