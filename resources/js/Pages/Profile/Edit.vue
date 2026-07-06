<template>
  <AppLayout>
    <div class="relative min-h-screen overflow-hidden p-4 md:p-6">
      <!-- Animated Background -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/4 right-1/4 w-96 h-96 rounded-full bg-blue-600/5 blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 left-1/4 w-[500px] h-[500px] rounded-full bg-indigo-600/5 blur-3xl animate-pulse-slow" style="animation-delay: 1s"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-purple-600/3 blur-3xl"></div>
        <div class="absolute top-20 right-10 w-3 h-3 rounded-full bg-blue-400/20 animate-float"></div>
        <div class="absolute bottom-40 right-1/4 w-4 h-4 rounded-full bg-indigo-400/20 animate-float" style="animation-delay: 0.7s"></div>
        <div class="absolute top-1/3 left-10 w-2 h-2 rounded-full bg-purple-400/20 animate-float" style="animation-delay: 1.4s"></div>
        <div class="absolute bottom-20 left-1/3 w-3 h-3 rounded-full bg-pink-400/20 animate-float" style="animation-delay: 2s"></div>
      </div>

      <div class="relative max-w-6xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
              Edit <span class="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Profile</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-white/40 mt-0.5">Keep your information up to date for better matches</p>
          </div>
          <div class="flex items-center gap-3">
            <Link
              href="/dashboard"
              class="group relative px-4 py-2 rounded-xl glass-surface hover:glass-elevated transition-all duration-300 hover:scale-105 text-gray-600 dark:text-white/50 hover:text-gray-900 dark:hover:text-white text-sm flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Back
            </Link>
          </div>
        </div>

        <!-- Profile Completion Overview (based only on REQUIRED fields) -->
        <div class="perspective-1000">
          <GlassCard variant="elevated" class="p-4 rounded-2xl transform -rotate-y-2 transition-all duration-700 hover:rotate-y-1">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <div class="relative w-10 h-10 rounded-full glass-elevated flex items-center justify-center">
                  <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">Profile Completion</p>
                  <p class="text-xs text-gray-500 dark:text-white/30">{{ overallCompletionStatus }}</p>
                </div>
              </div>
              <div class="flex items-center gap-4 w-full sm:w-auto">
                <div class="flex-1 sm:w-48">
                  <div class="h-2.5 bg-gray-200 dark:bg-white/5 rounded-full overflow-hidden shadow-inner">
                    <div
                      class="h-full rounded-full transition-all duration-700"
                      :style="{ width: overallCompletion + '%', background: completionGradient }"
                    >
                      <div class="absolute inset-0 opacity-30 blur-sm" :style="{ background: completionGradient }"></div>
                    </div>
                  </div>
                </div>
                <span class="text-lg font-bold text-gray-900 dark:text-white tabular-nums min-w-[44px] text-right">
                  {{ overallCompletion }}%
                </span>
              </div>
            </div>
          </GlassCard>
        </div>

        <!-- 2x2 Grid Sections -->
        <form @submit.prevent="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- SECTION 1: Personal Information -->
          <div class="perspective-500">
            <GlassCard class="p-4 rounded-2xl transform -rotate-y-1 transition-all duration-500 hover:rotate-y-0 hover:shadow-xl h-full">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-full bg-blue-500/10 dark:bg-blue-500/20 flex items-center justify-center text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Personal Info</h3>
                </div>
                <div class="flex items-center gap-1.5">
                  <span class="w-3 h-3 rounded-full" :class="sectionStatusColor('personal')"></span>
                  <span class="text-xs text-gray-500 dark:text-white/30">{{ sectionStatusLabel('personal') }}</span>
                </div>
              </div>
              <div class="space-y-2.5">
                <GlassInput v-model="form.full_name" placeholder="Full name" :error="form.errors.full_name" />
                <div class="grid grid-cols-2 gap-2">
                  <GlassDatePicker v-model="form.date_of_birth" placeholder="Date of birth" />
                  <GlassInput v-model="form.country" placeholder="Country" />
                </div>
                <GlassTextarea v-model="form.bio" :rows="2" placeholder="Bio (optional)" maxlength="500" />
              </div>
            </GlassCard>
          </div>

          <!-- SECTION 2: Academic Details -->
          <div class="perspective-500">
            <GlassCard class="p-4 rounded-2xl transform rotate-y-1 transition-all duration-500 hover:rotate-y-0 hover:shadow-xl h-full">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-full bg-indigo-500/10 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                  </div>
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Academic</h3>
                </div>
                <div class="flex items-center gap-1.5">
                  <span class="w-3 h-3 rounded-full" :class="sectionStatusColor('academic')"></span>
                  <span class="text-xs text-gray-500 dark:text-white/30">{{ sectionStatusLabel('academic') }}</span>
                </div>
              </div>
              <div class="space-y-2.5">
                <GlassSelect v-model="form.academic_level" :options="educationLevelOptions" placeholder="Education level" />
                <GlassSelect v-model="form.major" :options="majorOptions" placeholder="Major / Field of study" searchable />
                <div class="grid grid-cols-2 gap-2">
                  <GlassNumberInput v-model="form.gpa" :min="0" :max="4" :step="0.01" placeholder="GPA (optional)" />
                  <GlassInput v-model="form.english_proficiency" placeholder="e.g. IELTS 7.0" />
                </div>
                <GlassInput v-model="languagesText" @input="updateLanguages" placeholder="Languages (comma, optional)" />
              </div>
            </GlassCard>
          </div>

          <!-- SECTION 3: Experience & Budget -->
          <div class="perspective-500">
            <GlassCard class="p-4 rounded-2xl transform -rotate-y-1 transition-all duration-500 hover:rotate-y-0 hover:shadow-xl h-full">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-full bg-green-500/10 dark:bg-green-500/20 flex items-center justify-center text-green-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Experience &amp; Funding</h3>
                </div>
                <div class="flex items-center gap-1.5">
                  <span class="w-3 h-3 rounded-full" :class="sectionStatusColor('experience')"></span>
                  <span class="text-xs text-gray-500 dark:text-white/30">{{ sectionStatusLabel('experience') }}</span>
                </div>
              </div>
              <div class="space-y-2.5">
                <GlassSelect v-model="form.budget" :options="budgetOptions" placeholder="Funding situation" />
                <GlassSelect v-model="form.target_degree" :options="targetDegreeOptions" placeholder="Target degree (optional)" />
                <GlassCheckbox v-model="form.has_work_experience" label="I have work experience" />
                <GlassCheckbox v-model="form.has_research_experience" label="I have research experience" />
              </div>
            </GlassCard>
          </div>

          <!-- SECTION 4: Demographics & Interests -->
          <div class="perspective-500">
            <GlassCard class="p-4 rounded-2xl transform rotate-y-1 transition-all duration-500 hover:rotate-y-0 hover:shadow-xl h-full">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-full bg-purple-500/10 dark:bg-purple-500/20 flex items-center justify-center text-purple-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </div>
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Demographics</h3>
                </div>
                <div class="flex items-center gap-1.5">
                  <span class="w-3 h-3 rounded-full" :class="sectionStatusColor('demographics')"></span>
                  <span class="text-xs text-gray-500 dark:text-white/30">{{ sectionStatusLabel('demographics') }}</span>
                </div>
              </div>
              <div class="space-y-2.5">
                <div class="grid grid-cols-2 gap-2">
                  <GlassSelect v-model="form.demographics.income" :options="incomeOptions" placeholder="Income (optional)" />
                  <GlassInput v-model="form.demographics.ethnicity" placeholder="Ethnicity (optional)" />
                </div>
                <GlassSelect
                  v-model="form.interests"
                  :options="interestOptions"
                  placeholder="Interests (optional)"
                  :multiple="true"
                  searchable
                />
                <GlassInput v-model="form.english_level" placeholder="English level (required)" />
              </div>
            </GlassCard>
          </div>

          <!-- Submit Card -->
          <div class="md:col-span-2 perspective-1000">
            <GlassCard variant="elevated" class="p-4 rounded-2xl transform -rotate-y-1 transition-all duration-700 hover:rotate-y-0">
              <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-blue-500/10 dark:bg-blue-500/20 flex items-center justify-center text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm text-gray-700 dark:text-white/50">Save changes</p>
                    <p class="text-xs text-gray-500 dark:text-white/30">{{ overallCompletion === 100 ? 'Profile is complete!' : (100 - overallCompletion) + '% remaining' }}</p>
                  </div>
                </div>
                <button
                  type="submit"
                  class="group relative px-8 py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold overflow-hidden transition-all duration-300 hover:shadow-[0_0_60px_rgba(59,130,246,0.4)] hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed min-w-[200px]"
                  :disabled="form.processing"
                >
                  <span class="relative z-10 flex items-center justify-center gap-2">
                    <span v-if="form.processing" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
                    <span v-else>Save & Update Matches</span>
                    <svg v-if="!form.processing" class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                  </span>
                  <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
                </button>
              </div>
            </GlassCard>
          </div>
        </form>
      </div>
    </div>

    <ImageLightbox :src="lightboxSrc" :visible="previewAvatar" @close="previewAvatar = false" />
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassSelect from '@/Components/GlassSelect.vue';
import GlassNumberInput from '@/Components/GlassNumberInput.vue';
import GlassDatePicker from '@/Components/GlassDatePicker.vue';
import GlassTextarea from '@/Components/GlassTextarea.vue';
import GlassCheckbox from '@/Components/GlassCheckbox.vue';
import ImageLightbox from '@/Components/ImageLightbox.vue';

const props = defineProps({
  profile: Object,
  user: Object,
});

const existingDemo = props.profile?.demographics || {};

const form = useForm({
  full_name: props.profile?.full_name ?? '',
  academic_level: props.profile?.academic_level ?? '',
  major: props.profile?.major ?? '',
  gpa: props.profile?.gpa ?? '',
  date_of_birth: props.profile?.date_of_birth ?? '',
  country: props.profile?.country ?? '',
  english_proficiency: props.profile?.english_proficiency ?? '',
  languages: props.profile?.languages ?? [],
  bio: props.profile?.bio ?? '',
  demographics: {
    income: existingDemo.income || '',
    ethnicity: existingDemo.ethnicity || '',
  },
  interests: props.profile?.interests ?? [],
  target_degree: existingDemo.target_degree || '',
  has_work_experience: existingDemo.has_work_experience || false,
  has_research_experience: existingDemo.has_research_experience || false,
  english_level: existingDemo.english_level || '',
  english_score: existingDemo.english_score || '',
  budget: existingDemo.budget || '',
  avatar: null,
});

const avatarPreview = ref(null);
const previewAvatar = ref(false);
const languagesText = ref((props.profile?.languages ?? []).join(', '));

const userAvatarUrl = computed(() => {
  const avatar = props.user?.avatar_url;
  if (!avatar) return '';
  return avatar.startsWith('http') ? avatar : '/storage/' + avatar;
});

const lightboxSrc = computed(() => avatarPreview.value || userAvatarUrl.value);

// Options arrays unchanged...

const educationLevelOptions = [
  { value: 'high_school', label: 'High School' },
  { value: 'bachelors', label: "Bachelor's" },
  { value: 'masters', label: "Master's" },
  { value: 'phd', label: 'PhD' },
];

const majorOptions = [
  { value: 'Computer Science', label: 'Computer Science' },
  { value: 'Engineering', label: 'Engineering' },
  { value: 'Medicine', label: 'Medicine' },
  { value: 'Business', label: 'Business' },
  { value: 'Arts', label: 'Arts' },
  { value: 'Mathematics', label: 'Mathematics' },
  { value: 'Physics', label: 'Physics' },
  { value: 'Law', label: 'Law' },
  { value: 'Biology', label: 'Biology' },
  { value: 'Chemistry', label: 'Chemistry' },
  { value: 'Economics', label: 'Economics' },
  { value: 'Psychology', label: 'Psychology' },
];

const targetDegreeOptions = [
  { value: 'bachelors', label: "Bachelor's" },
  { value: 'masters', label: "Master's" },
  { value: 'phd', label: 'PhD' },
  { value: 'exchange', label: 'Exchange Program' },
  { value: 'summer', label: 'Summer School' },
];

const budgetOptions = [
  { value: 'none', label: 'No funding available' },
  { value: 'limited', label: 'Limited (partial coverage needed)' },
  { value: 'moderate', label: 'Moderate (can cover some costs)' },
  { value: 'full', label: 'Full (can self-fund)' },
];

const incomeOptions = [
  { value: 'low', label: 'Low Income' },
  { value: 'middle', label: 'Middle Income' },
  { value: 'high', label: 'High Income' },
  { value: 'prefer_not', label: 'Prefer Not to Say' },
];

const interestOptions = [
  { value: 'fully_funded', label: 'Fully Funded' },
  { value: 'europe', label: 'Europe' },
  { value: 'asia', label: 'Asia' },
  { value: 'north_america', label: 'North America' },
  { value: 'engineering', label: 'Engineering' },
  { value: 'medicine', label: 'Medicine' },
  { value: 'business', label: 'Business' },
  { value: 'arts', label: 'Arts' },
  { value: 'technology', label: 'Technology' },
  { value: 'science', label: 'Science' },
  { value: 'masters', label: "Master's" },
  { value: 'phd', label: 'PhD' },
  { value: 'undergraduate', label: 'Undergraduate' },
];

// ========== COMPLETION LOGIC (EXCLUDES OPTIONAL FIELDS) ==========

/**
 * The set of fields that are MANDATORY for profile completion.
 * Matches ProfileCompletionService: full_name, academic_level, major, date_of_birth, country,
 * plus demographics: english_level, budget.
 */
const requiredFields = [
  'full_name', 'academic_level', 'major', 'date_of_birth', 'country', 'english_level', 'budget'
];

const isRequiredFilled = (field) => {
  const val = field === 'english_level' ? form.english_level : (field === 'budget' ? form.budget : form[field]);
  if (val === null || val === undefined) return false;
  if (typeof val === 'string') return val.trim().length > 0;
  if (Array.isArray(val)) return val.length > 0;
  return true;
};

const overallCompletion = computed(() => {
  const filled = requiredFields.filter(f => isRequiredFilled(f)).length;
  return Math.round((filled / requiredFields.length) * 100);
});

const overallCompletionStatus = computed(() => {
  const p = overallCompletion.value;
  if (p === 100) return 'Profile complete – all required fields filled.';
  if (p >= 71) return 'Almost there – a few required fields missing.';
  if (p >= 50) return 'Halfway – keep going.';
  return 'Required fields need attention.';
});

const completionGradient = computed(() => {
  const p = overallCompletion.value;
  if (p === 100) return 'linear-gradient(to right, #34d399, #22c55e)';
  if (p >= 71) return 'linear-gradient(to right, #60a5fa, #3b82f6)';
  if (p >= 50) return 'linear-gradient(to right, #fbbf24, #f59e0b)';
  return 'linear-gradient(to right, #f87171, #ef4444)';
});

/**
 * For section status: we show a green dot only if ALL required fields within that section are filled.
 * Sections and their required fields:
 *   personal: full_name, date_of_birth, country
 *   academic: academic_level, major
 *   experience: budget
 *   demographics: english_level
 */
const sectionRequiredMap = {
  personal: ['full_name', 'date_of_birth', 'country'],
  academic: ['academic_level', 'major'],
  experience: ['budget'],
  demographics: ['english_level'],
};

const sectionCompletion = computed(() => {
  return Object.fromEntries(
    Object.entries(sectionRequiredMap).map(([section, fields]) => {
      const filled = fields.filter(f => isRequiredFilled(f)).length;
      return [section, Math.round((filled / fields.length) * 100)];
    })
  );
});

const sectionStatusColor = (section) => {
  const p = sectionCompletion.value[section];
  return p === 100 ? 'bg-green-500' : 'bg-gray-400 dark:bg-gray-500';
};

const sectionStatusLabel = (section) => {
  const p = sectionCompletion.value[section];
  return p === 100 ? 'Complete' : 'Incomplete';
};

function updateLanguages() {
  form.languages = languagesText.value.split(',').map(s => s.trim()).filter(Boolean);
}

function handleAvatar(e) {
  const file = e.target.files[0];
  if (file) {
    form.avatar = file;
    const reader = new FileReader();
    reader.onload = (ev) => { avatarPreview.value = ev.target.result; };
    reader.readAsDataURL(file);
  }
}

function save() {
  const demographics = {
    ...form.demographics,
    target_degree: form.target_degree,
    has_work_experience: form.has_work_experience,
    has_research_experience: form.has_research_experience,
    english_level: form.english_level,
    english_score: form.english_score,
    budget: form.budget,
  };

  form.transform(data => ({
    ...data,
    demographics: JSON.stringify(demographics),
    languages: JSON.stringify(data.languages),
    interests: JSON.stringify(data.interests),
    target_degree: undefined,
    has_work_experience: undefined,
    has_research_experience: undefined,
    english_level: undefined,
    english_score: undefined,
    budget: undefined,
  })).put(route('profile.update'), { preserveScroll: true });
}
</script>

<style scoped>
/* Theme‑aware glass utilities (override for Profile page) */
.glass-surface {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.dark .glass-surface {
  background: rgba(255, 255, 255, 0.03);
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
  background: rgba(255, 255, 255, 0.07);
  border-color: rgba(255, 255, 255, 0.12);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.perspective-1000 { perspective: 1000px; }
.perspective-500 { perspective: 500px; }
.-rotate-y-2 { transform: rotateY(-2deg) rotateX(1deg); }
.rotate-y-1 { transform: rotateY(1deg) rotateX(0.5deg); }
.-rotate-y-1 { transform: rotateY(-1deg); }
.rotate-y-0 { transform: rotateY(0deg); }

/* Animations */
@keyframes float {
  0%, 100% { transform: translateY(0px) scale(1); opacity: 0.2; }
  50% { transform: translateY(-20px) scale(1.2); opacity: 0.6; }
}
.animate-float { animation: float 6s ease-in-out infinite; }
.animate-float.delay-700 { animation-delay: 700ms; }
.animate-float.delay-1400 { animation-delay: 1400ms; }
.animate-float.delay-2000 { animation-delay: 2000ms; }

@keyframes pulse-slow {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.1); }
}
.animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }

@media (max-width: 768px) {
  .perspective-1000, .perspective-500 { perspective: none; }
  .-rotate-y-2, .rotate-y-1, .-rotate-y-1, .rotate-y-0 { transform: none !important; }
}
</style>