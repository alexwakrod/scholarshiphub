<template>
  <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 p-4 rounded-2xl glass-avatar-surface border border-gray-200 dark:border-white/5">
    <div class="relative group">
      <div
        class="w-24 h-24 rounded-full glass-elevated flex items-center justify-center overflow-hidden ring-2 ring-gray-300 dark:ring-white/10 group-hover:ring-gray-400 dark:group-hover:ring-white/20 transition-all cursor-pointer"
        @click="$emit('preview')"
      >
        <img
          v-if="src"
          :src="src"
          class="w-full h-full object-cover"
          alt="Avatar"
        />
        <svg v-else class="w-10 h-10 text-gray-400 dark:text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <div class="absolute bottom-0.5 right-0.5 w-3 h-3 rounded-full bg-green-500 border-2 border-white/20 dark:border-gray-900/20 shadow-[0_0_12px_rgba(74,222,128,0.4)]">
          <div class="absolute inset-0 rounded-full bg-green-400 animate-ping opacity-75"></div>
        </div>
      </div>
      <div class="absolute -inset-0.5 rounded-full bg-gradient-to-br from-blue-500/20 to-indigo-500/20 blur-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
    </div>

    <div class="flex-1">
      <p class="text-sm font-medium text-gray-900 dark:text-white">Profile Picture</p>
      <p class="text-xs text-gray-500 dark:text-white/30 mt-0.5">Click the avatar to preview</p>
      <div class="mt-2">
        <label class="glass-upload cursor-pointer inline-block px-4 py-2 rounded-xl glass-upload-surface hover:glass-upload-hover transition-all text-xs text-gray-600 dark:text-white/50 hover:text-gray-900 dark:hover:text-white group">
          <input type="file" accept="image/*" @change="$emit('upload', $event)" class="hidden" />
          <span class="flex items-center gap-2">
            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Upload New
          </span>
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  src: { type: String, default: '' },
});
defineEmits(['preview', 'upload']);
</script>

<style scoped>
.glass-avatar-surface {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
}
.dark .glass-avatar-surface {
  background: rgba(255, 255, 255, 0.02);
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

.glass-upload-surface {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.dark .glass-upload-surface {
  background: rgba(255, 255, 255, 0.02);
  border-color: rgba(255, 255, 255, 0.06);
}

.glass-upload-hover {
  background: rgba(255, 255, 255, 0.5);
  border-color: rgba(0, 0, 0, 0.15);
}
.dark .glass-upload-hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.15);
}
</style>