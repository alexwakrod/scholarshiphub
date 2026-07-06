<template>
  <AppLayout>
    <div class="p-6 max-w-3xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Document Review</h1>
        <Link :href="route('documents.index')" class="text-sm text-white/50 hover:text-white underline">Back to documents</Link>
      </div>

      <GlassCard variant="elevated" class="p-6">
        <p class="text-sm text-white/50">Document: {{ document.file_name }}</p>
        <button
          @click="showUploadAgain = true"
          class="mt-3 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 transition-colors"
        >
          Upload Again
        </button>
      </GlassCard>

      <GlassCard v-if="review" variant="elevated" class="p-6">
        <div class="grid grid-cols-3 gap-4 mb-6">
          <div class="text-center p-3 rounded-lg bg-white/5">
            <p class="text-2xl font-bold text-blue-400">{{ review.quality_score }}</p>
            <p class="text-xs text-white/50">Quality</p>
          </div>
          <div class="text-center p-3 rounded-lg bg-white/5">
            <p class="text-2xl font-bold text-green-400">{{ review.ats_score }}</p>
            <p class="text-xs text-white/50">ATS Score</p>
          </div>
          <div class="text-center p-3 rounded-lg bg-white/5">
            <p class="text-2xl font-bold text-purple-400">{{ review.competitiveness_score }}</p>
            <p class="text-xs text-white/50">Competitiveness</p>
          </div>
        </div>

        <div v-if="review.strengths?.length" class="mb-4">
          <h3 class="text-sm font-medium text-white mb-2">Strengths</h3>
          <ul class="list-disc list-inside text-sm text-white/70 space-y-1">
            <li v-for="(s, i) in review.strengths" :key="i">{{ s }}</li>
          </ul>
        </div>

        <div v-if="review.weaknesses?.length" class="mb-4">
          <h3 class="text-sm font-medium text-white mb-2">Weaknesses</h3>
          <ul class="list-disc list-inside text-sm text-white/70 space-y-1">
            <li v-for="(w, i) in review.weaknesses" :key="i">{{ w }}</li>
          </ul>
        </div>

        <!-- Suggestions with checkboxes -->
        <div v-if="review.suggestions?.length" class="mb-4">
          <h3 class="text-sm font-medium text-white mb-2">Suggestions</h3>
          <div class="space-y-2">
            <div v-for="(s, i) in review.suggestions" :key="i" class="flex items-start gap-2 text-sm text-white/70">
              <input
                type="checkbox"
                :checked="completedSuggestions[i] || false"
                @change="toggleCompletion('suggestion', i, $event.target.checked)"
                class="mt-1 rounded border-white/30 text-blue-500 focus:ring-blue-400 bg-white/10"
              />
              <span :class="{ 'line-through text-white/40': completedSuggestions[i] }">{{ s }}</span>
            </div>
          </div>
        </div>

        <!-- Grammar issues with checkboxes -->
        <div v-if="review.grammar_issues?.length" class="mb-4">
          <h3 class="text-sm font-medium text-white mb-2">Grammar Issues</h3>
          <div class="space-y-2">
            <div v-for="(issue, i) in review.grammar_issues" :key="i" class="flex items-start gap-2 text-sm text-white/70">
              <input
                type="checkbox"
                :checked="completedGrammar[i] || false"
                @change="toggleCompletion('grammar', i, $event.target.checked)"
                class="mt-1 rounded border-white/30 text-blue-500 focus:ring-blue-400 bg-white/10"
              />
              <div>
                <span class="text-red-400 line-through mr-1">{{ issue.text }}</span>
                <span class="text-green-400">{{ issue.correction }}</span>
              </div>
            </div>
          </div>
        </div>

        <p class="text-xs text-white/40 mt-4">
          Reviewed at {{ formatDate(review.reviewed_at) }}
        </p>
      </GlassCard>
      <div v-else class="text-center text-white/50">No review data available.</div>
    </div>

    <!-- Upload Again Modal -->
    <GlassModal v-model="showUploadAgain" @close="showUploadAgain = false">
      <div class="space-y-4">
        <h3 class="text-xl font-bold text-white">Upload New Version</h3>
        <p class="text-sm text-white/50">Your document type is <strong>{{ formatFileType(document.file_type) }}</strong>.</p>
        <div>
          <GlassFileUpload
            v-model="uploadFiles"
            :multiple="false"
            :accept="'.pdf,.doc,.docx,.txt'"
          />
        </div>
        <div class="flex justify-end">
          <button
            @click="submitUploadAgain"
            :disabled="!uploadFiles.length || uploading"
            class="px-6 py-2 rounded-xl bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50"
          >
            {{ uploading ? 'Uploading...' : 'Upload' }}
          </button>
        </div>
      </div>
    </GlassModal>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import GlassModal from '@/Components/GlassModal.vue';
import GlassFileUpload from '@/Components/GlassFileUpload.vue';

const props = defineProps({
  document: Object,
  review: Object,
});

const showUploadAgain = ref(false);
const uploadFiles = ref([]);
const uploading = ref(false);

// Completion arrays – initialize from database or empty
const completedSuggestions = ref([]);
const completedGrammar = ref([]);

onMounted(() => {
  if (props.review) {
    completedSuggestions.value = props.review.completed_suggestions || [];
    completedGrammar.value = props.review.completed_grammar_issues || [];
  }
});

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
    console.error('Toggle error:', error);
  }
}

function formatDate(dateStr) {
  try {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return dateStr;
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  } catch {
    return dateStr || '';
  }
}

function formatFileType(type) {
  const map = {
    cv: 'CV / Resume',
    personal_statement: 'Personal Statement',
    other: 'Other',
  };
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
    // Optionally trigger a new review for the latest version
    // But the user might want to see the new version first, so we just refresh the document list
    window.location.href = route('documents.index');
  } catch (error) {
    console.error('Upload again failed:', error);
  } finally {
    uploading.value = false;
  }
}
</script>