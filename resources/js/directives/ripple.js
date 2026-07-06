/**
 * Vue 3 custom directive: v-ripple
 * Adds a material-like ripple effect on click/tap.
 */
export default {
  mounted(el) {
    el.addEventListener('pointerdown', createRipple);
  },
  beforeUnmount(el) {
    el.removeEventListener('pointerdown', createRipple);
  },
};

function createRipple(event) {
  const el = event.currentTarget;
  const rect = el.getBoundingClientRect();
  const size = Math.max(rect.width, rect.height);
  const x = event.clientX - rect.left - size / 2;
  const y = event.clientY - rect.top - size / 2;

  const ripple = document.createElement('span');
  ripple.style.position = 'absolute';
  ripple.style.borderRadius = '50%';
  ripple.style.width = ripple.style.height = size + 'px';
  ripple.style.left = x + 'px';
  ripple.style.top = y + 'px';
  ripple.style.background = 'rgba(255,255,255,0.3)';
  ripple.style.transform = 'scale(0)';
  ripple.style.animation = 'ripple 600ms ease-out';
  ripple.style.pointerEvents = 'none';
  ripple.classList.add('ripple-effect');

  el.style.position = 'relative';
  el.style.overflow = 'hidden';
  el.appendChild(ripple);

  ripple.addEventListener('animationend', () => {
    ripple.remove();
  });
}

// Add global keyframe style
if (typeof document !== 'undefined') {
  const style = document.createElement('style');
  style.textContent = `
    @keyframes ripple {
      to { transform: scale(4); opacity: 0; }
    }
  `;
  document.head.appendChild(style);
}