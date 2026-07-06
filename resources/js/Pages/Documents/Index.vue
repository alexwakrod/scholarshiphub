<template>
  <AppLayout>
    <div class="relative min-h-screen overflow-hidden bg-gray-100 dark:bg-gray-950 p-4 md:p-6">
      <!-- Animated Background Particles -->
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
        <!-- Header with 3D Glass Card -->
        <div class="perspective-1000">
          <GlassCard
            variant="elevated"
            class="p-6 md:p-8 rounded-3xl transform -rotate-y-3 transition-all duration-700 hover:rotate-y-0 hover:-translate-y-1 hover:shadow-2xl"
          >
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                  <span class="text-gradient">My Documents</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-white/40 mt-1">
                  Upload and review your CV, personal statements, and other materials
                </p>
              </div>

              <!-- Upload Button with Glow -->
              <button
                @click="showUploadModal = true"
                class="group relative px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-400"
              >
                <span class="relative z-10 flex items-center gap-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  <span>Upload New Document</span>
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
              </button>
            </div>
          </GlassCard>
        </div>

        <!-- Empty State (3D Glass) -->
        <div v-if="documents.length === 0" class="perspective-1000">
          <GlassCard
            variant="elevated"
            class="p-8 rounded-3xl transform -rotate-y-2 hover:rotate-y-0 hover:-translate-y-1 transition-all duration-500 text-center"
          >
            <EmptyState
              title="No documents yet"
              description="Upload your CV, personal statement, or other documents to get AI-powered reviews."
            />
            <div class="mt-8">
              <button
                @click="showUploadModal = true"
                class="group relative px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95"
              >
                <span class="relative z-10 flex items-center gap-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  <span>Upload Your First Document</span>
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
              </button>
            </div>
          </GlassCard>
        </div>

        <!-- Document Groups with Staggered Entrance -->
        <div v-else class="space-y-8">
          <transition-group name="staggered-list" appear>
            <div
              v-for="(group, index) in documents"
              :key="group.file_type"
              class="perspective-1000"
              :style="{ transitionDelay: `${index * 80}ms` }"
            >
              <GlassCard
                variant="elevated"
                class="p-4 md:p-6 rounded-3xl transform -rotate-y-1 transition-all duration-500 hover:rotate-y-0 hover:-translate-y-1 hover:shadow-2xl"
              >
                <!-- Group Header -->
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 px-2 flex items-center gap-2.5">
                  <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  {{ formatFileType(group.file_type) }}
                </h2>

                <!-- Desktop Table Header (updated – single score column) -->
                <div class="hidden sm:flex items-center px-4 py-2 text-xs uppercase tracking-wider text-gray-500 dark:text-white/30 border-b border-white/5">
                  <span class="w-12">Ver</span>
                  <span class="flex-1">File Name</span>
                  <span class="w-24">Uploaded</span>
                  <span class="w-24 text-center">Overall Score</span>
                  <span class="w-32 text-right">Actions</span>
                </div>

                <!-- Document Rows -->
                <div
                  v-for="doc in group.versions"
                  :key="doc.id"
                  class="flex flex-col sm:flex-row items-start sm:items-center px-4 py-3 border-b border-white/5 hover:bg-white/5 dark:hover:bg-white/5 transition-all duration-300 rounded-lg group cursor-default doc-row"
                >
                  <!-- Mobile stack -->
                  <div class="flex w-full sm:hidden items-center justify-between mb-2">
                    <div>
                      <span class="text-xs text-gray-500 dark:text-white/30">v{{ doc.version_number }}</span>
                      <span class="mx-2 text-gray-500 dark:text-white/30">·</span>
                      <span class="text-xs text-gray-500 dark:text-white/30">{{ formatDate(doc.created_at) }}</span>
                    </div>
                  </div>
                  <div class="sm:hidden w-full">
                    <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ doc.file_name }}</span>
                  </div>

                  <!-- Desktop columns -->
                  <span class="hidden sm:block w-12 text-sm text-gray-500 dark:text-white/30">{{ doc.version_number }}</span>
                  <span class="flex-1 text-sm font-medium text-gray-900 dark:text-white truncate pr-4">{{ doc.file_name }}</span>
                  <span class="hidden sm:block w-24 text-xs text-gray-500 dark:text-white/30">{{ formatDate(doc.created_at) }}</span>

                  <!-- Single overall score badge -->
                  <div class="flex gap-2 sm:gap-0 sm:w-24 items-center justify-center mt-2 sm:mt-0 w-full sm:w-auto">
                    <span v-if="averageOf(doc) !== null" class="glass-badge" :class="scoreClass(averageOf(doc))">
                      {{ averageOf(doc) }}
                    </span>
                    <span v-else class="glass-badge glass-badge-empty">—</span>
                  </div>

                  <!-- Action buttons: View and Delete -->
                  <div class="flex items-center gap-2 mt-2 sm:mt-0 sm:w-32 sm:justify-end w-full actions-bar">
                    <button
                      @click="viewDocument(doc.id)"
                      class="glass-action-btn glass-action-indigo"
                      title="View document"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <button
                      @click="confirmDelete(doc.id)"
                      class="glass-action-btn glass-action-red ml-1"
                      title="Delete document"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>
              </GlassCard>
            </div>
          </transition-group>
        </div>

        <!-- Upload Modal (Ultra‑Hero depth) -->
        <GlassModal v-model="showUploadModal" @close="showUploadModal = false">
          <div class="perspective-1000">
            <div class="transform -rotate-y-1 transition-all duration-500 hover:rotate-y-0 space-y-4">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">Upload Document</h3>
              <div>
                <label class="block text-sm text-gray-700 dark:text-white/70 mb-1">Document Type</label>
                <GlassSelect v-model="uploadForm.file_type" :options="fileTypeOptions" placeholder="Choose type" />
              </div>
              <div v-if="uploadForm.file_type">
                <GlassFileUpload
                  v-model="uploadForm.files"
                  :multiple="false"
                  :accept="'.pdf,.doc,.docx,.txt'"
                />
              </div>
              <div class="flex justify-end">
                <button
                  @click="submitUpload"
                  class="group relative px-6 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(59,130,246,0.3)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50"
                  :disabled="!uploadForm.file_type || uploadForm.files.length === 0 || uploading"
                >
                  <span class="relative z-10 flex items-center gap-2">
                    <span v-if="uploading" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
                    <span>{{ uploading ? 'Uploading...' : 'Upload' }}</span>
                  </span>
                  <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
                </button>
              </div>
            </div>
          </div>
        </GlassModal>

        <!-- Delete confirmation modal (Ultra‑Hero depth) -->
        <GlassModal v-model="showDeleteModal" @close="showDeleteModal = false">
          <div class="perspective-1000">
            <div class="transform -rotate-y-1 transition-all duration-500 hover:rotate-y-0 space-y-4">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">Delete Document?</h3>
              <p class="text-gray-600 dark:text-white/70">Are you sure you want to delete this document? This action cannot be undone.</p>
              <div class="flex justify-end gap-3">
                <button @click="showDeleteModal = false" class="px-4 py-2 rounded-xl glass-surface text-gray-700 dark:text-white/80 text-sm hover:bg-gray-200 dark:hover:bg-white/10 transition-colors">
                  Cancel
                </button>
                <button @click="executeDelete" class="px-4 py-2 rounded-xl bg-gradient-to-r from-red-600 to-orange-600 text-white text-sm font-medium hover:shadow-[0_0_30px_rgba(239,68,68,0.3)] transition-all">
                  Delete
                </button>
              </div>
            </div>
          </div>
        </GlassModal>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import GlassModal from '@/Components/GlassModal.vue';
import GlassSelect from '@/Components/GlassSelect.vue';
import GlassFileUpload from '@/Components/GlassFileUpload.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
  documents: Array,
  documentTypes: Array,
});

const showUploadModal = ref(false);
const showDeleteModal = ref(false);
const uploading = ref(false);
const documentToDelete = ref(null);

const uploadForm = reactive({
  file_type: '',
  files: [],
});

const fileTypeOptions = props.documentTypes.map(type => ({ value: type, label: formatFileType(type) }));

function formatFileType(type) {
  const map = {
    cv: 'CV / Resume',
    personal_statement: 'Personal Statement',
    other: 'Other',
  };
  return map[type] || type;
}

function formatDate(dateStr) {
  try {
    return new Date(dateStr).toLocaleDateString();
  } catch {
    return dateStr;
  }
}

// Compute average of quality, ats, competitiveness
function averageOf(doc) {
  const scores = [doc.quality_score, doc.ats_score, doc.competitiveness_score];
  const valid = scores.filter(s => s != null).map(Number);
  if (valid.length === 0) return null;
  const sum = valid.reduce((a, b) => a + b, 0);
  return (sum / valid.length).toFixed(1);
}

function scoreClass(score) {
  const num = parseFloat(score);
  if (isNaN(num)) return 'glass-badge-empty';
  if (num >= 8) return 'glass-badge-green';
  if (num >= 6) return 'glass-badge-yellow';
  return 'glass-badge-red';
}

async function submitUpload() {
  if (!uploadForm.file_type || uploadForm.files.length === 0) return;
  uploading.value = true;
  const formData = new FormData();
  formData.append('file_type', uploadForm.file_type);
  formData.append('file', uploadForm.files[0]);

  try {
    const response = await axios.post(route('documents.store'), formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    const newId = response.data.id;
    showUploadModal.value = false;
    uploadForm.file_type = '';
    uploadForm.files = [];
    router.visit(route('documents.show', newId));
  } catch (error) {
    console.error('Upload failed:', error);
  } finally {
    uploading.value = false;
  }
}

function viewDocument(documentId) {
  router.visit(route('documents.show', documentId));
}

function confirmDelete(id) {
  documentToDelete.value = id;
  showDeleteModal.value = true;
}

async function executeDelete() {
  if (!documentToDelete.value) return;
  try {
    await axios.post(route('documents.destroy', documentToDelete.value), {
      _method: 'DELETE',
    });
    showDeleteModal.value = false;
    documentToDelete.value = null;
    router.visit(route('documents.index'));
  } catch (error) {
    console.error('Delete failed:', error);
  }
}
</script>

<style scoped>
/* ================================================================
   PREMIUM GLASS DOCUMENTS INDEX – THEME‑AWARE & FULL BLUEPRINT
   ================================================================ */

/* ---------- Z‑PLANE GLASS DEPTHS (reused) ---------- */
.glass-surface {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.dark .glass-surface {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.06);
}
.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
}

/* 3D Perspective & Physics */
.perspective-1000 { perspective: 1000px; }
.-rotate-y-1 { transform: rotateY(-1deg) rotateX(0.5deg); }
.-rotate-y-2 { transform: rotateY(-2deg) rotateX(1deg); }
.-rotate-y-3 { transform: rotateY(-3deg) rotateX(1deg); }
.rotate-y-0 { transform: rotateY(0deg); }

.doc-row {
  transition: all 0.25s ease;
  transform: rotateY(1deg);
  will-change: transform;
}
.doc-row:hover {
  transform: rotateY(0deg) translateY(-2px) scale(1.01);
}

/* Typography Gradient */
.text-gradient {
  background: linear-gradient(135deg, #60a5fa, #818cf8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Score Badges with Energy Colours */
.glass-badge {
  @apply px-2 py-0.5 rounded-lg text-xs font-mono tabular-nums backdrop-blur-sm border transition-colors duration-200;
  min-width: 2.5rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.glass-badge-green {
  background: rgba(16, 185, 129, 0.15);
  border-color: rgba(16, 185, 129, 0.3);
  color: #10b981;
  box-shadow: 0 0 12px rgba(16, 185, 129, 0.2);
}
.glass-badge-yellow {
  background: rgba(245, 158, 11, 0.15);
  border-color: rgba(245, 158, 11, 0.3);
  color: #f59e0b;
  box-shadow: 0 0 12px rgba(245, 158, 11, 0.2);
}
.glass-badge-red {
  background: rgba(239, 68, 68, 0.15);
  border-color: rgba(239, 68, 68, 0.3);
  color: #ef4444;
  box-shadow: 0 0 12px rgba(239, 68, 68, 0.2);
}
.glass-badge-empty {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.3);
}

/* Action Buttons (Reduced 3D, Adaptive) */
.glass-action-btn {
  @apply text-xs px-3 py-1.5 rounded-lg backdrop-blur-sm border font-medium transition-all duration-300 hover:scale-105 active:scale-95 focus-visible:ring-2 focus-visible:ring-offset-1 focus-visible:ring-blue-400;
  transform: rotateY(1deg);
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.glass-action-btn:hover {
  transform: rotateY(0deg) scale(1.05);
}
.glass-action-indigo {
  background: rgba(99, 102, 241, 0.15);
  border-color: rgba(99, 102, 241, 0.3);
  color: #818cf8;
}
.glass-action-indigo:hover {
  background: rgba(99, 102, 241, 0.25);
}
.glass-action-red {
  background: rgba(239, 68, 68, 0.15);
  border-color: rgba(239, 68, 68, 0.3);
  color: #fca5a5;
  margin-left: 0.25rem;
}
.glass-action-red:hover {
  background: rgba(239, 68, 68, 0.25);
}

/* Adaptive action bar on mobile: keep buttons as icons */
.actions-bar .glass-action-btn {
  width: 2.25rem;
  height: 2.25rem;
  padding: 0;
}
@media (min-width: 640px) {
  .actions-bar .glass-action-btn {
    width: auto;
    height: auto;
    padding-left: 0.75rem;
    padding-right: 0.75rem;
    padding-top: 0.375rem;
    padding-bottom: 0.375rem;
  }
}

/* Staggered List Entrance */
.staggered-list-enter-active, .staggered-list-leave-active {
  transition: all 0.4s ease;
}
.staggered-list-enter-from, .staggered-list-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* Background animations */
@keyframes float {
  0%, 100% { transform: translateY(0px) scale(1); opacity: 0.3; }
  50% { transform: translateY(-20px) scale(1.2); opacity: 0.8; }
}
.animate-float { animation: float 6s ease-in-out infinite; }

@keyframes pulse-slow {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.1); }
}
.animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }

/* Accessibility & Mobile Overrides */
@media (max-width: 768px) {
  .perspective-1000 { perspective: none; }
  .-rotate-y-1, .-rotate-y-2, .-rotate-y-3 { transform: none !important; }
  .doc-row { transform: none; }
  .doc-row:hover { transform: none; }
  .glass-action-btn { transform: none; }
  .glass-action-btn:hover { transform: scale(1.05); }
}
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation: none !important;
    transition: none !important;
  }
  .doc-row, .glass-action-btn, .glass-action-btn:hover { transform: none; }
}
</style>