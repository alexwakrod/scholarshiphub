<template>
  <AppLayout>
    <div class="p-4 md:p-6 space-y-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
        Admin <span class="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Dashboard</span>
      </h1>

      <!-- Quick Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="stat in stats" :key="stat.label" class="glass-elevated rounded-2xl p-5 text-center">
          <p class="text-3xl font-bold text-gray-900 dark:text-white tabular-nums">{{ stat.value }}</p>
          <p class="text-xs text-gray-500 dark:text-white/40 mt-1 uppercase tracking-wide">{{ stat.label }}</p>
        </div>
      </div>

      <!-- Admin Quick Links Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <Link
          v-for="link in adminLinks"
          :key="link.href"
          :href="link.href"
          class="glass-elevated rounded-2xl p-5 flex items-start gap-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl group"
        >
          <div class="w-10 h-10 rounded-xl bg-blue-500/10 dark:bg-blue-500/20 flex items-center justify-center text-blue-400">
            <component :is="link.icon" class="w-5 h-5" />
          </div>
          <div>
            <p class="font-semibold text-gray-900 dark:text-white group-hover:bg-gradient-to-r group-hover:from-blue-400 group-hover:to-indigo-400 group-hover:bg-clip-text group-hover:text-transparent transition-all">{{ link.label }}</p>
            <p class="text-xs text-gray-500 dark:text-white/40 mt-1">{{ link.description }}</p>
          </div>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
  AcademicCapIcon,
  UsersIcon,
  ChartBarIcon,
  QuestionMarkCircleIcon,
  ArrowUpTrayIcon,
  StarIcon,
  TagIcon,
  ClipboardIcon,
  ClockIcon,
  FlagIcon,
  PencilIcon,
  DocumentTextIcon,
  BellIcon,
  ShareIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  totalUsers: Number,
  totalStudents: Number,
  totalAdmins: Number,
  activeScholarships: Number,
  expiredScholarships: Number,
  totalAiReviews: Number,
  totalApplications: Number,
  averageMatchScore: Number,
});

const stats = [
  { label: 'Total Users', value: props.totalUsers },
  { label: 'Active Scholarships', value: props.activeScholarships },
  { label: 'AI Reviews', value: props.totalAiReviews },
  { label: 'Avg Match Score', value: props.averageMatchScore + '%' },
];

const adminLinks = [
  { href: '/admin/scholarships', label: 'Manage Scholarships', description: 'Add, edit, parse, and delete scholarships', icon: AcademicCapIcon },
  { href: '/admin/users', label: 'User Management', description: 'View profiles, impersonate, export data', icon: UsersIcon },
  { href: '/admin/analytics', label: 'Analytics', description: 'Charts, metrics, and activity heatmap', icon: ChartBarIcon },
  { href: '/admin/faqs', label: 'FAQs', description: 'Manage public frequently asked questions', icon: QuestionMarkCircleIcon },
  { href: '/admin/import', label: 'Import Scholarships', description: 'Upload CSV with column mapping', icon: ArrowUpTrayIcon },
  { href: '/admin/testimonials', label: 'Testimonials', description: 'Approve or delete student stories', icon: StarIcon },
  { href: '/admin/categories', label: 'Categories', description: 'Tree-based category management', icon: TagIcon },
  { href: '/admin/audit-logs', label: 'Audit Logs', description: 'Track all admin actions and changes', icon: ClipboardIcon },
  { href: '/admin/scheduled-tasks', label: 'Scheduled Tasks', description: 'View and run background commands', icon: ClockIcon },
  { href: '/admin/feature-flags', label: 'Feature Flags', description: 'Toggle AI features and more', icon: FlagIcon },
  { href: '/admin/menu-editor', label: 'Menu Editor', description: 'Customize sidebar navigation', icon: PencilIcon },
  { href: '/admin/file-manager', label: 'File Manager', description: 'Browse and manage uploaded documents', icon: DocumentTextIcon },
  { href: '/admin/notification-templates', label: 'Notification Templates', description: 'Edit email templates', icon: BellIcon },
  { href: '/admin/shared-dashboards', label: 'Shared Dashboards', description: 'Create public dashboard links', icon: ShareIcon },
];
</script>