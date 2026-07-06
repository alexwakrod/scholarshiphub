import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import axios from 'axios';
import { useCustomCursor } from './composables/useCustomCursor';
import { useTactileFeedback } from './composables/useTactileFeedback';
import '../css/app.css';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Axios CSRF setup
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Axios interceptor
axios.interceptors.response.use(
  response => response,
  error => {
    console.error('[Axios] Error:', error?.response?.status, error?.config?.url);
    return Promise.reject(error);
  }
);

// Service worker
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then(registration => console.log('ServiceWorker registered:', registration.scope))
      .catch(err => console.error('ServiceWorker registration failed:', err));
  });
}

// Global ripple effect for any element with class "ripple"
document.addEventListener('pointerdown', (event) => {
  const el = event.target.closest('.ripple');
  if (!el) return;
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
  ripple.style.background = 'rgba(255,255,255,0.25)';
  ripple.style.transform = 'scale(0)';
  ripple.style.animation = 'global-ripple 600ms ease-out';
  ripple.style.pointerEvents = 'none';

  el.style.position = 'relative';
  el.style.overflow = 'hidden';
  el.appendChild(ripple);

  ripple.addEventListener('animationend', () => ripple.remove());
});

const globalStyle = document.createElement('style');
globalStyle.textContent = `
  @keyframes global-ripple {
    to { transform: scale(4); opacity: 0; }
  }
`;
document.head.appendChild(globalStyle);


window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});

createInertiaApp({
  title: title => `${title} - ScholarshipHub`,
  resolve: (name) => {
    const page = resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue')
    );
    return page;
  },
  setup({ el, App, props, plugin }) {
    const vueApp = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .mixin({
        methods: {
          handleError(error, info) {
            console.error('[Vue] Error:', error, 'Info:', info);
            if (window.Sentry) window.Sentry.captureException(error);
          }
        }
      });

    vueApp.config.errorHandler = (err, instance, info) => {
      console.error('[Vue Global Error]', err, info);
      if (window.Sentry) window.Sentry.captureException(err);
    };

    useCustomCursor();

    const { tapFeedback } = useTactileFeedback();
    document.addEventListener('click', (e) => {
      const target = e.target.closest('button, a, [role="button"], input[type="submit"], input[type="button"], .tactile-feedback');
      if (target) tapFeedback();
    }, { passive: true });

    vueApp.mount(el);
  },
  progress: {
    delay: 250,
    color: '#3B82F6',
    includeCSS: true,
    showSpinner: false,
  },
});