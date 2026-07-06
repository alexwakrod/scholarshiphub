<template>
  <AppLayout>
    <div v-if="scholarship" class="p-6 max-w-5xl mx-auto space-y-6">
      <!-- Header -->
      <GlassCard variant="elevated" class="p-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
          <div class="flex-1">
            <h1 class="text-2xl font-bold text-white">{{ scholarship.title }}</h1>
            <div class="flex items-center gap-2 mt-2 text-sm text-white/60">
              <span>{{ scholarship.provider }}</span>
              <span>·</span>
              <span>{{ scholarship.country }}</span>
              <span v-if="scholarship.amount" class="text-accent font-semibold">· ${{ scholarship.amount }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-2 mt-3">
              <span
                v-for="tag in requirementTags"
                :key="tag"
                class="px-2 py-0.5 rounded-full text-xs bg-white/10 text-white/70 cursor-pointer hover:bg-white/20"
                @click="filterByTag(tag)"
              >
                {{ tag }}
              </span>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <MatchGauge v-if="matchScore" :score="matchScore.overall_score" :size="80" />
            <div v-else class="text-white/50 text-sm">Complete your profile to see match.</div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap items-center gap-3 mt-6">
          <button
            @click="toggleBookmark"
            class="px-4 py-2 rounded-lg flex items-center gap-2 text-sm transition-colors"
            :class="isBookmarked ? 'bg-yellow-500/20 text-yellow-300 hover:bg-yellow-500/30' : 'bg-white/10 text-white hover:bg-white/20'"
          >
            <svg class="w-4 h-4" :fill="isBookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
            </svg>
            {{ isBookmarked ? 'Bookmarked' : 'Bookmark' }}
          </button>
          <button
            @click="startApplication"
            v-if="!application"
            class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700"
          >
            Start Application
          </button>
          <button
            v-else
            @click="$inertia.visit(route('applications.index'))"
            class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700"
          >
            View Application ({{ application.status }})
          </button>
          <button
            @click="copyUrl"
            class="px-3 py-2 rounded-lg bg-white/10 text-white/70 text-sm hover:bg-white/20"
            title="Copy link"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
            </svg>
          </button>
        </div>
      </GlassCard>

      <!-- Description -->
      <GlassCard variant="elevated" class="p-6">
        <h2 class="text-lg font-semibold text-white mb-3">Description</h2>
        <div class="prose prose-invert max-w-none text-sm text-white/70" v-html="scholarship.description"></div>
        <p v-if="scholarship.source_url" class="mt-4">
          <a :href="scholarship.source_url" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:underline text-sm">
            View original source →
          </a>
        </p>
      </GlassCard>

      <!-- Match breakdown -->
      <GlassCard v-if="matchScore && matchScore.category_scores" variant="elevated" class="p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Match Breakdown</h2>
        <div class="flex flex-col lg:flex-row items-center gap-6">
          <RadarChart
            :categories="Object.keys(matchScore.category_scores)"
            :data="Object.values(matchScore.category_scores)"
            :size="250"
            color="rgba(59,130,246,0.4)"
          />
          <div class="text-sm text-white/70 space-y-2">
            <p v-for="(score, cat) in matchScore.category_scores" :key="cat">
              <span class="capitalize text-white/80">{{ cat }}:</span> {{ score }}%
            </p>
          </div>
        </div>
      </GlassCard>

      <!-- Application journey tracker -->
      <GlassCard v-if="application" variant="elevated" class="p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Application Journey</h2>
        <JourneyTracker
          :steps="journeySteps"
          @update:steps="updateJourneySteps"
        />
        <p class="text-sm text-red-400 mt-4">Deadline: {{ formatDeadline(scholarship.deadline) }}</p>
      </GlassCard>

      <!-- Community reviews -->
      <GlassCard variant="elevated" class="p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Community Reviews</h2>
        <div v-if="reviews.length">
          <div v-for="review in reviews" :key="review.id" class="border-b border-white/5 py-3 last:border-0">
            <div class="flex items-center gap-2">
              <span class="text-white text-sm font-medium">{{ review.user_name }}</span>
              <div class="flex text-yellow-400 text-xs">
                <span v-for="n in 5" :key="n" :class="n <= review.rating ? 'opacity-100' : 'opacity-30'">★</span>
              </div>
              <span class="text-xs text-white/40">{{ review.created_at }}</span>
            </div>
            <p class="text-sm text-white/70 mt-1">{{ review.comment }}</p>
          </div>
        </div>
        <div v-else class="text-white/50 text-sm">No reviews yet.</div>
        <div v-if="canReview" class="mt-4">
          <form @submit.prevent="submitReview" class="space-y-2">
            <div class="flex items-center gap-1 text-yellow-400">
              <button type="button" v-for="n in 5" :key="n" @click="newRating = n" class="text-xl">
                {{ n <= newRating ? '★' : '☆' }}
              </button>
            </div>
            <GlassTextarea v-model="newComment" placeholder="Write your review..." rows="2" />
            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
              Submit Review
            </button>
          </form>
        </div>
      </GlassCard>
    </div>
    <!-- Loading/error fallback -->
    <div v-else class="flex items-center justify-center min-h-[60vh]">
      <p v-if="errorMsg" class="text-red-400">{{ errorMsg }}</p>
      <SkeletonLoader v-else type="card" :count="3" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import MatchGauge from '@/Components/MatchGauge.vue';
import RadarChart from '@/Components/RadarChart.vue';
import JourneyTracker from '@/Components/JourneyTracker.vue';
import GlassTextarea from '@/Components/GlassTextarea.vue';
import SkeletonLoader from '@/Components/SkeletonLoader.vue';

const page = usePage();

const props = defineProps({
  scholarship: Object,
  matchScore: Object,
  application: Object,
  canReview: Boolean,
  reviews: Array,
});

const errorMsg = ref('');

// Bookmark state from shared props or local?
const bookmarkedIds = ref(page.props.bookmarkedIds || []);
const isBookmarked = computed(() => bookmarkedIds.value.includes(props.scholarship?.id));

const journeySteps = ref(props.application?.checklist
  ? props.application.checklist.map((t, i) => ({
      id: `step-${i}`,
      label: t.task,
      completed: t.completed,
      tasks: [], // we don't have sub-tasks
    }))
  : []);

const requirementTags = computed(() => {
  const parsed = props.scholarship?.parsed_requirements;
  if (!parsed) return [];
  const tags = [];
  if (parsed.academic_level) tags.push(parsed.academic_level);
  if (parsed.minimum_gpa) tags.push(`GPA ≥ ${parsed.minimum_gpa}`);
  if (parsed.majors?.length) tags.push(...parsed.majors);
  if (parsed.english_proficiency) tags.push(parsed.english_proficiency);
  return tags.slice(0, 10);
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
  // Toast notification could be shown
}

function updateJourneySteps(steps) {
  journeySteps.value = steps;
  // Sync with backend
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
    // Refresh reviews locally
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

function formatDeadline(dateStr) {
  try {
    return new Date(dateStr).toLocaleDateString();
  } catch {
    return dateStr;
  }
}

onMounted(() => {
  if (!props.scholarship) {
    errorMsg.value = 'Scholarship not found.';
  }
});
</script>