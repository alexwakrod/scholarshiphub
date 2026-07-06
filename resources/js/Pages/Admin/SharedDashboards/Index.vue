<template>
  <AppLayout>
    <div class="p-6 max-w-4xl mx-auto space-y-6">
      <h1 class="text-2xl font-bold text-white">Dashboard Sharing</h1>

      <!-- Create share form -->
      <GlassCard variant="elevated" class="p-6 space-y-4">
        <h2 class="text-lg font-semibold text-white">Create Share Link</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <GlassInput v-model="form.name" placeholder="Share name" />
          <GlassInput v-model="form.expires_at" type="date" placeholder="Expires (optional)" />
        </div>
        <div>
          <label class="block text-sm text-white/70 mb-2">Select widgets to include</label>
          <div class="flex flex-wrap gap-3">
            <GlassCheckbox
              v-for="widget in allWidgets"
              :key="widget.id"
              :modelValue="form.widget_ids.includes(widget.id)"
              @update:modelValue="val => toggleWidget(widget.id, val)"
              :label="widget.label"
            />
          </div>
        </div>
        <button @click="createShare" :disabled="!form.name || !form.widget_ids.length" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50">
          Create
        </button>
      </GlassCard>

      <!-- Existing shares list -->
      <GlassCard variant="elevated" class="p-6 space-y-3">
        <h2 class="text-lg font-semibold text-white mb-2">Active Shares</h2>
        <div v-if="shares.length === 0" class="text-white/50 text-sm">No shares created yet.</div>
        <div v-else v-for="share in shares" :key="share.id" class="flex items-center justify-between p-3 rounded-lg bg-white/5">
          <div>
            <p class="text-white text-sm">{{ share.name }}</p>
            <p class="text-xs text-white/50">
              {{ share.token }} &middot; {{ share.expires_at ? 'Expires ' + share.expires_at : 'No expiry' }}
            </p>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="copyLink(share.token)"
              class="text-xs bg-indigo-600 px-2 py-1 rounded text-white hover:bg-indigo-700"
            >
              Copy Link
            </button>
            <button
              @click="toggleActive(share)"
              class="text-xs px-2 py-1 rounded"
              :class="share.is_active ? 'bg-yellow-600 hover:bg-yellow-700 text-white' : 'bg-green-600 hover:bg-green-700 text-white'"
            >
              {{ share.is_active ? 'Disable' : 'Enable' }}
            </button>
            <button @click="deleteShare(share.id)" class="text-xs bg-red-600/60 px-2 py-1 rounded text-white hover:bg-red-600">
              Delete
            </button>
          </div>
        </div>
      </GlassCard>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassCheckbox from '@/Components/GlassCheckbox.vue';

const props = defineProps({
  shares: Array,
});

const form = reactive({
  name: '',
  widget_ids: [],
  expires_at: '',
});

const allWidgets = [
  { id: 'stats', label: 'Key Metrics' },
  { id: 'registrations_chart', label: 'Registrations Chart' },
  { id: 'reviews_chart', label: 'AI Reviews Chart' },
  { id: 'score_distribution', label: 'Score Distribution' },
  { id: 'top_countries', label: 'Top Countries' },
  { id: 'avg_match_provider', label: 'Avg Match by Provider' },
];

function toggleWidget(id, include) {
  if (include) form.widget_ids.push(id);
  else form.widget_ids = form.widget_ids.filter(w => w !== id);
}

async function createShare() {
  try {
    await axios.post('/admin/shared-dashboards', form);
    router.reload();
  } catch (e) {
    console.error('Create share error:', e);
  }
}

function copyLink(token) {
  const url = window.location.origin + '/dashboard/shared/' + token;
  navigator.clipboard.writeText(url).then(() => alert('Link copied!'));
}

async function toggleActive(share) {
  try {
    await axios.put(`/admin/shared-dashboards/${share.id}`, { is_active: !share.is_active });
    router.reload();
  } catch (e) {
    console.error('Toggle error:', e);
  }
}

async function deleteShare(id) {
  if (!confirm('Delete this share?')) return;
  try {
    await axios.delete(`/admin/shared-dashboards/${id}`);
    router.reload();
  } catch (e) {
    console.error('Delete error:', e);
  }
}
</script>