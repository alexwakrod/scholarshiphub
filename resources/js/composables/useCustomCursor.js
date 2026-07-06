import { onMounted, onBeforeUnmount } from 'vue';

export function useCustomCursor() {
  const cursor = document.createElement('div');
  cursor.id = 'custom-cursor';
  document.body.appendChild(cursor);

  const style = document.createElement('style');
  style.textContent = `
    #custom-cursor {
      position: fixed;
      pointer-events: none;
      z-index: 9999;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: rgba(59,130,246,0.3);
      backdrop-filter: blur(4px);
      border: 1px solid rgba(255,255,255,0.5);
      box-shadow: 0 0 8px rgba(59,130,246,0.6);
      transform: translate(-50%, -50%) scale(1);
      transition: width 0.15s, height 0.15s, background 0.15s, transform 0.15s;
    }
    #custom-cursor.pointer {
      width: 28px;
      height: 28px;
      background: rgba(59,130,246,0.5);
    }
  `;
  document.head.appendChild(style);

  let rafId = null;
  let mouseX = 0, mouseY = 0;

  function updatePosition() {
    cursor.style.left = mouseX + 'px';
    cursor.style.top = mouseY + 'px';
    rafId = null;
  }

  function onMouseMove(e) {
    mouseX = e.clientX;
    mouseY = e.clientY;
    if (!rafId) rafId = requestAnimationFrame(updatePosition);
  }

  function onMouseOver(e) {
    const target = e.target.closest('a, button, input, select, textarea, [role="button"], .cursor-pointer');
    if (target) cursor.classList.add('pointer');
  }

  function onMouseOut(e) {
    cursor.classList.remove('pointer');
  }

  function onMouseDown() {
    cursor.style.transform = 'translate(-50%, -50%) scale(0.85)';
  }

  function onMouseUp() {
    cursor.style.transform = 'translate(-50%, -50%) scale(1)';
  }

  onMounted(() => {
    document.addEventListener('mousemove', onMouseMove, { passive: true });
    document.addEventListener('mouseover', onMouseOver, { passive: true });
    document.addEventListener('mouseout', onMouseOut, { passive: true });
    document.addEventListener('mousedown', onMouseDown);
    document.addEventListener('mouseup', onMouseUp);
  });

  onBeforeUnmount(() => {
    document.removeEventListener('mousemove', onMouseMove);
    document.removeEventListener('mouseover', onMouseOver);
    document.removeEventListener('mouseout', onMouseOut);
    document.removeEventListener('mousedown', onMouseDown);
    document.removeEventListener('mouseup', onMouseUp);
    if (cursor.parentNode) cursor.parentNode.removeChild(cursor);
    if (style.parentNode) style.parentNode.removeChild(style);
  });
}