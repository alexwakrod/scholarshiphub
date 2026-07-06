<template>
  <AppLayout>
    <div class="min-h-screen px-4 py-8 sm:py-12">
      <div class="mx-auto max-w-3xl">
        <!-- Page Title with Glass Bead -->
        <h1 class="mb-8 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
          Notifications
        </h1>

        <!-- Elevated Glass Container -->
        <div class="notifications-wrapper glass-elevated-slab rounded-3xl p-6 md:p-8">
          <!-- Empty State – deliberate glass panel -->
          <div
            v-if="notifications.data.length === 0"
            class="glass-empty-state rounded-2xl p-12 text-center"
          >
            <svg
              class="mx-auto h-12 w-12 text-gray-400/40 dark:text-white/20"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.5"
                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
              />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-700 dark:text-white/70">
              No notifications
            </h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-white/40">
              You will be notified about deadlines, new matches, and review completions.
            </p>
          </div>

          <!-- Notification List with Staggered Entrance -->
          <transition-group
            v-else
            name="stagger"
            tag="div"
            class="space-y-3"
            appear
          >
            <div
              v-for="(notif, index) in notifications.data"
              :key="notif.id"
              class="notification-card group relative cursor-pointer rounded-2xl border border-gray-200/60 dark:border-white/10 glass-surface-card transition-all duration-500 hover:z-10 hover:-translate-y-2 hover:scale-[1.02] hover:shadow-2xl active:scale-95 active:translate-y-1"
              :style="{ '--i': index }"
              :class="{ 'opacity-60': notif.read_at }"
              @click="markRead(notif.id)"
              tabindex="0"
            >
              <!-- Ambient Backlight -->
              <div class="pointer-events-none absolute inset-0 rounded-2xl bg-gradient-to-r from-blue-500/10 to-purple-500/10 opacity-0 blur-xl transition-opacity duration-500 group-hover:opacity-100"></div>

              <div class="relative flex items-start gap-4 p-4">
                <!-- Unread Glass Bead -->
                <div
                  v-if="!notif.read_at"
                  class="unread-dot mt-1.5 h-2.5 w-2.5 flex-shrink-0 rounded-full bg-[#3b82f6] shadow-[0_0_12px_rgba(59,130,246,0.6)]"
                ></div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium leading-snug text-gray-900 dark:text-white text-shadow-glass">
                    {{ notif.data?.message || 'Notification' }}
                  </p>
                  <p class="mt-1 text-xs text-gray-500 dark:text-white/40 tracking-wide">
                    {{ timeAgo(notif.created_at) }}
                  </p>
                </div>
              </div>
            </div>
          </transition-group>
        </div>

        <!-- Pagination Micro Elements -->
        <div
          v-if="notifications.links"
          class="mt-8 flex justify-center gap-2"
        >
          <button
            v-for="link in notifications.links"
            :key="link.label"
            :href="link.url || '#'"
            :disabled="!link.url"
            class="pagination-btn micro-glass rounded-xl px-3 py-1.5 text-sm font-medium transition-all duration-300 hover:z-10 hover:-translate-y-0.5 hover:scale-[1.02] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#3b82f6] disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:translate-y-0 disabled:hover:scale-100 text-gray-700 dark:text-white/60 hover:text-gray-900 dark:hover:text-white"
            :class="{ 'active-page bg-blue-500/20 dark:bg-white/20 text-gray-900 dark:text-white': link.active }"
            v-html="link.label"
          ></button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import EmptyState from '@/Components/EmptyState.vue';

defineProps({
  notifications: Object,
});

async function markRead(id) {
  try {
    await axios.put(`/notifications/${id}/read`);
    location.reload();
  } catch (e) {
    console.error('markRead error', e);
  }
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
  } catch {
    return '';
  }
}
</script>

<style scoped>
/* ============================================================
   GLASS ARCHITECT'S BLUEPRINT – NOTIFICATIONS/INDEX.VUE
   ============================================================ */

/* ------ 1. Elevated Glass Container ------ */
.glass-elevated-slab {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow:
    0 16px 48px rgba(0, 0, 0, 0.12),
    inset 0 1px 0 rgba(255, 255, 255, 0.5);
  border-radius: 1.5rem; /* 24px */
  transition: background 0.3s ease;
}

.dark .glass-elevated-slab {
  background: rgba(0, 0, 0, 0.25);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow:
    0 16px 48px rgba(0, 0, 0, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.06);
}

/* ------ 2. Surface Notification Cards ------ */
.glass-surface-card {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  transform: rotateY(-1deg) rotateX(0.5deg); /* default tilt */
  will-change: transform, opacity;
  transition: transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1),
              box-shadow 0.4s ease,
              background 0.3s ease,
              opacity 0.3s ease;
}

.dark .glass-surface-card {
  background: rgba(255, 255, 255, 0.04);
}

.glass-surface-card:hover {
  transform: rotateY(0deg) rotateX(0deg) translateY(-8px) scale(1.02);
  box-shadow:
    0 24px 48px rgba(0, 0, 0, 0.15),
    inset 0 1px 0 rgba(255, 255, 255, 0.6);
  background: rgba(255, 255, 255, 0.35);
}

.dark .glass-surface-card:hover {
  background: rgba(255, 255, 255, 0.08);
  box-shadow:
    0 24px 48px rgba(0, 0, 0, 0.5),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.glass-surface-card:active {
  transform: scale(0.95) translateY(4px);
  transition-duration: 0.1s;
}

/* ------ 3. Unread Glass Bead ------ */
.unread-dot {
  box-shadow: 0 0 12px rgba(59, 130, 246, 0.6);
  transition: box-shadow 0.3s ease;
}

.notification-card:hover .unread-dot {
  box-shadow: 0 0 18px rgba(59, 130, 246, 0.8);
}

/* ------ 4. Text Readability ------ */
.text-shadow-glass {
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}
.dark .text-shadow-glass {
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

/* ------ 5. Empty State Glass Panel ------ */
.glass-empty-state {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow:
    0 8px 32px rgba(0, 0, 0, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.4);
}

.dark .glass-empty-state {
  background: rgba(0, 0, 0, 0.2);
  border-color: rgba(255, 255, 255, 0.06);
}

/* ------ 6. Pagination Micro Elements ------ */
.micro-glass {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  transform: rotateY(-1deg);
  will-change: transform;
  transition: transform 0.3s ease, background 0.2s ease, box-shadow 0.2s ease;
}

.dark .micro-glass {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.08);
}

.micro-glass:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
  background: rgba(255, 255, 255, 0.25);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.dark .micro-glass:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.08);
}

.micro-glass:active:not(:disabled) {
  transform: scale(0.95) translateY(1px);
}

/* Active page state */
.active-page {
  background: rgba(59, 130, 246, 0.2);
  border-color: rgba(59, 130, 246, 0.3);
  color: #ffffff;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

/* ------ 7. Staggered Entrance Animation ------ */
.stagger-enter-active {
  transition: all 0.4s ease;
  transition-delay: calc(var(--i, 0) * 60ms);
}

.stagger-leave-active {
  transition: all 0.3s ease;
}

.stagger-enter-from {
  opacity: 0;
  transform: translateY(16px) scale(0.98);
}

.stagger-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* ------ 8. Responsive & Accessibility Overrides ------ */
@media (max-width: 767px) {
  .glass-surface-card,
  .glass-surface-card:hover,
  .glass-surface-card:active,
  .micro-glass,
  .micro-glass:hover,
  .micro-glass:active {
    transform: none !important;
  }

  .glass-surface-card:hover {
    background: rgba(255, 255, 255, 0.3);
  }

  .dark .glass-surface-card:hover {
    background: rgba(255, 255, 255, 0.06);
  }

  .space-y-3 > * + * {
    margin-top: 0.75rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>