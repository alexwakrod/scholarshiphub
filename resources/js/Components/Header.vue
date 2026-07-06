<template>
  <!-- Floating Glass Header – hides on scroll down, shows on scroll up -->
  <header
    class="fixed left-4 right-4 z-40 flex items-center justify-between px-5 py-3 transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] glass-header-floating"
    :class="[
      headerVisible ? 'top-4 translate-y-0 opacity-100' : '-translate-y-full opacity-0 pointer-events-none',
      reduceMotion ? 'no-motion' : ''
    ]"
    role="banner"
  >
    <!-- Animated glass reflection overlay -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden rounded-2xl">
      <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-gray-400/20 dark:via-white/10 to-transparent"></div>
      <div class="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-gray-400/10 dark:via-white/5 to-transparent"></div>
      <div class="absolute -bottom-1/2 right-1/4 w-48 h-24 bg-gradient-to-l from-blue-500/5 to-indigo-500/5 blur-3xl"></div>
    </div>

    <!-- Left Section: Toggle + Logo + Breadcrumb -->
    <div class="flex items-center gap-3">
      <!-- Sidebar Toggle Button -->
      <button
        @click="handleToggleSidebar"
        class="group relative w-9 h-9 rounded-xl glass-toggle-btn transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] hover:scale-105 active:scale-95 focus-visible:ring-2 focus-visible:ring-blue-400 flex items-center justify-center text-gray-500 dark:text-white/40 hover:text-gray-900 dark:hover:text-white"
        :aria-label="expanded ? 'Collapse sidebar' : 'Expand sidebar'"
      >
        <svg
          class="w-4.5 h-4.5 transition-transform duration-500 ease-[cubic-bezier(0.4,0,0.2,1)]"
          :class="{ 'rotate-180': expanded }"
          fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
        </svg>
      </button>

      <!-- Logo -->
      <Link href="/dashboard" class="flex items-center gap-2.5 group">
        <div class="relative w-8 h-8">
          <div class="absolute inset-0 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 blur-xl opacity-50 group-hover:opacity-70 transition-opacity"></div>
          <div class="relative w-full h-full rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-[0_0_40px_rgba(59,130,246,0.3)]">
            <span class="text-white font-bold text-sm">S</span>
          </div>
          <div class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-green-400 animate-pulse shadow-[0_0_12px_rgba(74,222,128,0.6)]"></div>
        </div>
        <span class="text-lg font-bold bg-gradient-to-r from-blue-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent hidden sm:inline">
          ScholarshipHub
        </span>
      </Link>

      <!-- Breadcrumb / Page Title -->
      <div class="hidden md:flex items-center gap-2.5 ml-2">
        <span class="text-sm text-gray-400 dark:text-white/30">/</span>
        <span class="text-sm font-medium text-gray-700 dark:text-white/80 truncate">{{ pageTitle }}</span>
      </div>
    </div>

    <!-- Right Section: Actions -->
    <div class="flex items-center gap-1.5 sm:gap-2">
      <!-- Notification Bell -->
      <NotificationBell />

      <!-- Theme Toggle -->
      <ThemeToggle />

      <!-- User Menu Dropdown -->
      <div class="relative" ref="userMenuRef">
        <button
          @click="profileMenuOpen = !profileMenuOpen"
          class="relative w-9 h-9 rounded-full bg-gradient-to-br from-white/10 to-white/5 dark:from-white/5 dark:to-white/0 ring-1 ring-gray-300/50 dark:ring-white/10 hover:ring-gray-400 dark:hover:ring-white/20 transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] hover:scale-105 active:scale-95 focus-visible:ring-2 focus-visible:ring-blue-400 group"
        >
          <img
            v-if="userAvatar"
            :src="userAvatar"
            alt="Avatar"
            class="absolute inset-0 w-full h-full object-cover rounded-full"
          />
          <svg v-else class="w-4 h-4 text-gray-500 dark:text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <div class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-green-500 border-2 border-white/20 dark:border-gray-900/20 shadow-[0_0_12px_rgba(74,222,128,0.4)]">
            <div class="absolute inset-0 rounded-full bg-green-400 animate-ping opacity-75"></div>
          </div>
        </button>

        <!-- Dropdown menu -->
            <Teleport to="body">
      <Transition name="menu-fade">
        <div
          v-if="profileMenuOpen"
          class="fixed z-50 w-56 glass-dropdown-header rounded-xl border border-gray-200 dark:border-white/10 shadow-xl"
          :style="dropdownStyle"
        >
          <div class="px-4 py-3 border-b border-gray-200 dark:border-white/5">
            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ userName }}</p>
            <p class="text-xs text-gray-500 dark:text-white/40 truncate">{{ userEmail }}</p>
          </div>
          <div class="py-1">
            <Link
              :href="route('profile.edit')"
              @click="profileMenuOpen = false"
              class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-white/80 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
            >
              <svg class="w-4 h-4 text-gray-500 dark:text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Profile
            </Link>
            <Link
              :href="route('dashboard')"
              @click="profileMenuOpen = false"
              class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-white/80 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
            >
              <svg class="w-4 h-4 text-gray-500 dark:text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              Dashboard
            </Link>
            <Link
              :href="route('landing')"
              @click="profileMenuOpen = false"
              class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-white/80 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
            >
              <svg class="w-4 h-4 text-gray-500 dark:text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              Landing
            </Link>
            <Link
              :href="route('settings')"
              @click="profileMenuOpen = false"
              class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-white/80 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
            >
              <svg class="w-4 h-4 text-gray-500 dark:text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Settings
            </Link>
            <Link
              v-if="isAdmin"
              :href="route('admin.dashboard')"
              @click="profileMenuOpen = false"
              class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-white/80 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
            >
              <svg class="w-4 h-4 text-gray-500 dark:text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              Admin Panel
            </Link>
          </div>
          <div class="border-t border-gray-200 dark:border-white/5 py-1">
            <Link
              :href="route('logout')"
              method="post"
              @click="profileMenuOpen = false"
              class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" />
              </svg>
              Sign out
            </Link>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NotificationBell from './NotificationBell.vue';
import ThemeToggle from './ThemeToggle.vue';

const props = defineProps({
  expanded: { type: Boolean, default: false },
});

const emit = defineEmits(['toggle-sidebar']);
const page = usePage();

const profileMenuOpen = ref(false);
const userMenuRef = ref(null);

// User data from shared auth
const userAvatar = computed(() => {
  const avatar = page.props.auth?.user?.avatar_url;
  if (!avatar) return null;
  if (avatar.startsWith('http')) return avatar;
  return '/storage/' + avatar;
});

const userName = computed(() => page.props.auth?.user?.name ?? 'User');
const userEmail = computed(() => page.props.auth?.user?.email ?? '');

const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');

const pageTitle = computed(() => {
  const url = page.url;
  const segments = url.split('/').filter(Boolean);
  if (segments.length === 0) return 'Dashboard';
  const last = segments[segments.length - 1];
  return last.charAt(0).toUpperCase() + last.slice(1).replace(/-/g, ' ');
});

const dropdownStyle = computed(() => {
  if (!userMenuRef.value) return { top: '0px', left: '0px' };
  const rect = userMenuRef.value.getBoundingClientRect();
  return {
    top: (rect.bottom + 8) + 'px',
    right: (window.innerWidth - rect.right) + 'px',
  };
});

function handleToggleSidebar() {
  emit('toggle-sidebar');
}

// Close dropdown on outside click
function handleClickOutside(event) {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    profileMenuOpen.value = false;
  }
}

// ========== Scroll-aware hide/show ==========
const headerVisible = ref(true);
const lastScrollY = ref(0);
const SCROLL_THRESHOLD = 50; // minimum scroll amount before toggling

function handleScroll() {
  const currentScrollY = window.scrollY;
  if (Math.abs(currentScrollY - lastScrollY.value) < SCROLL_THRESHOLD) return;

  if (currentScrollY > lastScrollY.value && currentScrollY > 100) {
    // Scrolling down
    headerVisible.value = false;
  } else {
    // Scrolling up
    headerVisible.value = true;
  }
  lastScrollY.value = currentScrollY;
}

// Accessibility: reduce motion
const reduceMotion = ref(false);
onMounted(() => {
  const mq = window.matchMedia('(prefers-reduced-motion: reduce)');
  reduceMotion.value = mq.matches;
  const handler = (e) => { reduceMotion.value = e.matches; };
  mq.addEventListener('change', handler);

  document.addEventListener('click', handleClickOutside);
  window.addEventListener('scroll', handleScroll, { passive: true });

  onBeforeUnmount(() => {
    mq.removeEventListener('change', handler);
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('scroll', handleScroll);
  });
});
</script>

<style scoped>
/* ============================================
   FLOATING GLASS HEADER – THEME‑AWARE
   ============================================ */

/* The floating header bubble */
.glass-header-floating {
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  border-radius: 1.5rem;
}
.dark .glass-header-floating {
  background: rgba(0, 0, 0, 0.4);
  border-color: rgba(255, 255, 255, 0.1);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

/* Toggle button */
.glass-toggle-btn {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.dark .glass-toggle-btn {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.06);
}
.glass-toggle-btn:hover {
  background: rgba(255, 255, 255, 0.4);
  border-color: rgba(0, 0, 0, 0.15);
}
.dark .glass-toggle-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.15);
}

/* Action buttons (Tour, etc.) */
.glass-surface-btn {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.dark .glass-surface-btn {
  background: rgba(255, 255, 255, 0.03);
  border-color: rgba(255, 255, 255, 0.06);
}
.glass-elevated-btn {
  background: rgba(255, 255, 255, 0.4);
  border-color: rgba(0, 0, 0, 0.12);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}
.dark .glass-elevated-btn {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.15);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
}

/* Dropdown glass panel */
.glass-dropdown-header {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-color: rgba(0, 0, 0, 0.1);
  box-shadow: 0 24px 80px rgba(0, 0, 0, 0.2);
}
.dark .glass-dropdown-header {
  background: rgba(0, 0, 0, 0.85);
  border-color: rgba(255, 255, 255, 0.1);
  box-shadow: 0 24px 80px rgba(0, 0, 0, 0.5);
}

/* Menu fade animation */
.menu-fade-enter-active,
.menu-fade-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.menu-fade-enter-from,
.menu-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

/* Pulse for online dot */
@keyframes pulse {
  0%, 100% { opacity: 0.75; transform: scale(1); }
  50% { opacity: 0; transform: scale(1.5); }
}
.animate-ping { animation: pulse 2s ease-in-out infinite; }

/* Reduced motion – disables all transitions and animations */
@media (prefers-reduced-motion: reduce) {
  .glass-header-floating,
  .glass-toggle-btn,
  .glass-surface-btn,
  .glass-elevated-btn,
  .glass-dropdown-header {
    transition: none !important;
    transform: none !important;
    animation: none !important;
  }
  .glass-header-floating,
  .glass-toggle-btn,
  .glass-surface-btn,
  .glass-elevated-btn {
    &:hover {
      transform: none !important;
      scale: 1 !important;
    }
    &:active {
      transform: none !important;
      scale: 1 !important;
    }
  }
}
</style>