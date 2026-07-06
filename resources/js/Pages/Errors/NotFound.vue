<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 px-4">
    <div class="text-center max-w-md">
      <div class="text-8xl font-extrabold text-white/20 mb-4">404</div>
      <h1 class="text-2xl font-bold text-white mb-2">Page not found</h1>
      <p class="text-sm text-white/60 mb-8">The page you are looking for doesn't exist or has been moved.</p>
      <Link
        href="/"
        class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold hover:opacity-90 transition-opacity"
      >
        Go back home
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>