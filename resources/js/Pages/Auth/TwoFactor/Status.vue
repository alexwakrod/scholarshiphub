<template>
  <GuestLayout>
    <div class="relative w-full max-w-md mx-auto perspective-1000">
      <!-- Elevated Glass Slab -->
      <div class="glass-elevated-slab rounded-3xl p-6 md:p-8 space-y-6">
        <!-- Back to Settings button (top-left corner) -->
        <Link
          :href="route('settings')"
          class="glass-back-btn group absolute top-4 left-4 z-10 flex items-center justify-center w-10 h-10 rounded-xl glass-surface hover:glass-elevated transition-all duration-300 hover:scale-105 active:scale-95 focus-visible:ring-2 focus-visible:ring-[#3b82f6]"
          aria-label="Back to Settings"
        >
          <svg class="w-5 h-5 text-gray-500 dark:text-white/40 group-hover:text-gray-900 dark:group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </Link>

        <!-- Header -->
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center tracking-tight text-shadow-glass">
          Two-Factor Authentication
        </h2>

        <!-- Status Indicator (Emerald Glass Orb) -->
        <div class="text-center">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-500/20 dark:bg-green-500/20 border border-green-500/30 backdrop-blur-sm shadow-[0_0_20px_rgba(16,185,129,0.3)]">
            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <p class="mt-3 font-medium text-gray-900 dark:text-white">2FA is enabled</p>
          <p class="mt-1 text-sm text-gray-500 dark:text-white/50">Your account is protected with two-factor authentication.</p>
        </div>

        <!-- Disable Form -->
        <form @submit.prevent="disable" class="space-y-4">
          <div>
            <GlassInput
              v-model="form.code"
              placeholder="Enter 6-digit code to disable"
              maxlength="6"
              inputmode="numeric"
              :error="form.errors.code"
            />
          </div>
          <button
            type="submit"
            :disabled="form.processing"
            class="btn-destructive group relative w-full py-3 rounded-2xl border border-red-500/30 bg-red-500/10 text-red-400 font-semibold overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(239,68,68,0.3)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-[#EF4444] focus-visible:ring-offset-2"
          >
            <span class="relative z-10 flex items-center justify-center gap-2">
              <span v-if="form.processing" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
              <span v-else>Disable 2FA</span>
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-red-400 to-orange-400 opacity-0 group-hover:opacity-25 transition-opacity blur-xl localized-glow"></div>
          </button>
        </form>

        <!-- Back to settings (embedded thread link) -->
        <p class="text-center text-sm text-gray-500 dark:text-white/50">
          <Link :href="route('settings')" class="text-blue-400 hover:text-blue-300 hover:underline transition-colors">Back to settings</Link>
        </p>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import GlassInput from '@/Components/GlassInput.vue';

const form = useForm({
  code: '',
});

function disable() {
  form.post(route('auth.2fa.disable'));
}
</script>

<style scoped>
/* ============================================================
   GLASS 2FA STATUS – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

/* 3D perspective container */
.perspective-1000 {
  perspective: 1000px;
}

/* Elevated glass slab */
.glass-elevated-slab {
  position: relative;
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow:
    0 16px 48px rgba(0, 0, 0, 0.12),
    inset 0 1px 0 rgba(255, 255, 255, 0.5);
  transform: rotateY(-2deg) rotateX(0.5deg);
  transition: transform 0.5s cubic-bezier(0.2, 0.8, 0.2, 1),
              box-shadow 0.5s ease,
              background 0.3s ease;
  will-change: transform;
  isolation: isolate;
  overflow: hidden;
}

.dark .glass-elevated-slab {
  background: rgba(0, 0, 0, 0.3);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow:
    0 16px 48px rgba(0, 0, 0, 0.5),
    inset 0 1px 0 rgba(255, 255, 255, 0.06);
}

.glass-elevated-slab:hover {
  transform: rotateY(0deg) rotateX(0deg) translateY(-4px) scale(1.01);
  box-shadow:
    0 24px 64px rgba(0, 0, 0, 0.15),
    inset 0 1px 0 rgba(255, 255, 255, 0.6);
  background: rgba(255, 255, 255, 0.35);
}

.dark .glass-elevated-slab:hover {
  background: rgba(0, 0, 0, 0.4);
  box-shadow:
    0 24px 64px rgba(0, 0, 0, 0.6),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.glass-elevated-slab:active {
  transform: scale(0.97) translateY(2px);
  transition-duration: 0.1s;
}

/* Inner rim highlight */
.glass-elevated-slab::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: inherit;
  background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 40%);
  pointer-events: none;
  z-index: 1;
}

.dark .glass-elevated-slab::after {
  background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, transparent 40%);
}

/* Back button */
.glass-back-btn {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(0,0,0,0.08);
  transform: rotateY(-1deg);
  will-change: transform;
}
.dark .glass-back-btn {
  border-color: rgba(255,255,255,0.08);
}
.glass-back-btn:hover {
  transform: rotateY(0deg) scale(1.05);
}

/* Surface glass variations */
.glass-surface {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.dark .glass-surface {
  background: rgba(255, 255, 255, 0.04);
}

.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.06);
  box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}

/* Destructive button */
.btn-destructive {
  transform: rotateY(-2deg);
  will-change: transform;
}
.btn-destructive:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}
.btn-destructive:active:not(:disabled) {
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
  opacity: 0.25;
}

/* Text shadow for readability */
.text-shadow-glass {
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}
.dark .text-shadow-glass {
  text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}

/* Error shake animation */
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}
:deep(.glass-input-error) {
  animation: shake 0.4s ease-in-out;
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
  .glass-elevated-slab,
  .glass-elevated-slab:hover,
  .glass-elevated-slab:active,
  .btn-destructive,
  .btn-destructive:hover,
  .btn-destructive:active,
  .glass-back-btn,
  .glass-back-btn:hover {
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