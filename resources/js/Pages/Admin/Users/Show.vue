<template>
  <AppLayout>
    <div class="p-6 max-w-5xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">User Detail</h1>
        <Link :href="route('admin.users.index')" class="text-sm text-white/50 hover:text-white underline">Back to list</Link>
      </div>

      <GlassCard variant="elevated" class="p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <p class="text-sm text-white/50">Name</p>
            <p class="text-white">{{ user.name }}</p>
          </div>
          <div>
            <p class="text-sm text-white/50">Email</p>
            <p class="text-white">{{ user.email }}</p>
          </div>
          <div>
            <p class="text-sm text-white/50">Role</p>
            <p class="text-white capitalize">{{ user.role }}</p>
          </div>
          <div>
            <p class="text-sm text-white/50">Joined</p>
            <p class="text-white">{{ formatDate(user.created_at) }}</p>
          </div>
        </div>
      </GlassCard>

      <!-- Student profile section -->
      <GlassCard v-if="user.student_profile" variant="elevated" class="p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Student Profile</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
          <div><span class="text-white/50">Full Name:</span> <span class="text-white">{{ user.student_profile.full_name }}</span></div>
          <div><span class="text-white/50">Academic Level:</span> <span class="text-white">{{ user.student_profile.academic_level }}</span></div>
          <div><span class="text-white/50">Major:</span> <span class="text-white">{{ user.student_profile.major || '—' }}</span></div>
          <div><span class="text-white/50">GPA:</span> <span class="text-white">{{ user.student_profile.gpa || '—' }}</span></div>
          <div><span class="text-white/50">Country:</span> <span class="text-white">{{ user.student_profile.country || '—' }}</span></div>
          <div><span class="text-white/50">English:</span> <span class="text-white">{{ user.student_profile.english_proficiency || '—' }}</span></div>
          <div><span class="text-white/50">Profile Completed:</span> <span class="text-white">{{ user.student_profile.profile_completed ? 'Yes' : 'No' }}</span></div>
        </div>
      </GlassCard>

      <!-- Documents -->
      <GlassCard variant="elevated" class="p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Documents ({{ documents?.length || 0 }})</h2>
        <div v-if="documents?.length" class="space-y-2">
          <div v-for="doc in documents" :key="doc.id" class="flex justify-between text-sm">
            <span class="text-white">{{ doc.file_name }}</span>
            <span class="text-white/50 capitalize">{{ doc.file_type }}</span>
          </div>
        </div>
        <div v-else class="text-white/50 text-sm">No documents.</div>
      </GlassCard>

      <!-- Applications -->
      <GlassCard variant="elevated" class="p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Applications ({{ applications?.length || 0 }})</h2>
        <div v-if="applications?.length" class="space-y-2">
          <div v-for="app in applications" :key="app.id" class="flex justify-between text-sm">
            <span class="text-white">{{ app.scholarship?.title || 'N/A' }}</span>
            <span class="text-white/50 capitalize">{{ app.status }}</span>
          </div>
        </div>
        <div v-else class="text-white/50 text-sm">No applications.</div>
      </GlassCard>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';

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