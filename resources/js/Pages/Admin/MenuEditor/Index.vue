<template>
  <AppLayout>
    <div class="p-6 max-w-2xl">
      <h1 class="text-2xl font-bold text-white mb-6">Menu Customization</h1>

      <GlassCard variant="elevated" class="p-6">
        <p class="text-sm text-white/60 mb-4">Drag items to reorder the sidebar menu. Toggle visibility for each item.</p>

        <draggable
          v-model="localItems"
          item-key="route"
          handle=".drag-handle"
          @end="savePreferences"
          animation="200"
          ghost-class="opacity-50"
        >
          <template #item="{ element }">
            <div class="flex items-center justify-between p-3 rounded-lg bg-white/5 mb-2">
              <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-white/40 drag-handle cursor-grab" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                </svg>
                <span class="text-white text-sm">{{ element.label }}</span>
              </div>
              <GlassToggle :model-value="element.visible" @update:model-value="(val) => { element.visible = val; savePreferences(); }" />
            </div>
          </template>
        </draggable>

        <div class="mt-6 flex justify-end">
          <button
            @click="resetDefaults"
            class="px-4 py-2 rounded-lg bg-white/10 text-white text-sm hover:bg-white/20 transition-colors"
          >
            Reset to defaults
          </button>
        </div>
      </GlassCard>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import draggable from 'vuedraggable';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import GlassToggle from '@/Components/GlassToggle.vue';

const defaultMenu = [
  { route: '/dashboard', label: 'Dashboard', icon: 'HomeIcon', visible: true },
  { route: '/scholarships', label: 'Scholarships', icon: 'AcademicCapIcon', visible: true },
  { route: '/documents', label: 'Documents', icon: 'DocumentTextIcon', visible: true },
  { route: '/applications', label: 'Applications', icon: 'ClipboardDocumentListIcon', visible: true },
  { route: '/bookmarks', label: 'Bookmarks', icon: 'BookmarkIcon', visible: true },
  { route: '/pathway', label: 'Pathway', icon: 'LightBulbIcon', visible: true },
  { route: '/notifications', label: 'Notifications', icon: 'BellIcon', visible: true },
  { route: '/profile', label: 'Profile', icon: 'UserIcon', visible: true },
  { route: '/admin/scholarships', label: 'Admin: Scholarships', icon: 'CogIcon', visible: true },
  { route: '/admin/users', label: 'Admin: Users', icon: 'UsersIcon', visible: true },
  { route: '/admin/analytics', label: 'Admin: Analytics', icon: 'ChartBarIcon', visible: true },
  { route: '/admin/faqs', label: 'Admin: FAQs', icon: 'QuestionMarkCircleIcon', visible: true },
  { route: '/admin/import', label: 'Admin: Import', icon: 'ArrowUpTrayIcon', visible: true },
  { route: '/admin/testimonials', label: 'Admin: Testimonials', icon: 'StarIcon', visible: true },
  { route: '/admin/categories', label: 'Admin: Categories', icon: 'TagIcon', visible: true },
  { route: '/admin/audit-logs', label: 'Admin: Audit Logs', icon: 'ClipboardIcon', visible: true },
  { route: '/admin/scheduled-tasks', label: 'Admin: Tasks', icon: 'ClockIcon', visible: true },
  { route: '/admin/feature-flags', label: 'Admin: Feature Flags', icon: 'FlagIcon', visible: true },
  { route: '/admin/menu-editor', label: 'Admin: Menu Editor', icon: 'PencilIcon', visible: true },
];

const localItems = ref([]);

onMounted(async () => {
  try {
    const { data } = await axios.get('/admin/menu/preferences');
    if (data && data.length) {
      localItems.value = data;
    } else {
      localItems.value = [...defaultMenu];
    }
  } catch (e) {
    localItems.value = [...defaultMenu];
  }
});

async function savePreferences() {
  try {
    await axios.put('/admin/menu/preferences', { items: localItems.value });
  } catch (e) {
    console.error('Save menu preferences failed:', e);
  }
}

async function resetDefaults() {
  localItems.value = [...defaultMenu];
  await savePreferences();
}
</script>