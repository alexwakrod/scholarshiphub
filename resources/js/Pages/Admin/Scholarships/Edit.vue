<template>
  <AppLayout>
    <div class="p-6 max-w-3xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Edit Scholarship</h1>
        <div class="flex items-center gap-3">
          <button @click="diffOpen = true" class="text-sm text-white/50 hover:text-white underline">View Changes</button>
          <Link :href="route('admin.scholarships.index')" class="text-sm text-white/50 hover:text-white underline">Back to list</Link>
        </div>
      </div>

      <GlassCard variant="elevated" class="p-6">
        <form @submit.prevent="save" class="space-y-5">
          <div>
            <label class="block text-sm text-white/70 mb-1">Title</label>
            <GlassInput v-model="form.title" :error="form.errors.title" />
          </div>
          <div>
            <label class="block text-sm text-white/70 mb-1">Description</label>
            <GlassTextarea v-model="form.description" rows="6" :error="form.errors.description" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm text-white/70 mb-1">Provider</label>
              <GlassInput v-model="form.provider" :error="form.errors.provider" />
            </div>
            <div>
              <label class="block text-sm text-white/70 mb-1">Country</label>
              <GlassInput v-model="form.country" :error="form.errors.country" />
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm text-white/70 mb-1">Amount (USD)</label>
              <GlassNumberInput v-model="form.amount" :min="0" :step="0.01" />
            </div>
            <div>
              <label class="block text-sm text-white/70 mb-1">Deadline</label>
              <GlassInput v-model="form.deadline" type="datetime-local" :error="form.errors.deadline" />
            </div>
          </div>
          <div>
            <label class="block text-sm text-white/70 mb-1">Source URL</label>
            <GlassInput v-model="form.source_url" placeholder="https://..." />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm text-white/70 mb-1">Status</label>
              <GlassSelect
                v-model="form.status"
                :options="[{value:'active',label:'Active'},{value:'expired',label:'Expired'}]"
              />
            </div>
          </div>
          <div>
            <label class="block text-sm text-white/70 mb-1">Parsed Requirements (JSON)</label>
            <GlassTextarea v-model="form.parsed_requirements_text" rows="4" placeholder='{"academic_level":"undergraduate",...}' />
            <p v-if="form.errors.parsed_requirements" class="text-red-400 text-xs mt-1">{{ form.errors.parsed_requirements }}</p>
          </div>

          <div class="flex justify-between pt-4">
            <button
              type="button"
              @click="triggerParse"
              class="px-4 py-2 rounded-lg bg-yellow-600 text-white text-sm hover:bg-yellow-700"
            >
              Re-parse with AI
            </button>
            <button
              type="submit"
              class="px-6 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50"
              :disabled="form.processing"
            >
              Update Scholarship
            </button>
          </div>
        </form>
      </GlassCard>

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