<template>
  <AppLayout>
    <div class="relative min-h-screen overflow-hidden p-4 md:p-6 space-y-6">
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

      <div class="relative max-w-5xl mx-auto space-y-6">
        <!-- Back link -->
        <div class="flex items-center gap-3">
          <Link
            :href="route('scholarships.index')"
            class="group relative px-4 py-2 rounded-xl glass-surface hover:glass-elevated transition-all duration-300 hover:scale-105 text-gray-600 dark:text-white/50 hover:text-gray-900 dark:hover:text-white text-sm flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Scholarships
          </Link>
        </div>

        <!-- Header with Match Gauge and Deadline Badge integrated -->
        <div class="perspective-1000">
          <GlassCard variant="elevated" class="p-6 md:p-8 rounded-3xl transform -rotate-y-2 transition-all duration-700 hover:rotate-y-1 relative overflow-hidden">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
              <div class="flex-1">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">{{ scholarship.title }}</h1>
                <div class="flex items-center gap-2 mt-2 text-sm text-gray-500 dark:text-white/40">
                  <span>{{ scholarship.provider }}</span>
                  <span class="w-1 h-1 rounded-full bg-gray-400 dark:bg-white/20"></span>
                  <span>{{ scholarship.country }}</span>
                  <span v-if="scholarship.amount" class="text-accent font-semibold">
                    <span class="w-1 h-1 rounded-full bg-gray-400 dark:bg-white/20 mx-1 inline-block"></span>
                    ${{ scholarship.amount }}
                  </span>
                </div>

                <!-- Deadline badge inline, after provider line -->
                <div class="mt-3">
                  <div
                    class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold shadow-lg backdrop-blur-sm"
                    :class="deadlineBadgeClass"
                  >
                    <span v-if="isDeadlineUrgent" class="mr-1">⚡</span>
                    {{ isDeadlineUrgent ? 'URGENT' : 'Deadline' }}: {{ formattedDeadline }}
                  </div>
                </div>

                <!-- Tags row with new structured data -->
                <div class="flex flex-wrap items-center gap-2 mt-3">
                  <!-- Academic levels -->
                  <span
                    v-for="level in tagList.academic_levels"
                    :key="level"
                    class="px-2 py-0.5 rounded-full text-[11px] glass-tag cursor-pointer hover:scale-105 transition-transform"
                    @click="filterByTag(level)"
                  >
                    {{ level }}
                  </span>
                  <!-- Majors -->
                  <span
                    v-for="major in tagList.majors"
                    :key="major"
                    class="px-2 py-0.5 rounded-full text-[11px] glass-tag cursor-pointer hover:scale-105 transition-transform"
                    @click="filterByTag(major)"
                  >
                    {{ major }}
                  </span>
                  <!-- Countries -->
                  <span
                    v-for="country in tagList.countries"
                    :key="country"
                    class="px-2 py-0.5 rounded-full text-[11px] glass-tag cursor-pointer hover:scale-105 transition-transform"
                    @click="filterByTag(country)"
                  >
                    {{ country }}
                  </span>
                  <!-- Host institution -->
                  <span
                    v-if="tagList.host_institution"
                    class="px-2 py-0.5 rounded-full text-[11px] glass-tag"
                  >
                    {{ tagList.host_institution }}
                  </span>
                  <!-- Duration -->
                  <span
                    v-if="tagList.duration"
                    class="px-2 py-0.5 rounded-full text-[11px] glass-tag"
                  >
                    {{ tagList.duration }}
                  </span>
                  <!-- Benefit type -->
                  <span
                    v-if="tagList.benefit_type"
                    class="px-2 py-0.5 rounded-full text-[11px] glass-tag"
                    :class="benefitTagClass"
                  >
                    {{ tagList.benefit_type }}
                  </span>
                  <!-- English proficiency -->
                  <span
                    v-if="tagList.english_proficiency"
                    class="px-2 py-0.5 rounded-full text-[11px] glass-tag"
                  >
                    {{ tagList.english_proficiency }}
                  </span>
                  <!-- GPA -->
                  <span
                    v-if="tagList.minimum_gpa"
                    class="px-2 py-0.5 rounded-full text-[11px] glass-tag"
                  >
                    GPA ≥ {{ tagList.minimum_gpa }}
                  </span>
                </div>
              </div>
              <div class="flex items-center gap-4">
                <MatchGauge v-if="matchScore" :score="matchScore.overall_score" :size="80" />
                <div v-else class="text-gray-500 dark:text-white/50 text-sm">Complete your profile to see match.</div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap items-center gap-3 mt-6">
              <!-- Bookmark button (theme‑aware) -->
              <button
                @click="toggleBookmark"
                class="px-4 py-2 rounded-xl flex items-center gap-2 text-sm transition-colors glass-surface hover:glass-elevated"
                :class="isBookmarked
                  ? 'bg-yellow-500/20 dark:bg-yellow-500/30 text-yellow-700 dark:text-yellow-300 border-yellow-500/30 dark:border-yellow-500/50'
                  : 'text-gray-700 dark:text-white/70'"
              >
                <svg class="w-4 h-4" :fill="isBookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
                {{ isBookmarked ? 'Bookmarked' : 'Bookmark' }}
              </button>

              <!-- Start / View Tracking buttons (unchanged) -->
              <button
                @click="startApplication"
                v-if="!application"
                class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm hover:bg-blue-700 transition-colors"
              >
                Start Tracking
              </button>
              <button
                v-else
                @click="$inertia.visit(route('applications.index'))"
                class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm hover:bg-indigo-700 transition-colors"
              >
                View Tracking ({{ application.status }})
              </button>

              <!-- Copy link button (theme‑aware) -->
              <button
                @click="copyUrl"
                class="px-3 py-2 rounded-xl glass-surface hover:glass-elevated transition-colors text-gray-600 dark:text-white/50 hover:text-gray-900 dark:hover:text-white"
                title="Copy link"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                </svg>
              </button>

              <!-- Apply Now (unchanged) -->
              <a
                v-if="scholarship.source_url"
                :href="scholarship.source_url"
                target="_blank"
                rel="noopener noreferrer"
                class="group relative px-6 py-2.5 rounded-xl bg-gradient-to-r from-green-600 to-emerald-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(16,185,129,0.4)] hover:-translate-y-0.5 flex items-center gap-2"
              >
                <span class="relative z-10 flex items-center gap-2">
                  Apply Now
                  <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                  </svg>
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-green-400 to-emerald-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
              </a>
            </div>
          </GlassCard>
        </div>

        <!-- Description (formatted HTML, links stripped, Markdown bold and link text highlighted) -->
        <div class="perspective-1000">
          <GlassCard variant="elevated" class="p-6 md:p-8 rounded-3xl transform rotate-y-1 transition-all duration-700 hover:rotate-y-0">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2.5">
              <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Description
            </h2>
            <div
              class="prose prose-sm max-w-none text-gray-700 dark:text-gray-300 scholarship-description"
              v-html="formattedDescription"
            ></div>
          </GlassCard>
        </div>

        <!-- Match Breakdown -->
        <div v-if="matchScore && matchScore.category_scores" class="perspective-1000">
          <GlassCard variant="elevated" class="p-6 md:p-8 rounded-3xl transform -rotate-y-2 transition-all duration-700 hover:rotate-y-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2.5">
              <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              Match Breakdown
            </h2>
            <div class="flex flex-col lg:flex-row items-center gap-6">
              <RadarChart
                :categories="Object.keys(matchScore.category_scores)"
                :data="Object.values(matchScore.category_scores)"
                :size="250"
                color="rgba(59,130,246,0.4)"
              />
              <div class="text-sm text-gray-600 dark:text-white/40 space-y-2">
                <p v-for="(score, cat) in matchScore.category_scores" :key="cat">
                  <span class="capitalize text-gray-900 dark:text-white/80">{{ cat }}:</span> {{ score }}%
                </p>
              </div>
            </div>
          </GlassCard>
        </div>

        <!-- Application Journey Tracker -->
        <div v-if="application" class="perspective-1000">
          <GlassCard variant="elevated" class="p-6 md:p-8 rounded-3xl transform rotate-y-1 transition-all duration-700 hover:rotate-y-0">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2.5">
              <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
              Application Journey
            </h2>
            <JourneyTracker
              :steps="journeySteps"
              @update:steps="updateJourneySteps"
            />
            <p class="text-sm text-red-400 mt-4">Deadline: {{ formattedDeadline }}</p>
          </GlassCard>
        </div>

        <!-- Community Reviews -->
        <div class="perspective-1000">
          <GlassCard variant="elevated" class="p-6 md:p-8 rounded-3xl transform -rotate-y-1 transition-all duration-700 hover:rotate-y-0">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2.5">
              <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
              </svg>
              Community Reviews
            </h2>
            <div v-if="reviews.length" class="space-y-4">
              <div v-for="review in reviews" :key="review.id" class="border-b border-gray-200 dark:border-white/5 pb-3 last:border-0">
                <div class="flex items-center gap-2">
                  <span class="text-gray-900 dark:text-white text-sm font-medium">{{ review.user_name }}</span>
                  <div class="flex text-yellow-400 text-xs">
                    <span v-for="n in 5" :key="n" :class="n <= review.rating ? 'opacity-100' : 'opacity-30'">★</span>
                  </div>
                  <span class="text-xs text-gray-500 dark:text-white/30">{{ review.created_at }}</span>
                </div>
                <p class="text-sm text-gray-700 dark:text-white/60 mt-1">{{ review.comment }}</p>
              </div>
            </div>
            <div v-else class="text-gray-500 dark:text-white/50 text-sm">No reviews yet.</div>
            <div v-if="canReview" class="mt-4">
              <form @submit.prevent="submitReview" class="space-y-2">
                <div class="flex items-center gap-1 text-yellow-400">
                  <button type="button" v-for="n in 5" :key="n" @click="newRating = n" class="text-xl">
                    {{ n <= newRating ? '★' : '☆' }}
                  </button>
                </div>
                <GlassTextarea v-model="newComment" placeholder="Write your review..." rows="2" />
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm hover:bg-indigo-700 transition-colors">
                  Submit Review
                </button>
              </form>
            </div>
          </GlassCard>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import MatchGauge from '@/Components/MatchGauge.vue';
import RadarChart from '@/Components/RadarChart.vue';
import JourneyTracker from '@/Components/JourneyTracker.vue';
import GlassTextarea from '@/Components/GlassTextarea.vue';

const page = usePage();

const props = defineProps({
  scholarship: Object,
  matchScore: Object,
  application: Object,
  canReview: Boolean,
  reviews: Array,
});

const bookmarkedIds = ref(page.props.bookmarkedIds || []);
const isBookmarked = computed(() => bookmarkedIds.value.includes(props.scholarship?.id));

const journeySteps = ref(props.application?.checklist
  ? props.application.checklist.map((t, i) => ({
      id: `step-${i}`,
      label: t.task,
      completed: t.completed,
      tasks: [],
    }))
  : []);

// Build tag list from scholarship.parsed_requirements
const tagList = computed(() => {
  const reqs = props.scholarship?.parsed_requirements ?? {};
  return {
    academic_levels: reqs.study_levels ?? (reqs.academic_level ? [reqs.academic_level] : []),
    majors: reqs.majors ?? [],
    countries: reqs.countries ?? [],
    host_institution: reqs.host_institution ?? null,
    duration: reqs.duration ?? null,
    benefit_type: reqs.benefit_type ?? null,
    english_proficiency: reqs.english_proficiency ?? null,
    minimum_gpa: reqs.minimum_gpa ?? null,
  };
});

const benefitTagClass = computed(() => {
  const type = tagList.value.benefit_type?.toLowerCase() ?? '';
  if (type.includes('fully')) return 'bg-green-500/20 text-green-400';
  if (type.includes('partial')) return 'bg-yellow-500/20 text-yellow-400';
  return '';
});

// Format deadline
const formattedDeadline = computed(() => {
  if (!props.scholarship?.deadline) return '';
  try {
    return new Date(props.scholarship.deadline).toLocaleDateString('en-US', {
      year: 'numeric', month: 'long', day: 'numeric'
    });
  } catch { return props.scholarship.deadline; }
});

// Check if deadline is within 7 days
const isDeadlineUrgent = computed(() => {
  if (!props.scholarship?.deadline) return false;
  const now = new Date();
  const deadline = new Date(props.scholarship.deadline);
  const diffDays = Math.ceil((deadline - now) / (1000 * 60 * 60 * 24));
  return diffDays <= 7 && diffDays > 0;
});

const deadlineBadgeClass = computed(() => {
  if (isDeadlineUrgent.value) {
    return 'bg-red-500/90 text-white border border-red-400 animate-pulse';
  }
  return 'bg-white/10 text-gray-800 dark:text-white border border-gray-300 dark:border-white/20';
});

// Format description: strip links but highlight their text, convert **bold** to <strong>, strip remaining HTML tags
const formattedDescription = computed(() => {
  let text = props.scholarship?.description ?? '';
  if (!text) return '';

  // 1. Convert Markdown-style links [text](url) → highlighted badge (no link)
  text = text.replace(/\[([^\]]+)\]\([^)]+\)/g, '<span class="desc-highlight">$1</span>');

  // 2. Convert **bold** markers to <strong>
  text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

  // 3. Strip any remaining HTML tags that aren't allowed (only keep <strong>, <span>, <br>, <p>, <ul>, <ol>, <li>, <em>)
  // We'll sanitize by removing all tags except those.
  // Simple approach: allowlist certain tags
  const allowedTags = ['strong', 'span', 'br', 'p', 'ul', 'ol', 'li', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'table', 'thead', 'tbody', 'tr', 'th', 'td'];
  const tagPattern = new RegExp(`<(?!\/?(${allowedTags.join('|')})\\b)[^>]*>`, 'gi');
  text = text.replace(tagPattern, '');

  return text;
});

const newRating = ref(0);
const newComment = ref('');

async function toggleBookmark() {
  try {
    const res = await axios.post(route('bookmarks.toggle', props.scholarship.id));
    if (res.data.status === 'bookmarked') {
      bookmarkedIds.value.push(props.scholarship.id);
    } else {
      bookmarkedIds.value = bookmarkedIds.value.filter(id => id !== props.scholarship.id);
    }
  } catch (e) {
    console.error('Bookmark toggle error:', e);
  }
}

function startApplication() {
  try {
    router.post(route('applications.store'), {
      scholarship_id: props.scholarship.id,
    });
  } catch (e) {
    console.error('Start application error:', e);
  }
}

function copyUrl() {
  navigator.clipboard.writeText(window.location.href);
}

function updateJourneySteps(steps) {
  journeySteps.value = steps;
  const checklist = steps.map(s => ({ task: s.label, completed: s.completed }));
  axios.put(route('applications.update', props.application.id), { checklist })
    .catch(err => console.error('Update checklist error:', err));
}

async function submitReview() {
  if (!newRating.value || !newComment.value.trim()) return;
  try {
    await axios.post('/api/reviews', {
      scholarship_id: props.scholarship.id,
      rating: newRating.value,
      comment: newComment.value,
    });
    props.reviews.push({
      id: Date.now(),
      user_name: page.props.auth.user?.name || 'You',
      rating: newRating.value,
      comment: newComment.value,
      created_at: new Date().toLocaleDateString(),
    });
    newRating.value = 0;
    newComment.value = '';
  } catch (e) {
    console.error('Review submit error:', e);
  }
}

function filterByTag(tag) {
  router.get(route('scholarships.index'), { category: tag }, { preserveState: false });
}
</script>

<style scoped>
/* Theme‑aware glass utilities */
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

.glass-tag {
  background: rgba(59, 130, 246, 0.15);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(59, 130, 246, 0.25);
  color: #1e40af;
  white-space: nowrap;
}
.dark .glass-tag {
  background: rgba(59, 130, 246, 0.2);
  border-color: rgba(59, 130, 246, 0.4);
  color: #93c5fd;
}

/* Highlighted link text (stripped of actual links) */
.desc-highlight {
  display: inline-block;
  padding: 0 0.5rem;
  border-radius: 9999px;
  background: rgba(59, 130, 246, 0.1);
  color: #1e40af;
  font-weight: 500;
  font-size: 0.9em;
  margin-right: 0.25rem;
}
.dark .desc-highlight {
  background: rgba(59, 130, 246, 0.2);
  color: #93c5fd;
}

/* Description HTML styling */
.scholarship-description :deep(p) {
  margin-bottom: 0.75rem;
  line-height: 1.6;
}
.scholarship-description :deep(strong),
.scholarship-description :deep(b) {
  font-weight: 600;
  color: inherit;
}
.scholarship-description :deep(h1),
.scholarship-description :deep(h2),
.scholarship-description :deep(h3),
.scholarship-description :deep(h4) {
  margin-top: 1.25rem;
  margin-bottom: 0.5rem;
  font-weight: 600;
}
.scholarship-description :deep(ul),
.scholarship-description :deep(ol) {
  margin-bottom: 0.75rem;
  padding-left: 1.25rem;
}
.scholarship-description :deep(li) {
  margin-bottom: 0.25rem;
}
.scholarship-description :deep(table) {
  width: 100%;
  margin-bottom: 1rem;
  border-collapse: collapse;
}
.scholarship-description :deep(th),
.scholarship-description :deep(td) {
  padding: 0.5rem;
  border: 1px solid rgba(0,0,0,0.1);
  text-align: left;
}
.dark .scholarship-description :deep(th),
.dark .scholarship-description :deep(td) {
  border-color: rgba(255,255,255,0.1);
}

.perspective-1000 { perspective: 1000px; }
.-rotate-y-2 { transform: rotateY(-2deg) rotateX(1deg); }
.-rotate-y-1 { transform: rotateY(-1deg); }
.rotate-y-1 { transform: rotateY(1deg) rotateX(0.5deg); }
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
  .perspective-1000 { perspective: none; }
  .-rotate-y-2, .-rotate-y-1, .rotate-y-1, .rotate-y-0 { transform: none !important; }
}
</style>