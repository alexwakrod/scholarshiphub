<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-900 px-4 py-12">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <Link href="/" class="inline-block">
          <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
            ScholarshipHub
          </h1>
        </Link>
      </div>
      <div class="glass-card glass-elevated p-8">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<style scoped>
.glass-card {
  backdrop-filter: blur(12px);
  background: rgba(255, 255, 255, 0.35);
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 1rem;
  color: #111827;
}
.dark .glass-card {
  background: rgba(0, 0, 0, 0.3);
  border-color: rgba(255, 255, 255, 0.1);
  color: #f3f4f6;
}
</style>