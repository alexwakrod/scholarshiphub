<template>
  <AppLayout>
    <div class="relative min-h-screen overflow-hidden bg-gray-100 dark:bg-gray-950 p-4 md:p-6">
      <!-- Animated Background Particles (Surface layer) -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/4 right-1/4 w-96 h-96 rounded-full bg-blue-600/5 dark:bg-blue-600/10 blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 left-1/4 w-[500px] h-[500px] rounded-full bg-indigo-600/5 dark:bg-indigo-600/10 blur-3xl animate-pulse-slow" style="animation-delay: 1s"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-purple-600/3 dark:bg-purple-600/5 blur-3xl"></div>
        <div class="absolute top-20 right-10 w-3 h-3 rounded-full bg-blue-400/20 dark:bg-blue-400/30 animate-float"></div>
        <div class="absolute bottom-40 right-1/4 w-4 h-4 rounded-full bg-indigo-400/20 dark:bg-indigo-400/30 animate-float" style="animation-delay: 0.7s"></div>
        <div class="absolute top-1/3 left-10 w-2 h-2 rounded-full bg-purple-400/20 dark:bg-purple-400/30 animate-float" style="animation-delay: 1.4s"></div>
        <div class="absolute bottom-20 left-1/3 w-3 h-3 rounded-full bg-pink-400/20 dark:bg-pink-400/30 animate-float" style="animation-delay: 2s"></div>
      </div>

      <!-- Glass Reflection Overlay -->
      <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-gray-400/30 dark:via-white/30 to-transparent"></div>
        <div class="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-gray-400/10 dark:via-white/10 to-transparent"></div>
        <div class="absolute top-1/4 -right-1/4 w-1/2 h-64 bg-gradient-to-l from-blue-500/5 dark:from-blue-500/5 to-transparent blur-3xl rotate-12"></div>
      </div>

      <!-- Page Content -->
      <div class="relative max-w-7xl mx-auto space-y-8">
        <!-- Back link (embedded thread) -->
        <div class="flex items-center justify-between">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Document Details</h1>
          <Link :href="route('documents.index')" class="text-sm text-gray-500 dark:text-white/50 hover:text-blue-500 dark:hover:text-blue-400 hover:underline transition-colors duration-300">
            Back to documents
          </Link>
        </div>

        <!-- Document info card (Elevated glass slab) -->
        <GlassCard variant="elevated" class="p-6 md:p-8 rounded-3xl document-info">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
              <p class="text-sm text-gray-500 dark:text-white/40 tracking-widest uppercase">File Name</p>
              <p class="text-gray-900 dark:text-white font-medium truncate">{{ document.file_name }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-white/40 tracking-widest uppercase">Type</p>
              <p class="text-gray-900 dark:text-white capitalize">{{ formatFileType(document.file_type) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-white/40 tracking-widest uppercase">Version</p>
              <p class="text-gray-900 dark:text-white">{{ document.version_number }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-white/40 tracking-widest uppercase">Uploaded</p>
              <p class="text-gray-900 dark:text-white">{{ formatDate(document.created_at) }}</p>
            </div>
          </div>

          <!-- Physical glass divider -->
          <div class="glass-divider my-4"></div>

          <div class="flex flex-wrap items-center gap-3">
            <!-- Upload Again button (3D glass with glow) -->
            <button
              @click="showUploadAgain = true"
              class="glass-button glass-button-primary group relative px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-blue-400"
            >
              <span class="relative z-10 flex items-center gap-2">
                <svg class="w-4 h-4 icon-engraved" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Upload Again
              </span>
              <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
            </button>

            <!-- Request review button (3D glass with glow) -->
            <button
              v-if="!reviewData && reviewStatus !== 'processing' && reviewStatus !== 'pending'"
              @click="requestReview"
              :disabled="requesting"
              class="glass-button glass-button-secondary group relative px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(99,102,241,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-indigo-400"
              title="Request an AI review for this document"
            >
              <span class="relative z-10 flex items-center gap-2">
                <svg v-if="requesting" class="animate-spin w-4 h-4 icon-engraved" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span v-else>Request AI Review</span>
              </span>
              <div class="absolute inset-0 bg-gradient-to-r from-indigo-400 to-purple-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
            </button>
          </div>
        </GlassCard>

        <!-- Completed Review Section (Elevated glass slab with staggered entrance) -->
        <transition-group v-if="reviewData" name="staggered-list" appear>
          <GlassCard key="review-card" variant="elevated" class="p-6 md:p-8 rounded-3xl review-card">
            <!-- Scores grid with glass gems (three separate scores) -->
            <div class="grid grid-cols-3 gap-4 mb-6">
              <div class="score-card quality-score">
                <p class="text-2xl font-bold text-blue-400 drop-shadow-[0_0_8px_rgba(59,130,246,0.5)]">{{ reviewData.quality_score }}</p>
                <p class="text-xs text-gray-500 dark:text-white/50 uppercase tracking-wider">Quality</p>
              </div>
              <div class="score-card ats-score">
                <p class="text-2xl font-bold text-emerald-400 drop-shadow-[0_0_8px_rgba(16,185,129,0.5)]">{{ reviewData.ats_score }}</p>
                <p class="text-xs text-gray-500 dark:text-white/50 uppercase tracking-wider">ATS Score</p>
              </div>
              <div class="score-card competitiveness-score">
                <p class="text-2xl font-bold text-purple-400 drop-shadow-[0_0_8px_rgba(168,85,247,0.5)]">{{ reviewData.competitiveness_score }}</p>
                <p class="text-xs text-gray-500 dark:text-white/50 uppercase tracking-wider">Competitiveness</p>
              </div>
            </div>

            <!-- Strengths -->
            <div v-if="reviewData.strengths?.length" class="mb-4">
              <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Strengths</h3>
              <ul class="list-disc list-inside text-sm text-gray-700 dark:text-white/70 space-y-1">
                <li v-for="(s, i) in reviewData.strengths" :key="'s'+i">{{ s }}</li>
              </ul>
            </div>

            <!-- Weaknesses -->
            <div v-if="reviewData.weaknesses?.length" class="mb-4">
              <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Weaknesses</h3>
              <ul class="list-disc list-inside text-sm text-gray-700 dark:text-white/70 space-y-1">
                <li v-for="(w, i) in reviewData.weaknesses" :key="'w'+i">{{ w }}</li>
              </ul>
            </div>

            <!-- Suggestions with glass checkboxes and strikethrough -->
            <div v-if="reviewData.suggestions?.length" class="mb-4">
              <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Suggestions</h3>
                <span class="text-xs text-gray-500 dark:text-white/40">
                  {{ completedSuggestions.filter(Boolean).length }} / {{ reviewData.suggestions.length }} completed
                </span>
              </div>
              <div class="space-y-2">
                <div
                  v-for="(s, i) in reviewData.suggestions"
                  :key="'sg'+i"
                  class="flex items-start gap-2 text-sm"
                >
                  <GlassCheckbox
                    :modelValue="completedSuggestions[i] || false"
                    @update:modelValue="(val) => toggleCompletion('suggestion', i, val)"
                    class="mt-1 flex-shrink-0"
                  />
                  <span
                    class="flex-1"
                    :class="completedSuggestions[i]
                      ? 'line-through text-gray-400 dark:text-white/40'
                      : 'text-gray-700 dark:text-white/70'"
                  >
                    {{ s }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Grammar issues with checkboxes and strikethrough across whole line -->
            <div v-if="reviewData.grammar_issues?.length" class="mb-4">
              <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Grammar Issues</h3>
                <span class="text-xs text-gray-500 dark:text-white/40">
                  {{ completedGrammar.filter(Boolean).length }} / {{ reviewData.grammar_issues.length }} completed
                </span>
              </div>
              <div class="space-y-3">
                <div
                  v-for="(issue, i) in reviewData.grammar_issues"
                  :key="'gi'+i"
                  class="flex items-start gap-2 text-sm"
                >
                  <GlassCheckbox
                    :modelValue="completedGrammar[i] || false"
                    @update:modelValue="(val) => toggleCompletion('grammar', i, val)"
                    class="mt-1 flex-shrink-0"
                  />
                  <div
                    :class="completedGrammar[i]
                      ? 'line-through text-gray-400 dark:text-white/40'
                      : 'text-gray-700 dark:text-white/70'"
                  >
                    <div class="flex flex-wrap items-baseline gap-x-1">
                      <span class="text-red-400">{{ issue.text }}</span>
                      <span class="mx-1">→</span>
                      <span class="text-green-400">{{ issue.correction }}</span>
                    </div>
                    <p v-if="issue.comment" class="text-xs text-gray-400 dark:text-white/30 mt-0.5 no-underline">{{ issue.comment }}</p>
                  </div>
                </div>
              </div>
            </div>

            <p class="text-xs text-gray-400 dark:text-white/40 mt-4">Reviewed at {{ formatDate(reviewData.reviewed_at) }}</p>
          </GlassCard>
        </transition-group>

        <!-- Processing State (Elevated glass with liquid progress) -->
        <GlassCard v-else-if="reviewStatus === 'processing' || reviewStatus === 'pending'" variant="elevated" class="p-6 md:p-8 rounded-3xl text-center processing-card">
          <div class="inline-flex items-center gap-3 mb-4">
            <svg class="animate-spin h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-900 dark:text-white text-sm font-medium">AI is reviewing your document...</span>
          </div>
          <!-- Liquid progress bar -->
          <div class="progress-track w-full h-1.5 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden mb-2">
            <div class="progress-fill h-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-500" :style="{ width: reviewProgress + '%' }">
              <div class="absolute inset-0 bg-gradient-to-r from-blue-400/30 to-indigo-400/30 blur-sm"></div>
            </div>
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400">{{ reviewProgress }}% complete</p>
        </GlassCard>

        <!-- Idle state (resting glass slab) -->
        <GlassCard v-else variant="elevated" class="p-6 md:p-8 rounded-3xl text-center idle-card">
          <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-white/10 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <p class="text-gray-500 dark:text-white/50 text-sm">No review yet. Click "Request AI Review" above.</p>
        </GlassCard>
      </div>

      <!-- Upload Again Modal (Ultra-Hero glass) -->
      <GlassModal v-model="showUploadAgain" @close="showUploadAgain = false">
        <div class="space-y-4">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white">Upload New Version</h3>
          <p class="text-sm text-gray-600 dark:text-white/50">Your document type is <strong class="text-gray-900 dark:text-white/80">{{ formatFileType(document.file_type) }}</strong>.</p>
          <div>
            <GlassFileUpload v-model="uploadFiles" :multiple="false" :accept="'.pdf,.doc,.docx,.txt'" />
          </div>
          <div class="flex justify-end gap-3">
            <button @click="showUploadAgain = false" class="px-4 py-2 rounded-xl glass-surface text-gray-700 dark:text-white/80 text-sm hover:bg-gray-200 dark:hover:bg-white/10 transition-colors">
              Cancel
            </button>
            <button
              @click="submitUploadAgain"
              :disabled="!uploadFiles.length || uploading"
              class="px-6 py-2 rounded-xl bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50 transition-all"
            >
              {{ uploading ? 'Uploading...' : 'Upload' }}
            </button>
          </div>
        </div>
      </GlassModal>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, inject } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import GlassModal from '@/Components/GlassModal.vue';
import GlassFileUpload from '@/Components/GlassFileUpload.vue';
import GlassCheckbox from '@/Components/GlassCheckbox.vue';

const props = defineProps({
  document: Object,
  review: [Object, null],
});

const showToast = inject('showToast', null);

const showUploadAgain = ref(false);
const uploadFiles = ref([]);
const uploading = ref(false);
const requesting = ref(false);

const reviewData = ref(null);
const reviewStatus = ref('idle');
const reviewProgress = ref(100);

const completedSuggestions = ref([]);
const completedGrammar = ref([]);

if (props.review && typeof props.review === 'object') {
  reviewData.value = { ...props.review };
  reviewStatus.value = 'completed';
  completedSuggestions.value = props.review.completed_suggestions || [];
  completedGrammar.value = props.review.completed_grammar_issues || [];
}

let pollTimer = null;

function startPolling() {
  if (pollTimer) return;
  pollTimer = setInterval(checkCompletion, 1000);
}

function stopPolling() {
  if (pollTimer) {
    clearInterval(pollTimer);
    pollTimer = null;
  }
}

async function checkCompletion() {
  try {
    const { data } = await axios.get(`/api/documents/${props.document.id}/review-status`);
    reviewProgress.value = data.progress || 0;
    reviewStatus.value = data.status || 'idle';

    if (data.status === 'completed') {
      window.location.reload();
    } else if (data.status !== 'processing' && data.status !== 'pending') {
      stopPolling();
    }
  } catch (error) {
    console.error('Error checking review status:', error);
    stopPolling();
  }
}

async function requestReview() {
  requesting.value = true;
  reviewData.value = null;
  reviewStatus.value = 'pending';
  reviewProgress.value = 0;

  try {
    await axios.post(route('documents.review', props.document.id));
    startPolling();
  } catch (error) {
    console.error('Failed to request review:', error);
    reviewStatus.value = 'idle';
  } finally {
    requesting.value = false;
  }
}

async function toggleCompletion(type, index, checked) {
  // Optimistic update
  if (type === 'suggestion') {
    completedSuggestions.value[index] = checked;
  } else {
    completedGrammar.value[index] = checked;
  }

  try {
    await axios.put(route('documents.review.toggle', props.document.id), {
      type: type === 'suggestion' ? 'suggestion' : 'grammar',
      index,
      completed: checked,
    });
  } catch (error) {
    // Revert on failure
    if (type === 'suggestion') {
      completedSuggestions.value[index] = !checked;
    } else {
      completedGrammar.value[index] = !checked;
    }
    if (showToast) {
      showToast('Failed to update checklist.', 'error');
    }
    console.error('Toggle error:', error.response?.data || error.message);
  }
}

function formatDate(dateStr) {
  try {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return dateStr;
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
  } catch {
    return dateStr || '';
  }
}

function formatFileType(type) {
  const map = { cv: 'CV / Resume', personal_statement: 'Personal Statement', other: 'Other' };
  return map[type] || type;
}

async function submitUploadAgain() {
  if (!uploadFiles.value.length) return;
  uploading.value = true;
  const formData = new FormData();
  formData.append('file_type', props.document.file_type);
  formData.append('file', uploadFiles.value[0]);
  try {
    await axios.post(route('documents.store'), formData);
    showUploadAgain.value = false;
    uploadFiles.value = [];
    window.location.href = route('documents.index');
  } catch (error) {
    console.error('Upload again failed:', error);
  } finally {
    uploading.value = false;
  }
}

onMounted(() => {
  if (!reviewData.value) {
    checkCompletion().then(() => {
      if (reviewStatus.value === 'processing' || reviewStatus.value === 'pending') {
        startPolling();
      }
    });
  }
});

onBeforeUnmount(() => {
  stopPolling();
});
</script>

<style scoped>
/* ================================================================
   GLASS ARCHITECT'S BLUEPRINT – DOCUMENTS/SHOW.VUE
   ================================================================ */

/* ---------- Z-Plane Glass Depths ---------- */
.glass-surface {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.3);
}
.dark .glass-surface {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.06);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.6);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.08);
}

/* 3D Perspective for buttons */
.glass-button {
  transform: rotateY(2deg);
  will-change: transform;
}
.glass-button:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}
.glass-button:active:not(:disabled) {
  transform: scale(0.95) translateY(1px);
}

/* Icon engraving */
.icon-engraved {
  opacity: 0.6;
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.glass-button:hover .icon-engraved {
  opacity: 1;
  transform: scale(1.05);
}

/* Glass divider */
.glass-divider {
  height: 1px;
  background: linear-gradient(to right, transparent, rgba(0,0,0,0.1), transparent);
}
.dark .glass-divider {
  background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
}

/* Score cards (three separate) */
.score-card {
  text-align: center;
  padding: 0.75rem;
  border-radius: 0.75rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(0, 0, 0, 0.05);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.dark .score-card {
  background: rgba(255, 255, 255, 0.02);
  border-color: rgba(255, 255, 255, 0.05);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}
.score-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}
.dark .score-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
}
.quality-score { border-left: 2px solid rgba(59, 130, 246, 0.5); }
.ats-score { border-left: 2px solid rgba(16, 185, 129, 0.5); }
.competitiveness-score { border-left: 2px solid rgba(168, 85, 247, 0.5); }

/* Liquid progress */
.progress-fill {
  position: relative;
  overflow: hidden;
}
.progress-fill::after {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  animation: shimmer 1.5s infinite;
}
@keyframes shimmer {
  0% { left: -100%; }
  100% { left: 100%; }
}

/* Staggered list */
.staggered-list-enter-active, .staggered-list-leave-active {
  transition: all 0.4s ease;
}
.staggered-list-enter-from, .staggered-list-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* Mobile and reduced motion */
@media (max-width: 768px) {
  .glass-button {
    transform: none !important;
  }
  .glass-button:hover:not(:disabled) {
    transform: translateY(-1px) scale(1.01) !important;
  }
  .score-card:hover {
    transform: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation: none !important;
    transition: none !important;
  }
  .glass-button {
    transform: none !important;
  }
  .glass-button:hover:not(:disabled) {
    transform: none !important;
  }
}
</style>