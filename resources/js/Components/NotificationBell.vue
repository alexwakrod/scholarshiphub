<template>
  <div class="relative" ref="wrapperRef">
    <!-- Trigger button -->
    <button
      @click="toggleDropdown"
      class="group relative w-9 h-9 rounded-xl glass-surface hover:glass-elevated transition-all duration-300 hover:scale-105 flex items-center justify-center text-gray-500 dark:text-white/40 hover:text-gray-900 dark:hover:text-white"
      aria-label="Notifications"
    >
      <svg
        class="w-4.5 h-4.5 transition-transform duration-300 group-hover:scale-110"
        fill="none" stroke="currentColor" viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>

      <!-- Badge -->
      <div
        v-if="unreadCount > 0"
        class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] rounded-full bg-gradient-to-r from-red-500 to-orange-500 flex items-center justify-center text-[9px] font-bold text-white shadow-[0_0_16px_rgba(239,68,68,0.5)] px-1 leading-none"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </div>

      <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-20 transition-opacity blur-sm"></div>
    </button>

    <!-- Dropdown (teleported) -->
    <Teleport to="body">
      <div
        v-if="isOpen"
        ref="dropdownRef"
        class="fixed z-50 w-80 max-h-[400px] overflow-y-auto custom-scrollbar rounded-2xl glass-overlay border border-gray-200 dark:border-white/10 shadow-2xl p-2"
        :style="dropdownStyle"
      >
        <!-- Header -->
        <div class="flex items-center justify-between px-3 py-2 border-b border-gray-200 dark:border-white/5">
          <span class="text-sm font-medium text-gray-900 dark:text-white">Notifications</span>
          <button
            v-if="unreadCount > 0"
            @click="markAllAsRead"
            class="text-[10px] text-gray-500 dark:text-white/40 hover:text-gray-700 dark:hover:text-white/70 transition-colors"
          >
            Mark all read
          </button>
        </div>

        <!-- Empty state -->
        <div v-if="notifications.length === 0" class="py-8 text-center">
          <svg class="w-10 h-10 mx-auto mb-3 text-gray-300 dark:text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <p class="text-sm text-gray-500 dark:text-white/40">No notifications</p>
        </div>

        <!-- List -->
        <div v-else class="space-y-1">
          <div
            v-for="notif in notifications"
            :key="notif.id"
            class="p-3 rounded-xl transition-all duration-200 cursor-pointer group"
            :class="notif.read_at
              ? 'hover:bg-gray-100 dark:hover:bg-white/5'
              : 'bg-blue-50 dark:bg-blue-500/10 border-l-2 border-blue-400'"
            @click="markAsRead(notif.id)"
          >
            <div class="flex items-start gap-3">
              <!-- Icon -->
              <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" :class="getNotificationColor(notif.type)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="notif.type === 'deadline_reminder'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  <path v-else-if="notif.type === 'new_match'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-700 dark:text-white/80 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">
                  {{ notif.data?.message || 'Notification' }}
                </p>
                <p class="text-[10px] text-gray-400 dark:text-white/30 mt-0.5">{{ timeAgo(notif.created_at) }}</p>
              </div>
              <div v-if="!notif.read_at" class="w-1.5 h-1.5 rounded-full bg-blue-400 shrink-0 mt-2"></div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, inject, computed, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const isOpen = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const wrapperRef = ref(null);
const dropdownRef = ref(null);

const dropdownStyle = computed(() => {
  if (!wrapperRef.value || !isOpen.value) return { top: '0px', right: '0px' };
  const rect = wrapperRef.value.getBoundingClientRect();
  return {
    top: (rect.bottom + 8) + 'px',
    right: (window.innerWidth - rect.right) + 'px',
  };
});

const showToast = inject('showToast', null);

async function fetchNotifications() {
  try {
    const res = await axios.get('/notifications/recent');
    const newData = res.data;
    const prevUnreadCount = unreadCount.value;
    notifications.value = newData;
    unreadCount.value = newData.filter(n => !n.read_at).length;

    // Show toast for newly arrived notifications
    if (showToast && unreadCount.value > prevUnreadCount) {
      showToast('New notification received', 'info');
    }
  } catch (e) {
    console.error('Failed to fetch notifications:', e);
  }
}

function toggleDropdown() {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    fetchNotifications();
  }
}

async function markAsRead(id) {
  try {
    await axios.put(`/notifications/${id}/read`);
    const notif = notifications.value.find(n => n.id === id);
    if (notif) {
      notif.read_at = new Date().toISOString();
      unreadCount.value = notifications.value.filter(n => !n.read_at).length;

      // Navigate based on notification data
      isOpen.value = false;
      if (notif.data?.url) {
        router.visit(notif.data.url);
      } else if (notif.type === 'review_completed' && notif.data?.document_id) {
        router.visit(route('documents.show', { id: notif.data.document_id }));
      }
    }
  } catch (e) {
    console.error('Failed to mark notification as read:', e);
  }
}

async function markAllAsRead() {
  try {
    await axios.put('/notifications/mark-all-read');
    notifications.value.forEach(n => n.read_at = new Date().toISOString());
    unreadCount.value = 0;
  } catch (e) {
    console.error('Failed to mark all as read:', e);
  }
}

function getNotificationColor(type) {
  const colors = {
    deadline_reminder: 'bg-red-100 dark:bg-red-500/20 text-red-500 dark:text-red-400',
    new_match: 'bg-green-100 dark:bg-green-500/20 text-green-500 dark:text-green-400',
    review_completed: 'bg-blue-100 dark:bg-blue-500/20 text-blue-500 dark:text-blue-400',
  };
  return colors[type] || 'bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-white/40';
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

function handleClickOutside(event) {
  if (!isOpen.value) return;
  if (wrapperRef.value && wrapperRef.value.contains(event.target)) return;
  if (dropdownRef.value && dropdownRef.value.contains(event.target)) return;
  isOpen.value = false;
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside, true);
  fetchNotifications();
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside, true);
});
</script>

<style scoped>
.glass-surface {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.dark .glass-surface {
  background: rgba(255, 255, 255, 0.03);
  border-color: rgba(255, 255, 255, 0.06);
}

.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.07);
  border-color: rgba(255, 255, 255, 0.12);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.glass-overlay {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(0, 0, 0, 0.12);
}
.dark .glass-overlay {
  background: rgba(0, 0, 0, 0.85);
  border-color: rgba(255, 255, 255, 0.08);
}

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 999px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); }
</style>