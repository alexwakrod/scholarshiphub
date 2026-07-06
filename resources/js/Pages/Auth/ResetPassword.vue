<template>
  <GuestLayout>
    <div class="relative w-full max-w-md mx-auto perspective-1000">
      <!-- Elevated Glass Slab -->
      <div class="glass-form-slab rounded-3xl p-6 md:p-8 space-y-6">
        <!-- Header -->
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center tracking-tight text-shadow-glass">
          Reset Password
        </h2>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <GlassInput
              v-model="form.email"
              type="email"
              placeholder="Email"
              :error="form.errors.email"
            />
          </div>
          <div>
            <GlassInput
              v-model="form.password"
              type="password"
              placeholder="New password"
              :error="form.errors.password"
            />
          </div>
          <div>
            <GlassInput
              v-model="form.password_confirmation"
              type="password"
              placeholder="Confirm password"
            />
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="btn-primary-glass group relative w-full py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold overflow-hidden transition-all duration-300 hover:shadow-[0_0_60px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-[#3b82f6] focus-visible:ring-offset-2"
          >
            <span class="relative z-10 flex items-center justify-center gap-2">
              <span v-if="form.processing" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
              <span v-else>Reset Password</span>
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
          </button>
        </form>

        <!-- Back to login (embedded thread link) -->
        <p class="text-center text-sm text-gray-500 dark:text-white/50">
          <Link :href="route('login')" class="text-blue-400 hover:text-blue-300 hover:underline transition-colors">Back to login</Link>
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
  token: String,
  email: String,
});

const form = useForm({
  token: props.token,
  email: props.email || '',
  password: '',
  password_confirmation: '',
});

function submit() {
  form.post(route('password.update'));
}
</script>

<style scoped>
/* ============================================================
   GLASS RESET PASSWORD – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

/* 3D perspective container */
.perspective-1000 {
  perspective: 1000px;
}

/* Elevated glass slab */
.glass-form-slab {
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

.dark .glass-form-slab {
  background: rgba(0, 0, 0, 0.3);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow:
    0 16px 48px rgba(0, 0, 0, 0.5),
    inset 0 1px 0 rgba(255, 255, 255, 0.06);
}

.glass-form-slab:hover {
  transform: rotateY(0deg) rotateX(0deg) translateY(-4px) scale(1.01);
  box-shadow:
    0 24px 64px rgba(0, 0, 0, 0.15),
    inset 0 1px 0 rgba(255, 255, 255, 0.6);
  background: rgba(255, 255, 255, 0.35);
}

.dark .glass-form-slab:hover {
  background: rgba(0, 0, 0, 0.4);
  box-shadow:
    0 24px 64px rgba(0, 0, 0, 0.6),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.glass-form-slab:active {
  transform: scale(0.97) translateY(2px);
  transition-duration: 0.1s;
}

/* Inner rim highlight */
.glass-form-slab::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: inherit;
  background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 40%);
  pointer-events: none;
  z-index: 1;
}

.dark .glass-form-slab::after {
  background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, transparent 40%);
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

/* Input error states (global) */
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
  .glass-form-slab,
  .glass-form-slab:hover,
  .glass-form-slab:active,
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