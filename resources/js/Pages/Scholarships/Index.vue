<template>
  <AppLayout>
    <div class="relative min-h-screen overflow-hidden bg-gray-100 dark:bg-gray-950">
      <!-- Animated Background -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/4 right-1/4 w-96 h-96 rounded-full bg-blue-600/10 dark:bg-blue-600/5 blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 left-1/4 w-128 h-128 rounded-full bg-indigo-600/10 dark:bg-indigo-600/5 blur-3xl animate-pulse-slow" style="animation-delay: 1s"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-purple-600/5 dark:bg-purple-600/3 blur-3xl"></div>
        <div class="absolute top-20 right-10 w-3 h-3 rounded-full bg-blue-400/30 dark:bg-blue-400/20 animate-float"></div>
        <div class="absolute bottom-40 right-1/4 w-4 h-4 rounded-full bg-indigo-400/30 dark:bg-indigo-400/20 animate-float" style="animation-delay: 0.7s"></div>
        <div class="absolute top-1/3 left-10 w-2 h-2 rounded-full bg-purple-400/30 dark:bg-purple-400/20 animate-float" style="animation-delay: 1.4s"></div>
      </div>

      <!-- Glass Reflection Overlay -->
      <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-gray-400/30 dark:via-white/30 to-transparent"></div>
        <div class="absolute top-1/4 -right-1/4 w-1/2 h-64 bg-gradient-to-l from-blue-500/5 dark:from-blue-500/5 to-transparent blur-3xl rotate-12"></div>
        <div class="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-gray-400/10 dark:via-white/10 to-transparent"></div>
      </div>

      <div class="flex relative h-full">
        <!-- Main Content -->
        <main
          class="flex-1 p-4 md:p-6 overflow-y-auto relative transition-all duration-500"
          ref="mainScroll"
          @scroll="onScroll"
        >
          <PullToRefresh :onRefresh="refreshScholarships" :refreshThreshold="60">
            <!-- Top Bar with 3D -->
            <div class="perspective-1000">
              <div class="glass-top-bar p-4 rounded-2xl mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 transform -rotate-y-2 transition-all duration-500 hover:rotate-y-0 hover:-translate-y-1 hover:shadow-2xl hover:border-white/20">
                <div class="flex items-center gap-3">
                  <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">
                    <span class="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Scholarships</span>
                  </h1>
                  <span class="text-xs text-gray-500 dark:text-white/20 bg-gray-200/60 dark:bg-white/5 px-2 py-0.5 rounded-full">{{ scholarships.length }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <GlassSelect
                    v-model="filters.sort"
                    :options="sortOptions"
                    placeholder="Sort"
                    class="w-32"
                    :is-filter="true"
                  />
                  <div class="flex gap-1 p-1 rounded-xl glass-surface">
                    <button
                      @click="viewMode = 'grid'"
                      class="p-2 rounded-lg transition-all duration-300 hover:scale-105 active:scale-95"
                      :class="viewMode === 'grid' ? 'bg-white/10 dark:bg-white/20 text-gray-900 dark:text-white shadow-[0_0_20px_rgba(255,255,255,0.05)]' : 'text-gray-500 dark:text-white/20 hover:text-gray-900 dark:hover:text-white/50'"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                      </svg>
                    </button>
                    <button
                      @click="viewMode = 'list'"
                      class="p-2 rounded-lg transition-all duration-300 hover:scale-105 active:scale-95"
                      :class="viewMode === 'list' ? 'bg-white/10 dark:bg-white/20 text-gray-900 dark:text-white shadow-[0_0_20px_rgba(255,255,255,0.05)]' : 'text-gray-500 dark:text-white/20 hover:text-gray-900 dark:hover:text-white/50'"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Virtual Scroll List -->
            <div
              class="relative"
              :style="{ height: virtualContainerHeight + 'px' }"
            >
              <div
                v-for="item in visibleItems"
                :key="item.scholarship.id"
                class="absolute w-full transition-all duration-300 will-change-transform cursor-pointer"
                :style="{ top: item.top + 'px' }"
                @click="handleCardClick($event, item.scholarship.id)"
              >
                <ScholarshipCard
                  :scholarship="item.scholarship"
                  :bookmarked="bookmarkedIds.includes(item.scholarship.id)"
                  :matchScore="matchScores[item.scholarship.id]"
                  :viewMode="viewMode"
                  @toggle-bookmark="() => toggleBookmark(item.scholarship.id)"
                />
              </div>
            </div>

            <!-- Loading / Empty -->
            <div ref="sentinel" class="py-8 text-center">
              <div v-if="loadingMore" class="flex items-center justify-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/5 backdrop-blur-sm border border-white/10 flex items-center justify-center">
                  <svg class="animate-spin h-5 w-5 text-gray-500 dark:text-white/20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
                <span class="text-sm text-gray-500 dark:text-white/20">Loading more...</span>
              </div>
              <div v-else-if="!hasMore && scholarships.length > 0" class="text-sm text-gray-400 dark:text-white/10">All scholarships loaded</div>
              <div v-else-if="scholarships.length === 0 && !loadingMore" class="perspective-1000">
                <GlassCard variant="elevated" class="p-8 rounded-3xl transform -rotate-y-2 hover:-translate-y-1 hover:rotate-y-0 transition-all duration-500 text-center">
                  <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-white/5 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  <p class="text-gray-500 dark:text-white/20">No scholarships match your filters</p>
                  <button @click="resetFilters" class="mt-2 text-sm text-blue-400 hover:underline">Clear all filters</button>
                </GlassCard>
              </div>
            </div>
          </PullToRefresh>
        </main>

        <!-- Premium Floating Filter Panel (3D, no overflow-y-auto to prevent dropdown clipping) -->
        <div
          v-if="sidebarOpen"
          class="fixed top-[calc(64px+1.5rem)] right-4 z-30 w-[320px] perspective-1000"
        >
          <div
            class="glass-filter-panel rounded-3xl p-6 space-y-5 shadow-2xl transition-all duration-500 transform -rotate-y-3 hover:rotate-y-0 hover:-translate-y-1 hover:shadow-[0_20px_80px_rgba(0,0,0,0.25)]"
            style="overflow: visible;"
          >
            <!-- Header -->
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-blue-500/10 dark:bg-blue-400/10 flex items-center justify-center text-blue-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                  </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Filters</h2>
              </div>
              <button @click="sidebarOpen = false" class="text-gray-500 dark:text-white/40 hover:text-gray-900 dark:hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Essential Filters -->
            <GlassInputWrapper v-model="filters.search" label="Search" placeholder="Search scholarships..." :is-filter="true" />

            <GlassInputWrapper v-model="filters.category" label="Category" :is-filter="true">
              <GlassSelect v-model="filters.category" :options="categoryOptions" placeholder="All Categories" searchable :is-filter="true" />
            </GlassInputWrapper>

            <GlassInputWrapper v-model="filters.country" label="Country" :is-filter="true">
              <GlassSelect v-model="filters.country" :options="countryOptions" placeholder="All Countries" searchable :is-filter="true" />
            </GlassInputWrapper>

            <!-- AI Smart Filter -->
            <button
              @click="applyAIFilter"
              :disabled="aiFilterLoading"
              class="group relative w-full py-3 rounded-2xl bg-gradient-to-r from-blue-600/30 via-purple-600/30 to-pink-600/30 backdrop-blur-sm border border-white/10 text-white text-sm font-medium overflow-hidden transition-all duration-500 hover:shadow-[0_0_60px_rgba(168,85,247,0.2)] hover:-translate-y-1 hover:scale-[1.02] active:scale-95 disabled:opacity-50"
            >
              <span class="relative z-10 flex items-center justify-center gap-2.5">
                <span v-if="aiFilterLoading" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span>{{ aiFilterLoading ? 'Analyzing...' : 'AI Smart Filter' }}</span>
              </span>
              <div class="absolute inset-0 bg-gradient-to-r from-blue-400/10 via-purple-400/10 to-pink-400/10 opacity-0 group-hover:opacity-100 transition-opacity blur-xl"></div>
            </button>
            <p v-if="aiFilterReasoning" class="text-[10px] text-gray-500 dark:text-white/20 italic">{{ aiFilterReasoning }}</p>

            <!-- More Filters -->
            <button
              @click="showAdvancedFilters = true"
              class="w-full py-2.5 rounded-xl glass-surface hover:glass-elevated text-sm text-gray-700 dark:text-white/60 transition-all flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
              </svg>
              More Filters
              <span v-if="advancedActiveCount > 0" class="text-[10px] bg-blue-500 text-white rounded-full px-1.5 py-0.5">{{ advancedActiveCount }}</span>
            </button>
          </div>
        </div>

        <!-- Toggle button when sidebar is collapsed -->
        <button
          v-if="!sidebarOpen"
          @click="sidebarOpen = true"
          class="fixed top-[calc(64px+1.5rem)] right-4 z-30 w-12 h-12 rounded-2xl glass-filter-panel flex items-center justify-center text-gray-500 dark:text-white/40 hover:text-gray-900 dark:hover:text-white transition shadow-lg"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
          </svg>
        </button>

        <!-- Advanced Filters Modal -->
        <GlassModal v-model="showAdvancedFilters" @close="showAdvancedFilters = false">
          <div class="perspective-1000">
            <div class="transform -rotate-y-3 hover:rotate-y-0 transition-all duration-700 space-y-5 p-2">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">Advanced Filters</h3>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <GlassInputWrapper v-model="advanced.amount_min" label="Min Amount ($)" :is-filter="true">
                  <GlassNumberInput v-model="advanced.amount_min" placeholder="0" :min="0" :is-filter="true" />
                </GlassInputWrapper>

                <GlassInputWrapper v-model="advanced.amount_max" label="Max Amount ($)" :is-filter="true">
                  <GlassNumberInput v-model="advanced.amount_max" placeholder="No limit" :min="0" :is-filter="true" />
                </GlassInputWrapper>

                <GlassInputWrapper v-model="advanced.deadline_before" label="Deadline Before" :is-filter="true">
                  <GlassDatePicker v-model="advanced.deadline_before" placeholder="Select date" />
                </GlassInputWrapper>

                <GlassInputWrapper label="Min Match Score" :is-filter="true">
                  <div class="space-y-2">
                    <GlassRangeSlider v-model="advanced.min_match" :min="0" :max="100" :step="1" />
                    <div class="flex justify-between text-xs text-gray-500 dark:text-white/30">
                      <span>0%</span>
                      <span class="font-mono">{{ advanced.min_match }}%</span>
                      <span>100%</span>
                    </div>
                  </div>
                </GlassInputWrapper>
              </div>

              <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-white/5">
                <button
                  @click="showAdvancedFilters = false"
                  class="px-4 py-2 rounded-xl bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-white text-sm"
                >
                  Cancel
                </button>
                <button
                  @click="applyAdvancedFilters"
                  class="px-6 py-2 rounded-xl bg-blue-600 text-white text-sm hover:bg-blue-700"
                >
                  Apply Filters
                </button>
              </div>
            </div>
          </div>
        </GlassModal>

        <!-- Mobile Filter Toggle (FAB) -->
        <div class="lg:hidden fixed bottom-6 right-6 z-50">
          <button
            @click="sidebarOpen = !sidebarOpen"
            class="group relative w-14 h-14 rounded-full bg-gradient-to-r from-blue-600/40 to-indigo-600/40 backdrop-blur-xl border border-white/10 shadow-[0_0_60px_rgba(59,130,246,0.15)] flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95"
          >
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span v-if="totalActiveFilterCount > 0" class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-blue-500 text-[10px] font-bold text-white flex items-center justify-center">
              {{ totalActiveFilterCount }}
            </span>
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import debounce from 'lodash/debounce';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassSelect from '@/Components/GlassSelect.vue';
import GlassNumberInput from '@/Components/GlassNumberInput.vue';
import GlassRangeSlider from '@/Components/GlassRangeSlider.vue';
import GlassDatePicker from '@/Components/GlassDatePicker.vue';
import GlassInputWrapper from '@/Components/GlassInputWrapper.vue';
import ScholarshipCard from '@/Components/ScholarshipCard.vue';
import PullToRefresh from '@/Components/PullToRefresh.vue';
import GlassModal from '@/Components/GlassModal.vue';
import { useFilterOptions } from '@/composables/useFilterOptions';

const page = usePage();

// Global bookmarked IDs from Inertia share
const bookmarkedIds = ref(page.props.bookmarkedIds || []);
const matchScores = ref(page.props.matchScores || {});

const { countries: countryList, categories: categoryList } = useFilterOptions();

// UI State
const sidebarOpen = ref(false);
const showAdvancedFilters = ref(false);
const viewMode = ref('grid');
const scholarships = ref([]);
const loadingMore = ref(false);
const hasMore = ref(true);
const currentPage = ref(1);
const sentinel = ref(null);
const mainScroll = ref(null);
const aiFilterLoading = ref(false);
const aiFilterReasoning = ref('');

const itemHeight = computed(() => viewMode.value === 'grid' ? 260 : 120);

// Primary filters
const filters = reactive({
  search: '',
  category: '',
  country: '',
  sort: 'deadline',
});

// Advanced filters
const advanced = reactive({
  amount_min: '',
  amount_max: '',
  deadline_before: '',
  min_match: 0,
});

const combinedFilters = computed(() => ({
  ...filters,
  amount_min: advanced.amount_min || undefined,
  amount_max: advanced.amount_max || undefined,
  deadline_before: advanced.deadline_before || undefined,
  min_match: advanced.min_match || undefined,
  sort: filters.sort,
}));

const categoryOptions = computed(() =>
  categoryList.value?.map(c => ({ value: c, label: c })) ?? []
);
const countryOptions = computed(() =>
  countryList.value?.map(c => ({ value: c, label: c })) ?? []
);
const sortOptions = [
  { value: 'deadline', label: 'Deadline' },
  { value: 'amount', label: 'Amount' },
  { value: 'match_score', label: 'Best Match' },
];

// Active filter counts
const advancedActiveCount = computed(() => {
  let count = 0;
  if (advanced.amount_min) count++;
  if (advanced.amount_max) count++;
  if (advanced.deadline_before) count++;
  if (advanced.min_match > 0) count++;
  return count;
});
const totalActiveFilterCount = computed(() => {
  let count = 0;
  if (filters.search) count++;
  if (filters.category) count++;
  if (filters.country) count++;
  return count + advancedActiveCount.value;
});

// Virtual scroll
const virtualContainerHeight = computed(() => scholarships.value.length * itemHeight.value);
const visibleItems = computed(() => {
  const scrollTop = mainScroll.value?.scrollTop || 0;
  const viewportHeight = mainScroll.value?.clientHeight || 800;
  const startIndex = Math.max(0, Math.floor(scrollTop / itemHeight.value) - 5);
  const endIndex = Math.min(
    scholarships.value.length,
    startIndex + Math.ceil(viewportHeight / itemHeight.value) + 10
  );
  return scholarships.value.slice(startIndex, endIndex).map((scholarship, idx) => ({
    scholarship,
    top: (startIndex + idx) * itemHeight.value,
  }));
});

// Fetch scholarships
async function fetchScholarships(pageNum = 1, append = false) {
  try {
    loadingMore.value = true;
    const params = {
      page: pageNum,
      ...combinedFilters.value,
    };
    const response = await axios.get('/api/scholarships', { params });
    const { data, next_page_url, current_page } = response.data;
    if (append) {
      scholarships.value = [...scholarships.value, ...data];
    } else {
      scholarships.value = data;
    }
    hasMore.value = next_page_url !== null;
    currentPage.value = current_page;
  } catch (error) {
    console.error('Failed to load scholarships:', error);
  } finally {
    loadingMore.value = false;
  }
}

async function refreshScholarships() {
  await fetchScholarships(1, false);
}

function resetFilters() {
  filters.search = '';
  filters.category = '';
  filters.country = '';
  filters.sort = 'deadline';
  advanced.amount_min = '';
  advanced.amount_max = '';
  advanced.deadline_before = '';
  advanced.min_match = 0;
  aiFilterReasoning.value = '';
  fetchScholarships(1, false);
}

function applyAdvancedFilters() {
  showAdvancedFilters.value = false;
  fetchScholarships(1, false);
}

async function applyAIFilter() {
  if (aiFilterLoading.value) return;
  aiFilterLoading.value = true;
  aiFilterReasoning.value = '';
  try {
    const response = await axios.post('/api/scholarships/ai-filter', {
      profile: page.props.auth?.profile || {},
      current_filters: { ...filters, ...advanced },
    });
    const { recommended_filters, reasoning } = response.data;
    if (recommended_filters) {
      for (const [key, value] of Object.entries(recommended_filters)) {
        if (key in filters) {
          filters[key] = value;
        } else if (key in advanced) {
          advanced[key] = value;
        }
      }
      aiFilterReasoning.value = reasoning || 'AI has optimized your filters for the best matches.';
      await fetchScholarships(1, false);
    }
  } catch (error) {
    console.error('AI filter failed:', error);
    aiFilterReasoning.value = 'AI filter temporarily unavailable. Try again later.';
  } finally {
    aiFilterLoading.value = false;
  }
}

const debouncedFetch = debounce(() => fetchScholarships(1, false), 300);
watch([filters, advanced], debouncedFetch, { deep: true });

let observer;
function setupObserver() {
  if (!sentinel.value) return;
  observer = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting && hasMore.value && !loadingMore.value) {
      fetchScholarships(currentPage.value + 1, true);
    }
  }, { rootMargin: '100px' });
  observer.observe(sentinel.value);
}

function onScroll() {}

// Card click handler – ignores bookmark button
function handleCardClick(event, scholarshipId) {
  if (event.target.closest('[data-bookmark-toggle]')) return;
  router.visit(route('scholarships.show', scholarshipId));
}

// Toggle bookmark – uses axios directly, updates reactive bookmarkedIds
async function toggleBookmark(scholarshipId) {
  try {
    const response = await axios.post(`/bookmarks/${scholarshipId}`);
    if (response.data.status === 'bookmarked') {
      if (!bookmarkedIds.value.includes(scholarshipId)) {
        bookmarkedIds.value.push(scholarshipId);
      }
    } else {
      bookmarkedIds.value = bookmarkedIds.value.filter(id => id !== scholarshipId);
    }
  } catch (error) {
    console.error('Bookmark toggle error:', error);
  }
}

onMounted(() => {
  fetchScholarships(1, false);
  nextTick(() => setupObserver());
});

onBeforeUnmount(() => {
  if (observer) observer.disconnect();
  debouncedFetch.cancel?.();
});

</script>

<style scoped>
/* Premium Floating Filter Panel */
.glass-filter-panel {
  background: rgba(255, 255, 255, 0.45);
  backdrop-filter: blur(40px);
  -webkit-backdrop-filter: blur(40px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  max-height: calc(100vh - 100px);
  overflow: visible;
}
.dark .glass-filter-panel {
  background: rgba(0, 0, 0, 0.4);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

/* Top bar glass */
.glass-top-bar {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.4);
}
.dark .glass-top-bar {
  background: rgba(255, 255, 255, 0.03);
  border-color: rgba(255, 255, 255, 0.06);
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

/* Glass surface for buttons */
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
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.07);
  border-color: rgba(255, 255, 255, 0.12);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

/* 3D perspective */
.perspective-1000 { perspective: 1000px; }
.-rotate-y-2 { transform: rotateY(-2deg) rotateX(1deg); }
.-rotate-y-3 { transform: rotateY(-3deg) rotateX(1deg); }
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

@keyframes pulse-slow {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.1); }
}
.animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }
.animate-pulse-slow.delay-1000 { animation-delay: 1000ms; }

/* Mobile: disable 3D rotations */
@media (max-width: 768px) {
  .perspective-1000 { perspective: none !important; }
  .-rotate-y-2, .-rotate-y-3, .-rotate-y-1, .rotate-y-0,
  .glass-top-bar, .glass-filter-panel {
    transform: none !important;
  }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation: none !important;
    transition: none !important;
  }
  .perspective-1000 { perspective: none !important; }
  .-rotate-y-2, .-rotate-y-3, .-rotate-y-1, .rotate-y-0 {
    transform: none !important;
  }
}
</style>