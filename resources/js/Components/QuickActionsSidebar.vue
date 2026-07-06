<template>
  <div class="fixed top-0 right-0 h-full z-40 flex">
    <button
      @click="open = !open"
      class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-full bg-gray-200 dark:bg-white/10 backdrop-blur-sm rounded-l-xl p-2 text-gray-500 dark:text-white/70 hover:text-gray-900 dark:hover:text-white"
      title="Quick actions"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
      </svg>
    </button>

    <transition name="slide-right">
      <div
        v-if="open"
        class="w-80 glass-overlay border-l border-gray-200 dark:border-white/10 p-4 space-y-4 overflow-y-auto custom-scrollbar"
      >
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
          <button @click="open = false" class="text-gray-500 dark:text-white/50 hover:text-gray-900 dark:hover:text-white">&times;</button>
        </div>

        <nav class="space-y-1">
          <Link
            v-for="action in actions"
            :key="action.label"
            :href="action.href"
            class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-sm text-gray-700 dark:text-white/80 hover:text-gray-900 dark:hover:text-white transition-colors"
          >
            <component :is="action.icon" class="w-5 h-5 text-gray-500 dark:text-white/50" />
            <span>{{ action.label }}</span>
          </Link>
        </nav>

        <div class="border-t border-gray-200 dark:border-white/10 pt-4">
          <h4 class="text-xs text-gray-500 dark:text-white/50 uppercase mb-2">System</h4>
          <button
            @click="toggleFullScreen"
            class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-sm text-gray-700 dark:text-white/80"
          >
            <svg class="w-5 h-5 text-gray-500 dark:text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
            </svg>
            <span>Full Screen</span>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
  AcademicCapIcon, DocumentTextIcon, ClipboardDocumentListIcon,
  UserIcon, Cog6ToothIcon, ChartBarIcon, ArrowPathIcon,
} from '@heroicons/vue/24/outline';

const open = ref(false);

const actions = [
  { label: 'View Scholarships', href: '/scholarships', icon: AcademicCapIcon },
  { label: 'Manage Documents', href: '/documents', icon: DocumentTextIcon },
  { label: 'Applications', href: '/applications', icon: ClipboardDocumentListIcon },
  { label: 'Profile Settings', href: '/profile', icon: UserIcon },
  { label: 'Admin Settings', href: '/admin/scholarships', icon: Cog6ToothIcon },
  { label: 'Analytics', href: '/admin/analytics', icon: ChartBarIcon },
];

function toggleFullScreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
  } else {
    if (document.exitFullscreen) document.exitFullscreen();
  }
}
</script>

<style scoped>
.slide-right-enter-active, .slide-right-leave-active { transition: transform 0.3s ease; }
.slide-right-enter-from, .slide-right-leave-to { transform: translateX(100%); }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 2px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); }
</style>