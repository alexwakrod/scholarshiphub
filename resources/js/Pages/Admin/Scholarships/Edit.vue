<template>
  <AppLayout>
    <div class="p-4 md:p-6 max-w-4xl mx-auto space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight">
          Edit <span class="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Scholarship</span>
        </h1>
        <div class="flex items-center gap-4">
          <button
            @click="diffOpen = true"
            class="text-sm text-gray-500 dark:text-white/40 hover:text-blue-600 dark:hover:text-blue-400 transition-colors underline-offset-2 hover:underline"
          >
            View Changes
          </button>
          <Link
            :href="route('admin.scholarships.index')"
            class="text-sm text-gray-500 dark:text-white/40 hover:text-blue-600 dark:hover:text-blue-400 transition-colors underline-offset-2 hover:underline"
          >
            Back to list
          </Link>
        </div>
      </div>

      <!-- Elevated Glass Form Card -->
      <GlassCard variant="elevated" class="p-6 md:p-8 rounded-3xl">
        <form @submit.prevent="save" class="space-y-6">
          <!-- Title -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Title</label>
            <GlassInput v-model="form.title" :error="form.errors.title" placeholder="Scholarship title" />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Description</label>
            <GlassTextarea
              v-model="form.description"
              rows="6"
              :error="form.errors.description"
              placeholder="Full scholarship description..."
              class="content-glass"
            />
          </div>

          <!-- Provider / Country -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Provider</label>
              <GlassInput v-model="form.provider" :error="form.errors.provider" placeholder="e.g. DAAD, Chevening" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Country</label>
              <GlassInput v-model="form.country" :error="form.errors.country" placeholder="e.g. Germany, United Kingdom" />
            </div>
          </div>

          <!-- Amount / Deadline -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Amount (USD)</label>
              <GlassNumberInput v-model="form.amount" :min="0" :step="0.01" placeholder="0.00" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Deadline</label>
              <GlassInput v-model="form.deadline" type="datetime-local" :error="form.errors.deadline" />
            </div>
          </div>

          <!-- Source URL -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Source URL</label>
            <GlassInput v-model="form.source_url" placeholder="https://..." />
          </div>

          <!-- Status -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Status</label>
              <GlassSelect
                v-model="form.status"
                :options="[{value:'active',label:'Active'},{value:'expired',label:'Expired'}]"
                placeholder="Select status"
              />
            </div>
          </div>

          <!-- Parsed Requirements -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Parsed Requirements (JSON)</label>
            <GlassTextarea
              v-model="form.parsed_requirements_text"
              rows="4"
              placeholder='{"academic_level":"undergraduate",...}'
              class="content-glass"
            />
            <p v-if="form.errors.parsed_requirements" class="text-red-400 text-xs mt-2 flex items-center gap-1.5 animate-shake">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ form.errors.parsed_requirements }}
            </p>
          </div>

          <!-- Actions -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pt-6 border-t border-gray-200 dark:border-white/5">
            <button
              type="button"
              @click="triggerParse"
              class="glass-btn-reparse group relative px-5 py-3 rounded-2xl bg-amber-600 text-white text-sm font-semibold overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(245,158,11,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-amber-400"
            >
              <span class="relative z-10 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Re-parse with AI
              </span>
              <div class="absolute inset-0 bg-gradient-to-r from-amber-400 to-yellow-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="glass-btn-primary group relative px-8 py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold overflow-hidden transition-all duration-300 hover:shadow-[0_0_60px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-blue-400"
            >
              <span class="relative z-10 flex items-center justify-center gap-2">
                <span v-if="form.processing" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
                <span v-else>Update Scholarship</span>
              </span>
              <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </button>
          </div>
        </form>
      </GlassCard>

      <!-- Diff Viewer Modal (Ultra-Hero) -->
      <DiffViewerModal
        v-model="diffOpen"
        :scholarshipId="scholarship.id"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassTextarea from '@/Components/GlassTextarea.vue';
import GlassNumberInput from '@/Components/GlassNumberInput.vue';
import GlassSelect from '@/Components/GlassSelect.vue';
import DiffViewerModal from '@/Components/DiffViewerModal.vue';

const props = defineProps({
  scholarship: Object,
});

const diffOpen = ref(false);

const form = useForm({
  title: props.scholarship?.title || '',
  description: props.scholarship?.description || '',
  provider: props.scholarship?.provider || '',
  country: props.scholarship?.country || '',
  amount: props.scholarship?.amount || '',
  deadline: props.scholarship?.deadline ? new Date(props.scholarship.deadline).toISOString().slice(0, 16) : '',
  source_url: props.scholarship?.source_url || '',
  status: props.scholarship?.status || 'active',
  parsed_requirements_text: props.scholarship?.parsed_requirements
    ? JSON.stringify(props.scholarship.parsed_requirements, null, 2)
    : '',
});

function save() {
  const data = { ...form };
  try {
    data.parsed_requirements = data.parsed_requirements_text
      ? JSON.parse(data.parsed_requirements_text)
      : null;
  } catch {
    form.errors.parsed_requirements = 'Invalid JSON.';
    return;
  }
  delete data.parsed_requirements_text;

  form.put(route('admin.scholarships.update', props.scholarship.id), {
    ...data,
    onSuccess: () => {},
  });
}

async function triggerParse() {
  try {
    await axios.post(route('admin.scholarships.parse', props.scholarship.id));
    alert('AI parsing started.');
  } catch (e) {
    console.error('Parse trigger error:', e);
    alert('Failed to trigger parsing.');
  }
}
</script>

<style scoped>
/* ============================================================
   GLASS EDIT SCHOLARSHIP – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

/* 3D Button physics */
.glass-btn-primary,
.glass-btn-reparse {
  transform: rotateY(-2deg);
  will-change: transform;
}
.glass-btn-primary:hover:not(:disabled),
.glass-btn-reparse:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}
.glass-btn-primary:active:not(:disabled),
.glass-btn-reparse:active:not(:disabled) {
  transform: scale(0.95) translateY(1px);
  transition-duration: 0.1s;
}

/* Localized glow */
.localized-glow {
  filter: blur(20px);
  opacity: 0;
  transition: opacity 0.4s ease;
}
.group:hover .localized-glow {
  opacity: 0.3;
}

/* Reduced blur for large text blocks (content glass) */
.content-glass {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

/* Error shake animation */
.animate-shake {
  animation: shake 0.4s ease-in-out;
}
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}

/* Mobile & accessibility overrides */
@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .glass-btn-primary,
  .glass-btn-reparse,
  .glass-btn-primary:hover,
  .glass-btn-reparse:hover,
  .glass-btn-primary:active,
  .glass-btn-reparse:active {
    transform: none !important;
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