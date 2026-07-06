<template>
  <div class="min-h-screen bg-gray-50/50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 flex flex-col">
    <!-- Impersonation Banner -->
    <ImpersonationBanner />

    <!-- Offline Banner -->
    <OfflineBanner />

    <!-- Conditional Sidebars -->
    <!-- Public Sidebar (hidden on admin pages) -->
    <Sidebar
      v-if="!isAdminPage"
      :expanded="sidebarExpanded"
      :mobile-open="sidebarMobileOpen"
      :mobile="isMobile"
      @toggle="toggleSidebar"
    />

    <!-- Admin Sidebar (only on admin pages) -->
    <AdminSidebar
      v-else
      :expanded="sidebarExpanded"
      :mobile-open="sidebarMobileOpen"
      :mobile="isMobile"
      @toggle="toggleSidebar"
    />

    <!-- Mobile overlay -->
    <Transition name="fade">
      <div
        v-if="sidebarMobileOpen && isMobile"
        class="fixed inset-0 z-30 bg-black/40 backdrop-blur-md transition-all duration-300 lg:hidden"
        @click="sidebarMobileOpen = false"
      ></div>
    </Transition>

    <!-- Floating Header (independent of content flow) -->
    <Header
      :expanded="sidebarExpanded"
      @toggle-sidebar="toggleSidebar"
    />

    <!-- Main content area – clear floats with dynamic padding -->
    <div
      class="flex-1 flex flex-col transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)]"
      :class="mainContentClass"
    >
      <!-- Page content with enter transition -->
      <main class="flex-1 overflow-y-auto">
        <Transition name="page-fade" mode="out-in">
          <div :key="$page.url">
            <slot />
          </div>
        </Transition>
      </main>
    </div>

    <!-- Toast container -->
    <GlassToast ref="toastRef" />

    <!-- Keyboard shortcuts overlay -->
    <KeyboardShortcutsOverlay
      :visible="showShortcutsOverlay"
      :shortcuts="shortcutsList"
      @close="showShortcutsOverlay = false"
    />

    <!-- Scroll to top button -->
    <ScrollToTop />

    <!-- Background job progress notifications -->
    <JobProgressNotification />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, provide } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useGlobalShortcuts, shortcuts as shortcutsList } from '@/composables/useGlobalShortcuts';
import OfflineBanner from '@/Components/OfflineBanner.vue';
import ImpersonationBanner from '@/Components/ImpersonationBanner.vue';
import Sidebar from '@/Components/Sidebar.vue';
import AdminSidebar from '@/Components/AdminSidebar.vue';
import Header from '@/Components/Header.vue';
import GlassToast from '@/Components/GlassToast.vue';
import KeyboardShortcutsOverlay from '@/Components/KeyboardShortcutsOverlay.vue';
import ScrollToTop from '@/Components/ScrollToTop.vue';
import JobProgressNotification from '@/Components/JobProgressNotification.vue';

const page = usePage();

const { showOverlay: showShortcutsOverlay, setSaveAction, setCreateAction, setSearchAction } = useGlobalShortcuts();

// Sidebar state
const sidebarExpanded = ref(false); // starts collapsed
const sidebarMobileOpen = ref(false);
const isMobile = ref(window.innerWidth < 768);

// Detect admin routes
const isAdminPage = computed(() => page.url.startsWith('/admin'));

function handleResize() {
  try {
    isMobile.value = window.innerWidth < 768;
    if (!isMobile.value) {
      sidebarMobileOpen.value = false;
    }
  } catch (e) {
    console.error('[AppLayout] handleResize error:', e);
  }
}

function toggleSidebar() {
  try {
    if (isMobile.value) {
      sidebarMobileOpen.value = !sidebarMobileOpen.value;
    } else {
      sidebarExpanded.value = !sidebarExpanded.value;
    }
  } catch (e) {
    console.error('[AppLayout] toggleSidebar error:', e);
  }
}

// Dynamic padding to clear floating sidebar and header
const mainContentClass = computed(() => {
  try {
    const topPadding = 'pt-20'; // clear floating header (approx height)
    if (isMobile.value) {
      return `p-4 ${topPadding}`;
    }
    // Desktop: padding left depends on sidebar width and type
    const sidebarWidth = sidebarExpanded.value ? '256px' : '72px'; // 64 = 256px, 72px
    const sidebarLeft = isAdminPage.value ? '20' : '4'; // admin sidebar is at left-20, public at left-4
    const leftPadding = parseInt(sidebarLeft) + parseInt(sidebarWidth) + 16; // approximate total left offset
    return `p-6 ${topPadding} pl-${leftPadding}`;
  } catch (e) {
    return 'p-4 pt-20';
  }
});

// Provide methods so child pages can register shortcuts
provide('registerSaveAction', (fn) => setSaveAction(fn));
provide('registerCreateAction', (fn) => setCreateAction(fn));
provide('registerSearchAction', (fn) => setSearchAction(fn));

// Toast
const toastRef = ref(null);
provide('showToast', (message, type = 'info') => {
  try {
    toastRef.value?.addToast(message, type);
  } catch (e) {
    console.error('[AppLayout] showToast error:', e);
  }
});

// Reduced motion detection
const reduceMotion = ref(false);
let motionMq = null;

onMounted(() => {
  try {
    window.addEventListener('resize', handleResize);
    handleResize();

    motionMq = window.matchMedia('(prefers-reduced-motion: reduce)');
    reduceMotion.value = motionMq.matches;
    const motionHandler = (e) => {
      reduceMotion.value = e.matches;
    };
    motionMq.addEventListener('change', motionHandler);
    onBeforeUnmount(() => {
      motionMq.removeEventListener('change', motionHandler);
    });
  } catch (e) {
    console.error('[AppLayout] onMounted error:', e);
  }
});

onBeforeUnmount(() => {
  try {
    window.removeEventListener('resize', handleResize);
  } catch (e) {
    console.error('[AppLayout] onBeforeUnmount error:', e);
  }
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.page-fade-enter-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.page-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.page-fade-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.page-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>