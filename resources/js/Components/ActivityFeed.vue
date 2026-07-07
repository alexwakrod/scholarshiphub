<template>
  <div class="space-y-3">
    <!-- Title -->
    <h3 v-if="title" class="text-sm font-semibold tracking-wide uppercase" :class="isDark ? 'text-white/60' : 'text-gray-500'">
      {{ title }}
    </h3>

    <!-- Loading skeleton -->
    <div v-if="loading" class="space-y-2">
      <div v-for="n in 3" :key="n" class="skeleton-row glass-surface rounded-xl p-3 animate-pulse flex items-start gap-3">
        <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0" :class="isDark ? 'bg-white/10' : 'bg-gray-300'"></div>
        <div class="flex-1 space-y-1.5">
          <div class="h-3 rounded w-3/4" :class="isDark ? 'bg-white/10' : 'bg-gray-200'"></div>
          <div class="h-2 rounded w-1/2" :class="isDark ? 'bg-white/5' : 'bg-gray-100'"></div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else-if="entries.length === 0" class="text-sm" :class="isDark ? 'text-white/50' : 'text-gray-400'">
      No recent activity.
    </div>

    <!-- Activity entries -->
    <div v-else class="space-y-2">
      <div
        v-for="entry in entries"
        :key="entry.id"
        class="glass-surface rounded-xl p-3 flex items-start gap-3 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg group cursor-default"
      >
        <!-- Colored dot -->
        <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0" :class="dotClass(entry.action)"></div>

        <div class="flex-1 min-w-0">
          <p class="text-sm truncate" :class="isDark ? 'text-white/80' : 'text-gray-700'">
            <span class="font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">
              {{ entry.user?.name || 'System' }}
            </span>
            {{ actionLabel(entry.action) }}
            <span v-if="entry.subject_type" class="text-xs opacity-60 ml-1">
              {{ entry.subject_type.split('\\').pop() }}
            </span>
          </p>
          <p class="text-xs mt-0.5" :class="isDark ? 'text-white/40' : 'text-gray-500'">
            {{ timeAgo(entry.created_at) }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  title: { type: String, default: 'Recent Activity' },
  limit: { type: Number, default: 10 },
  pollInterval: { type: Number, default: 30000 },
});

const loading = ref(true);
const entries = ref([]);
const isDark = ref(document.documentElement.classList.contains('dark'));

let pollTimer = null;
let themeObserver = null;

// --- Fetch activity ---
async function fetchActivity() {
  try {
    const { data } = await axios.get('/admin/audit-logs/recent', { params: { limit: props.limit } });
    entries.value = data;
  } catch (e) {
    console.error('[ActivityFeed] fetch error:', e);
  } finally {
    loading.value = false;
  }
}

// --- Lifecycle ---
onMounted(() => {
  fetchActivity();
  if (props.pollInterval > 0) {
    pollTimer = setInterval(fetchActivity, props.pollInterval);
  }

  // Watch theme changes
  themeObserver = new MutationObserver(() => {
    isDark.value = document.documentElement.classList.contains('dark');
  });
  themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});

onBeforeUnmount(() => {
  if (pollTimer) clearInterval(pollTimer);
  if (themeObserver) themeObserver.disconnect();
});

// --- Helpers ---
function dotClass(action) {
  switch (action) {
    case 'created': return isDark.value ? 'bg-green-400 shadow-[0_0_6px_rgba(74,222,128,0.5)]' : 'bg-green-500';
    case 'updated': return isDark.value ? 'bg-blue-400 shadow-[0_0_6px_rgba(96,165,250,0.5)]' : 'bg-blue-500';
    case 'deleted': return isDark.value ? 'bg-red-400 shadow-[0_0_6px_rgba(248,113,113,0.5)]' : 'bg-red-500';
    case 'login':   return isDark.value ? 'bg-yellow-400 shadow-[0_0_6px_rgba(250,204,21,0.5)]' : 'bg-yellow-500';
    case 'logout':  return isDark.value ? 'bg-gray-400' : 'bg-gray-500';
    default:        return isDark.value ? 'bg-white/40' : 'bg-gray-400';
  }
}

function actionLabel(action) {
  const labels = {
    created: 'created',
    updated: 'updated',
    deleted: 'deleted',
    login: 'logged in',
    logout: 'logged out',
  };
  return labels[action] || action;
}

function timeAgo(dateStr) {
  try {
    const now = new Date();
    const date = new Date(dateStr);
    const diff = Math.floor((now - date) / 1000);
    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return Math.floor(diff / 86400) + 'd ago';
  } catch { return ''; }
}
</script>

<style scoped>
/* Glass surface for each entry row */
.glass-surface {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.06);
}
.dark .glass-surface {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.06);
}

/* Skeleton pulse animation */
.animate-pulse {
  animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}

/* Responsive & accessibility */
@media (max-width: 767px) {
  .glass-surface:hover {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .glass-surface,
  .glass-surface:hover {
    transition: none !important;
    transform: none !important;
  }
  .animate-pulse {
    animation: none !important;
  }
}
</style>