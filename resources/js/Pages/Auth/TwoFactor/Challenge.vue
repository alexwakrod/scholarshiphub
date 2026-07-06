<template>
  <GuestLayout>
    <div class="relative w-full max-w-md mx-auto perspective-1000">
      <!-- Elevated Glass Slab -->
      <div class="glass-elevated rounded-3xl p-6 md:p-8 space-y-6">
        <!-- Header -->
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center tracking-tight text-shadow-glass">
          Two-Factor Authentication
        </h2>
        <p class="text-sm text-gray-500 dark:text-white/60 text-center">
          Enter the 6-digit code from your authenticator app.
        </p>

        <form @submit.prevent="verify" class="space-y-5">
          <div>
            <GlassInput
              v-model="form.code"
              placeholder="6-digit code"
              maxlength="6"
              inputmode="numeric"
              :error="form.errors.code"
            />
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="btn-primary-glass group relative w-full py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold overflow-hidden transition-all duration-300 hover:shadow-[0_0_60px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-[#3b82f6] focus-visible:ring-offset-2"
          >
            <span class="relative z-10 flex items-center justify-center gap-2">
              <span v-if="form.processing" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
              <span v-else>Verify</span>
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
          </button>
        </form>

        <div class="text-center">
          <button
            @click="recovery"
            class="text-sm text-gray-500 dark:text-white/50 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
          >
            Use recovery code
          </button>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import GlassInput from '@/Components/GlassInput.vue';

const form = useForm({
  code: '',
});

function verify() {
  form.post(route('auth.2fa.verify'));
}

function recovery() {
  // Optional: implement recovery code logic later
  alert('Recovery codes not yet implemented.');
}
</script>

<style scoped>
/* ============================================================
   GLASS 2FA CHALLENGE – THEME‑AWARE & BLUEPRINT COMPLIANT
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