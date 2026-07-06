<template>
  <AppLayout>
    <div
      class="relative min-h-screen overflow-hidden p-4 md:p-6 space-y-6"
      :class="{ 'reduce-motion': reduceMotion }"
    >
      <!-- Animated Background Particles -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/4 right-1/4 w-96 h-96 rounded-full bg-blue-600/5 blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 left-1/4 w-[500px] h-[500px] rounded-full bg-indigo-600/5 blur-3xl animate-pulse-slow" style="animation-delay: 1s"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-purple-600/3 blur-3xl"></div>

        <!-- Floating Orbs -->
        <div class="absolute top-20 right-10 w-3 h-3 rounded-full bg-blue-400/20 animate-float"></div>
        <div class="absolute bottom-40 right-1/4 w-4 h-4 rounded-full bg-indigo-400/20 animate-float" style="animation-delay: 0.7s"></div>
        <div class="absolute top-1/3 left-10 w-2 h-2 rounded-full bg-purple-400/20 animate-float" style="animation-delay: 1.4s"></div>
        <div class="absolute bottom-20 left-1/3 w-3 h-3 rounded-full bg-pink-400/20 animate-float" style="animation-delay: 2s"></div>
      </div>

      <!-- Welcome & Profile Completion (3D Glass Card) -->
      <div class="relative perspective-1000">
        <GlassCard class="p-6 md:p-8 rounded-3xl transform -rotate-y-6 transition-all duration-700 hover:rotate-y-3 hover:shadow-2xl glass-welcome">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
              <!-- Avatar with 3D effect -->
              <div class="relative w-16 h-16 rounded-full glass-elevated flex items-center justify-center overflow-hidden ring-2 ring-gray-300 dark:ring-white/10 group-hover:ring-gray-400 dark:group-hover:ring-white/20 transition-all">
                <img
                  v-if="userAvatar"
                  :src="userAvatar"
                  class="w-full h-full object-cover"
                  alt="Avatar"
                />
                <svg v-else class="w-8 h-8 text-gray-500 dark:text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <!-- Online indicator -->
                <div class="absolute bottom-0.5 right-0.5 w-3.5 h-3.5 rounded-full bg-green-500 border-2 border-white/20 dark:border-gray-900/20 shadow-[0_0_12px_rgba(74,222,128,0.4)]">
                  <div class="absolute inset-0 rounded-full bg-green-400 animate-ping opacity-75"></div>
                </div>
              </div>

              <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                  Welcome back, <span class="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">{{ userName }}</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-white/40 mt-1">Here is your scholarship journey overview.</p>
              </div>
            </div>

            <div class="flex items-center gap-4">
              <div class="text-right">
                <p class="text-xs text-gray-500 dark:text-white/40">Profile completion</p>
                <div class="w-36 md:w-44 h-2.5 bg-gray-200 dark:bg-white/5 rounded-full mt-1 overflow-hidden shadow-inner">
                  <div
                    class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-700"
                    :style="{ width: profileCompletion + '%' }"
                  >
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-30 blur-sm"></div>
                  </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-white/30 mt-1 flex items-center justify-end gap-1.5">
                  <span>{{ profileCompletion }}%</span>
                  <span class="text-[10px] text-gray-400 dark:text-white/20">complete</span>
                </p>
              </div>

              <!-- Dynamic button: Complete Profile / Edit Profile -->
              <Link
                v-if="profileCompletion < 100"
                :href="route('profile.complete')"
                class="group relative px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(59,130,246,0.3)] hover:-translate-y-0.5 focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-400"
              >
                <span class="relative z-10 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                  Complete Profile
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
              </Link>
              <Link
                v-else
                :href="route('profile.edit')"
                class="group relative px-5 py-2.5 rounded-xl bg-gradient-to-r from-green-600 to-emerald-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(16,185,129,0.3)] hover:-translate-y-0.5 focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-400"
              >
                <span class="relative z-10 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                  Edit Profile
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-green-400 to-emerald-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
              </Link>
            </div>
          </div>
        </GlassCard>
      </div>

      <!-- Stats Row with 3D Hover -->
      <div id="dashboard-stats" class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div
          v-for="(stat, index) in formattedStats"
          :key="stat.label"
          class="group perspective-1000"
          :style="{ animationDelay: (index * 100) + 'ms' }"
        >
          <GlassCard
            class="p-4 md:p-5 text-center rounded-2xl transform -rotate-y-3 transition-all duration-500 hover:rotate-y-0 hover:-translate-y-1 hover:shadow-2xl focus-visible:ring-2 focus-visible:ring-blue-400"
            style="transform-style: preserve-3d;"
            tabindex="0"
          >
            <div class="relative">
              <p class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white tabular-nums">
                {{ stat.value }}
              </p>
              <p class="text-xs text-gray-500 dark:text-white/40 mt-1 tracking-wide uppercase">{{ stat.label }}</p>

              <div class="absolute -bottom-0.5 left-1/4 right-1/4 h-[1px] bg-gradient-to-r from-transparent via-gray-300 dark:via-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </div>
          </GlassCard>
        </div>
      </div>

      <!-- Top Matches + Upcoming Deadlines (3D Split) -->
      <div class="grid lg:grid-cols-2 gap-6">
        <!-- Top Matches -->
        <div class="perspective-1000">
          <GlassCard
            variant="elevated"
            class="p-6 md:p-8 rounded-3xl transform -rotate-y-6 transition-all duration-700 hover:rotate-y-3 hover:shadow-2xl h-full"
          >
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2.5">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Top Matches
              </h2>
              <div class="flex items-center gap-2">
                <span v-if="refreshingMatches" class="animate-spin w-3.5 h-3.5 border-2 border-blue-400/30 border-t-blue-400 rounded-full"></span>
                <span class="text-[10px] text-gray-400 dark:text-white/20 uppercase tracking-wider">Live</span>
              </div>
            </div>

            <div v-if="dynamicTopMatches.length === 0" class="text-gray-500 dark:text-white/40 text-sm py-8 text-center">
              <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
              No matches yet. Complete your profile to see scholarship matches.
            </div>

            <div v-else class="space-y-3">
              <Link
                v-for="match in dynamicTopMatches"
                :key="match.id"
                :href="route('scholarships.show', match.id)"
                class="group block p-4 rounded-xl bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 transition-all duration-300 hover:-translate-x-1 focus-visible:ring-2 focus-visible:ring-blue-400 focus-visible:rounded-lg"
              >
                <div class="flex items-center justify-between">
                  <div class="flex-1 min-w-0">
                    <p class="text-gray-900 dark:text-white font-medium truncate group-hover:text-gray-700 dark:group-hover:text-white/90 transition-colors">
                      {{ match.title }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-white/30 mt-0.5 flex items-center gap-2">
                      <span>{{ match.provider }}</span>
                      <span class="w-1 h-1 rounded-full bg-gray-400 dark:bg-white/20"></span>
                      <span>{{ match.country }}</span>
                    </p>
                  </div>
                  <div class="ml-4 flex items-center gap-3">
                    <span class="text-xs text-gray-400 dark:text-white/30 hidden sm:inline">
                      {{ formatDeadline(match.deadline) }}
                    </span>
                    <div class="flex items-center gap-2.5">
                      <span
                        class="text-lg font-bold tabular-nums"
                        :class="{
                          'text-green-400': match.score >= 80,
                          'text-yellow-400': match.score >= 60 && match.score < 80,
                          'text-red-400': match.score < 60
                        }"
                      >
                        {{ match.score }}%
                      </span>
                      <!-- Mini progress bar -->
                      <div class="w-12 h-1.5 rounded-full bg-gray-200 dark:bg-white/5 overflow-hidden">
                        <div
                          class="h-full rounded-full transition-all duration-700"
                          :class="{
                            'bg-gradient-to-r from-green-400 to-emerald-400': match.score >= 80,
                            'bg-gradient-to-r from-yellow-400 to-orange-400': match.score >= 60 && match.score < 80,
                            'bg-gradient-to-r from-red-400 to-orange-400': match.score < 60
                          }"
                          :style="{ width: match.score + '%' }"
                        ></div>
                      </div>
                    </div>
                  </div>
                </div>
              </Link>
            </div>
          </GlassCard>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="perspective-1000">
          <GlassCard
            variant="elevated"
            class="p-6 md:p-8 rounded-3xl transform rotate-y-6 transition-all duration-700 hover:rotate-y-3 hover:shadow-2xl h-full"
          >
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2.5">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Upcoming Deadlines
              </h2>
              <span class="text-[10px] text-red-400/40 uppercase tracking-wider flex items-center gap-1.5">
                <span class="relative flex h-1.5 w-1.5">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-red-500"></span>
                </span>
                Urgent
              </span>
            </div>

            <div v-if="dynamicUpcomingDeadlines.length === 0" class="text-gray-500 dark:text-white/40 text-sm py-8 text-center">
              <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              No upcoming deadlines in the next 7 days. Enjoy the break!
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="item in dynamicUpcomingDeadlines"
                :key="item.id"
                class="group p-4 rounded-xl bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 transition-all duration-300 hover:-translate-x-1"
              >
                <div class="flex items-center justify-between">
                  <div class="flex-1 min-w-0">
                    <p class="text-gray-900 dark:text-white font-medium truncate group-hover:text-gray-700 dark:group-hover:text-white/90 transition-colors">
                      {{ item.title }}
                    </p>
                    <p class="text-xs text-red-500 dark:text-red-400/80 mt-0.5 flex items-center gap-1.5">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span class="font-mono">{{ formatDeadline(item.deadline) }}</span>
                    </p>
                  </div>
                  <div class="ml-4 flex items-center gap-2">
                    <span class="text-[10px] text-gray-400 dark:text-white/20 uppercase tracking-wider">
                      Due in {{ getDaysUntil(item.deadline) }} days
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </GlassCard>
        </div>
      </div>

      <!-- Radar Chart with 3D Container -->
      <div class="perspective-1000" v-if="hasCategoryData">
        <GlassCard
          variant="elevated"
          class="p-6 md:p-8 rounded-3xl transform -rotate-y-2 transition-all duration-700 hover:rotate-y-1 hover:shadow-2xl"
        >
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2.5">
              <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              Your Match Profile
            </h2>
            <span class="text-[10px] text-gray-400 dark:text-white/20 uppercase tracking-wider">Radar</span>
          </div>

          <div class="flex justify-center">
            <RadarChart
              :categories="categoryKeys"
              :data="categoryValues"
              :size="320"
              color="rgba(59,130,246,0.4)"
              borderColor="rgba(59,130,246,0.8)"
            />
          </div>
        </GlassCard>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import RadarChart from '@/Components/RadarChart.vue';

const page = usePage();

const props = defineProps({
  user: Object,
  profileCompletion: { type: Number, default: 0 },
  topMatches: { type: Array, default: () => [] },
  upcomingDeadlines: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  unreadNotifications: { type: Number, default: 0 },
  categoryAverages: { type: Object, default: () => ({}) },
});

// User display helpers
const userName = computed(() => props.user?.name ?? 'User');
const userAvatar = computed(() => {
  const avatar = props.user?.avatar_url;
  if (!avatar) return null;
  if (avatar.startsWith('http')) return avatar;
  return '/storage/' + avatar;
});

// Dynamic data refs (initialized from props, then updated by polling)
const dynamicTopMatches = ref(props.topMatches || []);
const dynamicUpcomingDeadlines = ref(props.upcomingDeadlines || []);
const dynamicStats = ref(props.stats || {
  activeScholarships: 0,
  documentsCount: 0,
  applicationsInProgress: 0,
  avgMatchScore: 0,
});
const refreshingMatches = ref(false);

const formattedStats = computed(() => {
  const s = dynamicStats.value;
  return [
    { label: 'Active Scholarships', value: s.activeScholarships ?? 0 },
    { label: 'Documents', value: s.documentsCount ?? 0 },
    { label: 'Applications', value: s.applicationsInProgress ?? 0 },
    { label: 'Avg Match', value: (s.avgMatchScore ?? 0) + '%' },
  ];
});

const hasCategoryData = computed(() => {
  return props.categoryAverages && Object.keys(props.categoryAverages).length > 0;
});

const categoryKeys = computed(() => Object.keys(props.categoryAverages));
const categoryValues = computed(() => Object.values(props.categoryAverages).map(v => Number(v)));

// Formatting helpers
function formatDeadline(dateStr) {
  try {
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return dateStr;
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  } catch { return dateStr; }
}

function getDaysUntil(dateStr) {
  try {
    const target = new Date(dateStr);
    const now = new Date();
    const diff = Math.ceil((target - now) / (1000 * 60 * 60 * 24));
    return Math.max(diff, 0);
  } catch { return '?'; }
}

// Polling for dynamic updates
let pollTimer = null;

async function fetchLatestMatches() {
  refreshingMatches.value = true;
  try {
    const { data } = await axios.get('/api/top-matches?limit=3');
    if (Array.isArray(data)) {
      dynamicTopMatches.value = data;
    }
  } catch (e) {
    console.error('Failed to fetch top matches:', e);
  } finally {
    refreshingMatches.value = false;
  }
}

async function fetchStats() {
  try {
    const { data } = await axios.get('/api/stats/live');
    if (data) {
      dynamicStats.value = {
        activeScholarships: data.activeScholarships ?? 0,
        documentsCount: dynamicStats.value.documentsCount,
        applicationsInProgress: dynamicStats.value.applicationsInProgress,
        avgMatchScore: data.avgMatchScore ?? 0,
      };
    }
  } catch (e) {
    // Silently fail – keep old stats
  }
}

async function fetchUpcomingDeadlines() {
  try {
    const { data } = await axios.get('/api/upcoming-deadlines');
    if (Array.isArray(data)) {
      dynamicUpcomingDeadlines.value = data;
    }
  } catch (e) {
    console.error('Failed to fetch upcoming deadlines:', e);
  }
}

function startPolling() {
  fetchLatestMatches();
  fetchStats();
  fetchUpcomingDeadlines();

  pollTimer = setInterval(() => {
    fetchLatestMatches();
    fetchStats();
    fetchUpcomingDeadlines();
  }, 30000);
}

// Accessibility: reduced motion detection
const reduceMotion = ref(false);

onMounted(() => {
  startPolling();

  const mq = window.matchMedia('(prefers-reduced-motion: reduce)');
  reduceMotion.value = mq.matches;
  const handler = (event) => { reduceMotion.value = event.matches; };
  mq.addEventListener('change', handler);
  onUnmounted(() => mq.removeEventListener('change', handler));
});

onUnmounted(() => {
  clearInterval(pollTimer);
});
</script>

<style scoped>
/* Premium Glass Welcome Card */
.glass-welcome {
  backdrop-filter: blur(24px);
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.dark .glass-welcome {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.1);
}

/* 3D and animation utilities */
.perspective-1000 { perspective: 1000px; }
.-rotate-y-6 { transform: rotateY(-6deg) rotateX(2deg); }
.rotate-y-6 { transform: rotateY(6deg) rotateX(2deg); }
.-rotate-y-3 { transform: rotateY(-3deg); }
.rotate-y-0 { transform: rotateY(0deg); }
.-rotate-y-2 { transform: rotateY(-2deg) rotateX(1deg); }
.rotate-y-1 { transform: rotateY(1deg) rotateX(0.5deg); }

/* Animations */
@keyframes float {
  0%, 100% { transform: translateY(0px) scale(1); opacity: 0.2; }
  50% { transform: translateY(-20px) scale(1.2); opacity: 0.6; }
}
.animate-float { animation: float 6s ease-in-out infinite; }

@keyframes pulse-slow {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.1); }
}
.animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }

@keyframes ping {
  0%, 100% { transform: scale(1); opacity: 0.75; }
  50% { transform: scale(1.5); opacity: 0; }
}
.animate-ping { animation: ping 2s ease-in-out infinite; }

/* Reduced motion: disable all animations & transitions */
.reduce-motion *,
.reduce-motion *::before,
.reduce-motion *::after {
  animation: none !important;
  transition: none !important;
}

/* Responsive tweaks – ensure flat cards on mobile */
@media (max-width: 768px) {
  .perspective-1000 { perspective: none; }
  .-rotate-y-6, .rotate-y-6, .-rotate-y-3, .-rotate-y-2, .rotate-y-1 {
    transform: none !important;
  }
}
</style>