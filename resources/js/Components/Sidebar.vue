<template>
  <aside
    class="fixed top-1/2 -translate-y-1/2 z-30 flex flex-col transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)]"
    :class="[
      expanded ? 'w-64' : 'w-[72px]',
      mobileOpen ? 'left-4' : (mobile ? '-left-[calc(100%-1rem)]' : 'left-4'),
      'glass-sidebar-container'
    ]"
    role="navigation"
    aria-label="Main navigation"
    @mouseenter="showTooltips = true"
    @mouseleave="showTooltips = false"
  >
    <!-- Top spacer reduced -->
    <div class="flex-shrink-0 h-1"></div>

    <!-- Navigation Items -->
    <nav class="flex-1 overflow-y-auto custom-scrollbar py-1 px-2 space-y-1">
      <div
        v-for="(item, index) in filteredNavItems"
        :key="item.route"
        :style="{ transitionDelay: expanded ? `${index * 30}ms` : '0ms' }"
      >
        <Link
          :href="item.route"
          class="group relative flex items-center rounded-2xl transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] overflow-hidden nav-item-base"
          :class="[
            expanded ? 'h-10 px-3 gap-3' : 'h-10 w-10 mx-auto justify-center',
            $page.url.startsWith(item.route)
              ? 'nav-item-active'
              : 'nav-item-inactive'
          ]"
          @mouseenter="onItemMouseEnter($event, item)"
          @mouseleave="onItemMouseLeave"
          @touchstart="onItemTouchStart($event, item)"
          @touchend="onItemTouchEnd"
        >
          <!-- Icon -->
          <div class="relative flex items-center justify-center shrink-0 transition-transform duration-300 group-hover:scale-110">
            <div class="relative w-[22px] h-[22px] flex items-center justify-center">
              <component
                :is="item.icon"
                class="w-[20px] h-[20px] transition-colors duration-300"
                :class="[
                  $page.url.startsWith(item.route)
                    ? 'text-blue-500 dark:text-blue-400 drop-shadow-[0_0_6px_rgba(59,130,246,0.4)]'
                    : 'text-gray-500 dark:text-white/40 group-hover:text-gray-900 dark:group-hover:text-white/70',
                ]"
              />

              <!-- Notification badge -->
              <div
                v-if="item.hasBadge && unreadCount > 0"
                class="absolute -top-1 -right-1.5 min-w-[16px] h-[16px] rounded-full bg-gradient-to-r from-red-500 to-orange-500 flex items-center justify-center text-[8px] font-bold text-white shadow-[0_0_12px_rgba(239,68,68,0.4)] px-1 leading-none"
              >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
              </div>
            </div>
          </div>

          <!-- Label (expanded) -->
          <transition name="label-fade">
            <span
              v-if="expanded"
              class="flex-1 text-sm font-medium whitespace-nowrap truncate"
              :class="[
                $page.url.startsWith(item.route)
                  ? 'text-gray-900 dark:text-white'
                  : 'text-gray-600 dark:text-white/60 group-hover:text-gray-900 dark:group-hover:text-white/80',
              ]"
            >
              {{ item.label }}
            </span>
          </transition>

          <!-- Active dot (collapsed) -->
          <div
            v-if="!expanded && $page.url.startsWith(item.route)"
            class="absolute -right-1 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-blue-400 shadow-[0_0_10px_rgba(59,130,246,0.6)]"
          ></div>
        </Link>

        <!-- Premium Glass Tooltip (collapsed mode) -->
        <Teleport to="body">
          <transition name="tooltip-fade">
            <div
              v-if="!expanded && hoveredItem === item.route && showTooltips"
              class="fixed z-[60] px-3 py-1.5 rounded-xl glass-tooltip text-xs font-medium text-white whitespace-nowrap"
              :style="tooltipStyle"
            >
              <div class="glass-tooltip-arrow-left"></div>
              {{ item.label }}
            </div>
          </transition>
        </Teleport>
      </div>
    </nav>

    <!-- Admin Panel Quick Link (separated) -->
    <div v-if="isAdmin" class="px-2 pb-2 pt-1 border-t border-gray-200 dark:border-white/5">
      <Link
        :href="route('admin.dashboard')"
        class="group relative flex items-center rounded-2xl transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] overflow-hidden nav-item-base"
        :class="[
          expanded ? 'h-10 px-3 gap-3' : 'h-10 w-10 mx-auto justify-center',
          $page.url.startsWith('/admin')
            ? 'nav-item-active'
            : 'nav-item-inactive'
        ]"
        @mouseenter="onItemMouseEnter($event, { route: '/admin/dashboard', label: 'Admin Panel' })"
        @mouseleave="onItemMouseLeave"
        @touchstart="onItemTouchStart($event, { route: '/admin/dashboard', label: 'Admin Panel' })"
        @touchend="onItemTouchEnd"
      >
        <div class="relative flex items-center justify-center shrink-0 transition-transform duration-300 group-hover:scale-110">
          <div class="relative w-[22px] h-[22px] flex items-center justify-center">
            <svg
              class="w-[20px] h-[20px] transition-colors duration-300"
              :class="[
                $page.url.startsWith('/admin')
                  ? 'text-blue-500 dark:text-blue-400 drop-shadow-[0_0_6px_rgba(59,130,246,0.4)]'
                  : 'text-gray-500 dark:text-white/40 group-hover:text-gray-900 dark:group-hover:text-white/70',
              ]"
              fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
        </div>
        <transition name="label-fade">
          <span
            v-if="expanded"
            class="flex-1 text-sm font-medium whitespace-nowrap truncate"
            :class="[
              $page.url.startsWith('/admin')
                ? 'text-gray-900 dark:text-white'
                : 'text-gray-600 dark:text-white/60 group-hover:text-gray-900 dark:group-hover:text-white/80',
            ]"
          >
            Admin Panel
          </span>
        </transition>
      </Link>
    </div>

    <!-- Bottom spacer reduced -->
    <div class="flex-shrink-0 h-1"></div>
  </aside>
</template>

<script setup>
import { ref, computed, reactive, onMounted, onBeforeUnmount } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
  HomeIcon,
  AcademicCapIcon,
  DocumentTextIcon,
  ClipboardDocumentListIcon,
  BookmarkIcon,
  LightBulbIcon,
  BellIcon,
  UserIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  expanded: { type: Boolean, default: false },
  mobileOpen: { type: Boolean, default: false },
  mobile: { type: Boolean, default: false },
});

const emit = defineEmits(['toggle']);

const page = usePage();
const showTooltips = ref(false);
const hoveredItem = ref(null);
const tooltipStyle = reactive({ top: '0px', left: '0px' });

const userRole = computed(() => page.props.auth?.user?.role || 'student');
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');
const menuPreferences = computed(() => page.props.auth?.user?.menu_preferences || null);
const unreadCount = computed(() => page.props.unreadNotifications || 0);

const allNavItems = [
  { route: '/dashboard', label: 'Dashboard', icon: HomeIcon, roles: ['student', 'admin'] },
  { route: '/scholarships', label: 'Scholarships', icon: AcademicCapIcon, roles: ['student', 'admin'] },
  { route: '/documents', label: 'Documents', icon: DocumentTextIcon, roles: ['student', 'admin'] },
  { route: '/applications', label: 'Applications', icon: ClipboardDocumentListIcon, roles: ['student', 'admin'] },
  { route: '/bookmarks', label: 'Bookmarks', icon: BookmarkIcon, roles: ['student', 'admin'] },
  { route: '/pathway', label: 'Pathway', icon: LightBulbIcon, roles: ['student', 'admin'] },
  { route: '/notifications', label: 'Notifications', icon: BellIcon, roles: ['student', 'admin'], hasBadge: true },
  { route: '/profile', label: 'Profile', icon: UserIcon, roles: ['student', 'admin'] },
];

const filteredNavItems = computed(() => {
  let items = allNavItems.filter(item => item.roles.includes(userRole.value));
  if (menuPreferences.value && Array.isArray(menuPreferences.value) && menuPreferences.value.length > 0) {
    const prefsMap = {};
    menuPreferences.value.forEach(p => { prefsMap[p.route] = p; });
    items = items.filter(item => {
      const pref = prefsMap[item.route];
      return pref ? pref.visible !== false : true;
    });
    items.sort((a, b) => {
      const aIdx = menuPreferences.value.findIndex(p => p.route === a.route);
      const bIdx = menuPreferences.value.findIndex(p => p.route === b.route);
      if (aIdx === -1 && bIdx === -1) return 0;
      if (aIdx === -1) return 1;
      if (bIdx === -1) return -1;
      return aIdx - bIdx;
    });
  }
  return items;
});

function onItemMouseEnter(event, item) {
  hoveredItem.value = item.route;
  const el = event.currentTarget;
  const rect = el.getBoundingClientRect();
  tooltipStyle.top = (rect.top + rect.height / 2) + 'px';
  tooltipStyle.left = (rect.right + 12) + 'px';
}

function onItemMouseLeave() {
  hoveredItem.value = null;
}

function onItemTouchStart(event, item) {
  hoveredItem.value = item.route;
  const el = event.currentTarget;
  const rect = el.getBoundingClientRect();
  tooltipStyle.top = (rect.top + rect.height / 2) + 'px';
  tooltipStyle.left = (rect.right + 12) + 'px';
}

function onItemTouchEnd() {
  setTimeout(() => { hoveredItem.value = null; }, 1500);
}

const reduceMotion = ref(false);
onMounted(() => {
  const mq = window.matchMedia('(prefers-reduced-motion: reduce)');
  reduceMotion.value = mq.matches;
  const handler = (e) => { reduceMotion.value = e.matches; };
  mq.addEventListener('change', handler);
  onBeforeUnmount(() => mq.removeEventListener('change', handler));
});
</script>

<style scoped>
/* ============================================
   PREMIUM GLASS SIDEBAR – THEME‑AWARE
   ============================================ */

/* Sidebar container – floating glass card, centered vertically, auto height */
.glass-sidebar-container {
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  border-radius: 1.5rem;
  overflow: hidden;
  height: fit-content;
  max-height: calc(100vh - 4rem);
}
.dark .glass-sidebar-container {
  background: rgba(0, 0, 0, 0.4);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

/* Navigation item base – nested glass card */
.nav-item-base {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(0, 0, 0, 0.06);
}
.dark .nav-item-base {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.05);
}
.nav-item-base:hover {
  transform: translateY(-1px);
  background: rgba(255, 255, 255, 0.35);
  border-color: rgba(0, 0, 0, 0.12);
}
.dark .nav-item-base:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.12);
}
.nav-item-base:active {
  transform: scale(0.97);
  transition-duration: 0.15s;
}

/* Active state */
.nav-item-active {
  background: rgba(255, 255, 255, 0.5) !important;
  border-color: rgba(59, 130, 246, 0.3) !important;
  box-shadow: 0 4px 24px rgba(59, 130, 246, 0.12);
}
.dark .nav-item-active {
  background: rgba(255, 255, 255, 0.12) !important;
  border-color: rgba(59, 130, 246, 0.4) !important;
  box-shadow: 0 4px 24px rgba(59, 130, 246, 0.2);
}

/* Premium tooltip */
.glass-tooltip {
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  transform: translateY(-50%);
}

.glass-tooltip-arrow-left {
  position: absolute;
  left: -4px;
  top: 50%;
  transform: translateY(-50%) rotate(45deg);
  width: 8px;
  height: 8px;
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(20px);
  border-left: 1px solid rgba(255, 255, 255, 0.15);
  border-bottom: 1px solid rgba(255, 255, 255, 0.15);
}

/* Label fade transition */
.label-fade-enter-active,
.label-fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.label-fade-enter-from,
.label-fade-leave-to {
  opacity: 0;
  transform: translateX(-4px);
}

/* Tooltip fade */
.tooltip-fade-enter-active,
.tooltip-fade-leave-active {
  transition: opacity 0.2s ease;
}
.tooltip-fade-enter-from,
.tooltip-fade-leave-to {
  opacity: 0;
}

/* Custom scrollbar */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(0, 0, 0, 0.15) transparent;
}
.dark .custom-scrollbar {
  scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
}
.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.15);
  border-radius: 999px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.15);
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.3);
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .glass-sidebar-container,
  .nav-item-base,
  .nav-item-base:hover,
  .label-fade-enter-active,
  .label-fade-leave-active,
  .tooltip-fade-enter-active,
  .tooltip-fade-leave-active {
    transition: none !important;
    transform: none !important;
    animation: none !important;
  }
  .nav-item-base:active {
    transform: none !important;
  }
}
</style>