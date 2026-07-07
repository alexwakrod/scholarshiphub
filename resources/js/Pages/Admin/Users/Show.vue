<template>
  <AppLayout>
    <div class="p-4 md:p-6 max-w-6xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
          User Detail
        </h1>
        <Link
          :href="route('admin.users.index')"
          class="text-sm text-gray-500 dark:text-white/40 hover:text-blue-600 dark:hover:text-blue-400 transition-colors underline-offset-2 hover:underline"
        >
          Back to list
        </Link>
      </div>

      <!-- User Info Card (Elevated Glass) -->
      <div class="glass-elevated rounded-2xl p-5 md:p-6 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)] stagger-item" style="--i:0">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
          <div>
            <span class="block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-white/40 mb-1">Name</span>
            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ user.name }}</span>
          </div>
          <div>
            <span class="block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-white/40 mb-1">Email</span>
            <span class="text-sm text-gray-700 dark:text-white/80">{{ user.email }}</span>
          </div>
          <div>
            <span class="block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-white/40 mb-1">Role</span>
            <span class="text-sm capitalize text-gray-700 dark:text-white/80">{{ user.role }}</span>
          </div>
          <div>
            <span class="block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-white/40 mb-1">Joined</span>
            <span class="text-sm text-gray-700 dark:text-white/80">{{ formatDate(user.created_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Student Profile Section (conditionally rendered) -->
      <transition name="card-slide">
        <div v-if="user.student_profile" class="glass-elevated rounded-2xl p-5 md:p-6 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)] stagger-item" style="--i:1">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Student Profile</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div>
              <span class="text-xs text-gray-500 dark:text-white/40 uppercase tracking-wider">Full Name</span>
              <p class="text-gray-700 dark:text-white/80 mt-0.5">{{ user.student_profile.full_name }}</p>
            </div>
            <div>
              <span class="text-xs text-gray-500 dark:text-white/40 uppercase tracking-wider">Academic Level</span>
              <p class="text-gray-700 dark:text-white/80 mt-0.5">{{ user.student_profile.academic_level }}</p>
            </div>
            <div>
              <span class="text-xs text-gray-500 dark:text-white/40 uppercase tracking-wider">Major</span>
              <p class="text-gray-700 dark:text-white/80 mt-0.5">{{ user.student_profile.major || '—' }}</p>
            </div>
            <div>
              <span class="text-xs text-gray-500 dark:text-white/40 uppercase tracking-wider">GPA</span>
              <p class="text-gray-700 dark:text-white/80 mt-0.5">{{ user.student_profile.gpa || '—' }}</p>
            </div>
            <div>
              <span class="text-xs text-gray-500 dark:text-white/40 uppercase tracking-wider">Country</span>
              <p class="text-gray-700 dark:text-white/80 mt-0.5">{{ user.student_profile.country || '—' }}</p>
            </div>
            <div>
              <span class="text-xs text-gray-500 dark:text-white/40 uppercase tracking-wider">English</span>
              <p class="text-gray-700 dark:text-white/80 mt-0.5">{{ user.student_profile.english_proficiency || '—' }}</p>
            </div>
            <div class="sm:col-span-2">
              <span class="text-xs text-gray-500 dark:text-white/40 uppercase tracking-wider">Profile Completed</span>
              <p class="text-gray-700 dark:text-white/80 mt-0.5">{{ user.student_profile.profile_completed ? 'Yes' : 'No' }}</p>
            </div>
          </div>
        </div>
      </transition>

      <!-- Documents Section -->
      <div class="glass-elevated rounded-2xl p-5 md:p-6 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)] stagger-item" style="--i:2">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Documents ({{ documents?.length || 0 }})</h2>
        <div v-if="documents?.length" class="divide-y divide-gray-200/30 dark:divide-white/5">
          <div v-for="doc in documents" :key="doc.id" class="flex justify-between py-2.5 text-sm">
            <span class="text-gray-700 dark:text-white/80">{{ doc.file_name }}</span>
            <span class="text-gray-500 dark:text-white/40 capitalize">{{ doc.file_type }}</span>
          </div>
        </div>
        <div v-else class="text-gray-500 dark:text-white/40 text-sm py-4 text-center">No documents.</div>
      </div>

      <!-- Applications Section -->
      <div class="glass-elevated rounded-2xl p-5 md:p-6 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)] stagger-item" style="--i:3">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Applications ({{ applications?.length || 0 }})</h2>
        <div v-if="applications?.length" class="divide-y divide-gray-200/30 dark:divide-white/5">
          <div v-for="app in applications" :key="app.id" class="flex justify-between py-2.5 text-sm">
            <span class="text-gray-700 dark:text-white/80">{{ app.scholarship?.title || 'N/A' }}</span>
            <span class="text-gray-500 dark:text-white/40 capitalize">{{ app.status }}</span>
          </div>
        </div>
        <div v-else class="text-gray-500 dark:text-white/40 text-sm py-4 text-center">No applications.</div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  user: Object,
  documents: Array,
  applications: Array,
});

function formatDate(dateStr) {
  try {
    return new Date(dateStr).toLocaleDateString();
  } catch {
    return dateStr;
  }
}
</script>

<style scoped>
/* Glass elevated slab */
.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.05);
}

/* Staggered entrance */
.stagger-item {
  opacity: 0;
  animation: fade-in-up 0.4s ease-out forwards;
  animation-delay: calc(var(--i, 0) * 80ms);
}
@keyframes fade-in-up {
  0% { opacity: 0; transform: translateY(12px); }
  100% { opacity: 1; transform: translateY(0); }
}

/* Conditional card transition */
.card-slide-enter-active {
  transition: all 0.35s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.card-slide-leave-active {
  transition: all 0.25s ease;
}
.card-slide-enter-from,
.card-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Text readability */
.text-gray-900, .text-white {
  text-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.dark .text-gray-900,
.dark .text-white {
  text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

/* Dividers etched effect */
.divide-gray-200\/30 > :not([hidden]) ~ :not([hidden]) {
  border-color: rgba(0,0,0,0.06);
}
.dark .divide-white\/5 > :not([hidden]) ~ :not([hidden]) {
  border-color: rgba(255,255,255,0.05);
}

/* Responsive */
@media (max-width: 640px) {
  .glass-elevated {
    padding: 1rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  .stagger-item,
  .card-slide-enter-active,
  .card-slide-leave-active {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>