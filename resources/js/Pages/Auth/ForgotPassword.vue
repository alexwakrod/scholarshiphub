<template>
  <GuestLayout>
    <div class="relative w-full max-w-md mx-auto perspective-1000">
      <!-- Elevated Glass Slab (uses global glass-elevated for perfect theme sync) -->
      <div class="glass-elevated rounded-3xl p-6 md:p-8 space-y-6">
        <!-- Header -->
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center tracking-tight text-shadow-glass">
          Forgot Password
        </h2>
        <p class="text-sm text-gray-500 dark:text-white/60 text-center">
          Enter your email and we'll send you a reset link.
        </p>

        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <GlassInput
              v-model="form.email"
              type="email"
              placeholder="Email"
              :error="form.errors.email"
            />
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="btn-primary-glass group relative w-full py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold overflow-hidden transition-all duration-300 hover:shadow-[0_0_60px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-[#3b82f6] focus-visible:ring-offset-2"
          >
            <span class="relative z-10 flex items-center justify-center gap-2">
              <span v-if="form.processing" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
              <span v-else>Email Password Reset Link</span>
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
          </button>
        </form>

        <!-- Success message -->
        <div
          v-if="status"
          class="p-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-600 dark:text-green-400 text-sm text-center animate-fade-in"
        >
          {{ status }}
        </div>

        <!-- Back to login (embedded thread link) -->
        <p class="text-center text-sm text-gray-500 dark:text-white/50">
          <Link :href="route('login')" class="text-blue-600 dark:text-blue-400 hover:underline transition-colors">Back to login</Link>
        </p>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import GlassInput from '@/Components/GlassInput.vue';

const props = defineProps({
  status: String,
});

const form = useForm({
  email: '',
});

function submit() {
  form.post(route('password.email'));
}
</script>

<style scoped>
/* ============================================================
   GLASS FORGOT PASSWORD – THEME‑AWARE (global glass-elevated)
   ============================================================ */

/* 3D perspective container */
.perspective-1000 {
  perspective: 1000px;
}

/* Primary button with 3D physics */
.btn-primary-glass {
  position: relative;
  overflow: hidden;
  will-change: transform;
  transform: rotateY(-2deg);
}
.btn-primary-glass:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}
.btn-primary-glass:active:not(:disabled) {
  transform: scale(0.95) translateY(1px);
  transition-duration: 0.1s;
}

/* Localized glow */
.localized-glow {
  filter: blur(20px);
  opacity: 0;
  transition: opacity 0.4s ease;
}
.group:hover .localized-glow {
  opacity: 0.3;
}

/* Text shadow for readability */
.text-shadow-glass {
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}
.dark .text-shadow-glass {
  text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}

/* Success message fade in */
.animate-fade-in {
  animation: fade-in 0.4s ease-out;
}
@keyframes fade-in {
  0% { opacity: 0; transform: translateY(-8px); }
  100% { opacity: 1; transform: translateY(0); }
}

/* Input error shake */
:deep(.glass-input-error) {
  animation: shake 0.4s ease-in-out;
}
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}

/* Autofill transparency */
:deep(input:-webkit-autofill),
:deep(input:-webkit-autofill:hover),
:deep(input:-webkit-autofill:focus),
:deep(input:-webkit-autofill:active) {
  -webkit-box-shadow: 0 0 0 30px transparent inset !important;
  box-shadow: 0 0 0 30px transparent inset !important;
  -webkit-text-fill-color: #fff !important;
  transition: background-color 5000s ease-in-out 0s;
}

/* Mobile & accessibility overrides */
@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .btn-primary-glass,
  .btn-primary-glass:hover,
  .btn-primary-glass:active {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>