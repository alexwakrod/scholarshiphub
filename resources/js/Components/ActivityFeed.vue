<template>
  <div class="space-y-3">
    <h3 v-if="title" class="text-sm font-semibold text-gray-500 dark:text-white/70 uppercase">{{ title }}</h3>
    <div v-if="loading" class="text-center py-4">
      <SkeletonLoader type="list" :count="3" />
    </div>
    <div v-else-if="entries.length === 0" class="text-gray-400 dark:text-white/50 text-sm">No recent activity.</div>
    <div v-else class="space-y-2">
      <div
        v-for="entry in entries"
        :key="entry.id"
        class="flex items-start gap-3 text-sm"
      >
        <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0" :class="dotClass(entry.action)"></div>
        <div class="flex-1 min-w-0">
          <p class="text-gray-800 dark:text-white/80 truncate">
            <span class="font-medium text-gray-900 dark:text-white">{{ entry.user?.name || 'System' }}</span>
            {{ actionLabel(entry.action) }}
            <span v-if="entry.subject_type" class="text-gray-500 dark:text-white/50">{{ entry.subject_type.split('\\').pop() }}</span>
          </p>
          <p class="text-xs text-gray-400 dark:text-white/40 mt-0.5">{{ timeAgo(entry.created_at) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';
import SkeletonLoader from './SkeletonLoader.vue';

const props = defineProps({
  title: { type: String, default: 'Recent Activity' },
  limit: { type: Number, default: 10 },
  pollInterval: { type: Number, default: 30000 },
});

const loading = ref(true);
const entries = ref([]);
let pollTimer = null;

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

onMounted(() => {
  fetchActivity();
  if (props.pollInterval > 0) {
    pollTimer = setInterval(fetchActivity, props.pollInterval);
  }
});

onBeforeUnmount(() => {
  if (pollTimer) clearInterval(pollTimer);
});

function dotClass(action) {
  switch (action) {
    case 'created': return 'bg-green-400';
    case 'updated': return 'bg-blue-400';
    case 'deleted': return 'bg-red-400';
    case 'login': return 'bg-yellow-400';
    case 'logout': return 'bg-gray-400';
    default: return 'bg-gray-300 dark:bg-white/40';
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