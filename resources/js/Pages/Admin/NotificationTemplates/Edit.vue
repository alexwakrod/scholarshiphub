<template>
  <AppLayout>
    <div class="p-6 max-w-4xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">
          {{ isNew ? 'New Template' : 'Edit Template' }}
        </h1>
        <Link :href="route('admin.notification-templates.index')" class="text-sm text-white/50 hover:text-white underline">
          Back to templates
        </Link>
      </div>

      <div class="grid lg:grid-cols-2 gap-6">
        <!-- Editor form -->
        <GlassCard variant="elevated" class="p-6 space-y-5">
          <GlassInput v-model="form.name" placeholder="Template name (unique)" label="Name" :error="form.errors.name" />
          <GlassInput v-model="form.subject" placeholder="Email subject" label="Subject" :error="form.errors.subject" />

          <div>
            <label class="block text-sm text-white/70 mb-1">Body (Text)</label>
            <GlassTextarea v-model="form.body_text" rows="4" placeholder="Plain text version..." />
          </div>

          <div>
            <label class="block text-sm text-white/70 mb-1">Body (HTML)</label>
            <GlassTextarea v-model="form.body_html" rows="6" placeholder="HTML version..." />
            <p class="text-xs text-white/40 mt-1">
              Use placeholders like <code>@{{ scholarhip_title }}</code>.
            </p>
          </div>

          <div>
            <label class="block text-sm text-white/70 mb-1">Variables (JSON)</label>
            <GlassTextarea v-model="form.variables_text" rows="3" placeholder='["user_name","scholarship_title"]' />
          </div>

          <div class="flex items-center gap-4">
            <GlassToggle v-model="form.is_active" label="Active" />
          </div>

          <!-- Action buttons -->
          <div class="flex gap-3 pt-2">
            <button
              @click="saveTemplate"
              :disabled="form.processing"
              class="px-5 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50"
            >
              {{ isNew ? 'Create' : 'Update' }}
            </button>
            <button
              @click="sendTest"
              :disabled="form.processing"
              class="px-5 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700 disabled:opacity-50"
            >
              Send Test
            </button>
          </div>
        </GlassCard>

        <!-- Live Preview -->
        <GlassCard variant="elevated" class="p-6 space-y-4">
          <h3 class="text-lg font-semibold text-white">Live Preview</h3>
          <div class="border border-white/10 rounded-xl p-4 bg-white/5">
            <p class="text-sm text-white/50 mb-2">Subject: <span class="text-white">{{ preview.subject }}</span></p>
            <div v-if="preview.body_html" class="prose prose-invert prose-sm max-w-none" v-html="preview.body_html"></div>
            <pre v-else class="whitespace-pre-wrap text-sm text-white/70">{{ preview.body_text }}</pre>
          </div>
          <p class="text-xs text-white/40">Preview is rendered with sample data.</p>
        </GlassCard>
      </div>

      <!-- Test send modal -->
      <GlassModal v-model="testModalOpen" @close="testModalOpen = false">
        <div class="space-y-4">
          <h3 class="text-xl font-bold text-white">Send Test Email</h3>
          <GlassInput v-model="testEmail" placeholder="recipient@example.com" type="email" />
          <div class="flex justify-end gap-3">
            <button @click="testModalOpen = false" class="px-4 py-2 rounded-lg bg-white/10 text-white text-sm">Cancel</button>
            <button @click="doTestSend" :disabled="testSending" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700 disabled:opacity-50">
              {{ testSending ? 'Sending...' : 'Send' }}
            </button>
          </div>
          <p v-if="testMessage" class="text-sm" :class="testError ? 'text-red-400' : 'text-green-400'">{{ testMessage }}</p>
        </div>
      </GlassModal>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassTextarea from '@/Components/GlassTextarea.vue';
import GlassToggle from '@/Components/GlassToggle.vue';
import GlassModal from '@/Components/GlassModal.vue';
import { marked } from 'marked';

const props = defineProps({
  template: Object, // may be null for new
});

const isNew = !props.template?.id;

const form = useForm({
  name: props.template?.name || '',
  subject: props.template?.subject || '',
  body_text: props.template?.body_text || '',
  body_html: props.template?.body_html || '',
  variables_text: props.template?.variables ? JSON.stringify(props.template.variables) : '',
  is_active: props.template ? props.template.is_active : true,
});

const preview = reactive({ subject: '', body_text: '', body_html: '' });

const sampleData = {
  user_name: 'John Doe',
  scholarship_title: 'Fully-Funded Masters Scholarship',
  deadline: new Date().toISOString().split('T')[0],
  match_score: '92%',
  review_score: '8.5',
  app_link: window.location.origin + '/dashboard',
  document_type: 'personal_statement',
};

function interpolate(content) {
  let result = content || '';
  Object.entries(sampleData).forEach(([key, val]) => {
    result = result.replace(new RegExp(`{{\\s*${key}\\s*}}`, 'g'), val);
    result = result.replace(new RegExp(`{{${key}}}`, 'g'), val);
  });
  return result;
}

function updatePreview() {
  preview.subject = interpolate(form.subject);
  preview.body_text = interpolate(form.body_text || '');
  if (form.body_html) {
    const interpolatedHtml = interpolate(form.body_html);
    try {
      preview.body_html = marked.parse(interpolatedHtml);
    } catch {
      preview.body_html = interpolatedHtml;
    }
  } else {
    preview.body_html = '';
  }
}

watch(() => [form.subject, form.body_text, form.body_html], updatePreview, { immediate: true });

function saveTemplate() {
  const url = isNew
    ? route('admin.notification-templates.save')
    : route('admin.notification-templates.save', props.template.id);
  form.put(url, {
    preserveScroll: true,
    onSuccess: () => {
      // maybe show toast
    },
  });
}

// Test send
const testModalOpen = ref(false);
const testEmail = ref('');
const testSending = ref(false);
const testMessage = ref('');
const testError = ref(false);

function sendTest() {
  testEmail.value = '';
  testMessage.value = '';
  testModalOpen.value = true;
}

async function doTestSend() {
  testSending.value = true;
  testMessage.value = '';
  try {
    const { data } = await axios.post(route('admin.notification-templates.test-send'), {
      subject: form.subject,
      body_text: form.body_text,
      body_html: form.body_html,
      test_email: testEmail.value,
    });
    testMessage.value = data.message;
    testError.value = false;
    testModalOpen.value = false;
  } catch (e) {
    testMessage.value = e.response?.data?.message || 'Send failed.';
    testError.value = true;
  } finally {
    testSending.value = false;
  }
}
</script>