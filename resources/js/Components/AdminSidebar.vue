<template>
  <aside
    class="fixed top-1/2 -translate-y-1/2 z-[9999] flex flex-col transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)]"
    :class="[
      expanded ? 'w-64' : 'w-[72px]',
      mobileOpen ? 'left-4' : (mobile ? '-left-[calc(100%-1rem)]' : 'left-4'),
      'glass-sidebar-container'
    ]"
    role="navigation"
    aria-label="Admin navigation"
    @mouseenter="showTooltips = true"
    @mouseleave="showTooltips = false"
  >
    <!-- Top spacer -->
    <div class="flex-shrink-0 h-1"></div>

    <!-- Header (Admin) -->
    <div class="px-3 py-2 border-b border-gray-200 dark:border-white/5" v-if="expanded">
      <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-white/30">Administration</p>
    </div>
    <div v-else class="h-2"></div>

    <!-- Navigation Items (max 5 visible, rest scrollable with transparent scrollbar) -->
    <nav class="flex-1 overflow-y-auto admin-custom-scrollbar py-1 px-2 space-y-1" style="max-height: calc(7 * (2.5rem + 0.25rem));">
      <div
        v-for="(item, index) in adminNavItems"
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

    <!-- Bottom spacer -->
    <div class="flex-shrink-0 h-1"></div>
  </aside>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
  AcademicCapIcon,
  UsersIcon,
  ChartBarIcon,
  QuestionMarkCircleIcon,
  ArrowUpTrayIcon,
  StarIcon,
  TagIcon,
  ClipboardIcon,
  ClockIcon,
  FlagIcon,
  PencilIcon,
  DocumentTextIcon,
  BellIcon,
  ShareIcon,
  PresentationChartBarIcon,
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

const adminNavItems = [
  { route: '/admin/dashboard', label: 'Admin Dashboard', icon: PresentationChartBarIcon },
  { route: '/admin/scholarships', label: 'Manage Scholarships', icon: AcademicCapIcon },
  { route: '/admin/users', label: 'Users', icon: UsersIcon },
  { route: '/admin/faqs', label: 'FAQs', icon: QuestionMarkCircleIcon },
  { route: '/admin/import', label: 'Import', icon: ArrowUpTrayIcon },
  { route: '/admin/testimonials', label: 'Testimonials', icon: StarIcon },
  { route: '/admin/audit-logs', label: 'Audit Logs', icon: ClipboardIcon },
  { route: '/admin/scheduled-tasks', label: 'Scheduled Tasks', icon: ClockIcon },
  { route: '/admin/feature-flags', label: 'Feature Flags', icon: FlagIcon },
  { route: '/admin/file-manager', label: 'File Manager', icon: DocumentTextIcon },
  { route: '/admin/reports', label: 'Reports', icon: ChartBarIcon },
];

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
   PREMIUM GLASS ADMIN SIDEBAR – THEME‑AWARE
   ============================================ */

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

.label-fade-enter-active,
.label-fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.label-fade-enter-from,
.label-fade-leave-to {
  opacity: 0;
  transform: translateX(-4px);
}

.tooltip-fade-enter-active,
.tooltip-fade-leave-active {
  transition: opacity 0.2s ease;
}
.tooltip-fade-enter-from,
.tooltip-fade-leave-to {
  opacity: 0;
}

/* Transparent scrollbar with subtle hover */
.admin-custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: transparent transparent;
}
.admin-custom-scrollbar:hover {
  scrollbar-color: rgba(0, 0, 0, 0.15) transparent;
}
.dark .admin-custom-scrollbar:hover {
  scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
}
.admin-custom-scrollbar::-webkit-scrollbar { width: 4px; }
.admin-custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.admin-custom-scrollbar::-webkit-scrollbar-thumb { 
  background: transparent;
  border-radius: 999px;
}
.admin-custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.15);
}
.dark .admin-custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.15);
}

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