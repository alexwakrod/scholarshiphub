<template>
  <GuestLayout>
    <div class="relative w-full max-w-md mx-auto perspective-1000">
      <!-- Elevated Glass Slab with inner rim highlight and shadow -->
      <div class="glass-elevated-slab rounded-3xl p-6 md:p-8 space-y-6 stagger-container">
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
        <h2 class="text-2xl font-bold text-white text-center tracking-tight text-shadow-glass stagger-item" style="--i:0">
          Set Up Two-Factor Authentication
        </h2>
        <p class="text-sm text-white/60 text-center stagger-item" style="--i:1">
          Scan the QR code with your authenticator app (Google Authenticator, Authy, etc.) or enter the secret manually.
        </p>

        <!-- QR Code (illuminated glass trench) -->
        <div class="flex justify-center stagger-item" style="--i:2">
          <div class="glass-qr-code rounded-2xl p-3 bg-white/10 dark:bg-white/5 backdrop-blur-sm border border-white/10" v-html="qrCodeSvg"></div>
        </div>

        <!-- Secret key -->
        <div class="text-center stagger-item" style="--i:3">
          <p class="text-xs text-white/40 mb-1 tracking-widest uppercase">Manual entry key</p>
          <code class="glass-secret-code inline-block bg-white/10 dark:bg-white/5 backdrop-blur-sm px-4 py-1.5 rounded-xl text-sm text-white font-mono tracking-widest select-all">{{ secret }}</code>
          <div class="mt-2 flex justify-center">
            <ClipboardCopy :text="secret" label="Copy" class="text-xs text-white/50 hover:text-white/80 transition-colors" />
          </div>
        </div>

        <!-- Verification form -->
        <form @submit.prevent="verifyAndEnable" class="space-y-4 stagger-item" style="--i:4">
          <div>
            <GlassInput
              v-model="form.code"
              placeholder="Enter 6-digit code"
              maxlength="6"
              inputmode="numeric"
              :error="form.errors.code"
            />
          </div>
          <button
            type="submit"
            :disabled="form.processing"
            class="btn-primary-glass w-full py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold overflow-hidden transition-all duration-300 hover:shadow-[0_0_60px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-[#3b82f6] focus-visible:ring-offset-2"
          >
            <span class="relative z-10 flex items-center justify-center gap-2">
              <span v-if="form.processing" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
              <span v-else>Enable 2FA</span>
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
          </button>
        </form>

        <!-- Skip for now link (embedded thread) -->
        <p class="text-center text-sm text-white/50 stagger-item" style="--i:5">
          <Link :href="route('settings')" class="text-blue-400 hover:text-blue-300 hover:underline transition-colors">Skip for now</Link>
        </p>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import GlassInput from '@/Components/GlassInput.vue';
import ClipboardCopy from '@/Components/ClipboardCopy.vue';

defineProps({
  secret: String,
  qrCodeSvg: String,
});

const form = useForm({
  code: '',
});

function verifyAndEnable() {
  form.post(route('auth.2fa.enable'), {
    onError: (errors) => {
      // form.errors automatically populated
    },
  });
}
</script>

<style scoped>
/* ============================================================
   GLASS 2FA SETUP – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

/* 3D perspective for the slab */
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
  overflow: hidden; /* contains blur */
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

/* Active press */
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

/* Back to settings button */
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

/* Surface glass (for light/dark adaptability) */
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

/* QR code illumination */
.glass-qr-code {
  transition: box-shadow 0.3s ease;
}
.glass-qr-code:hover {
  box-shadow: 0 0 30px rgba(59,130,246,0.2);
}

/* Secret code monospaced */
.glass-secret-code {
  letter-spacing: 0.15em;
  border: 1px solid rgba(255,255,255,0.1);
  transition: border-color 0.2s ease;
}
.glass-secret-code:hover {
  border-color: rgba(59,130,246,0.3);
}

/* Primary button */
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

/* Text readability shadows */
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

/* Autofill transparency override */
:deep(input:-webkit-autofill),
:deep(input:-webkit-autofill:hover),
:deep(input:-webkit-autofill:focus),
:deep(input:-webkit-autofill:active) {
  -webkit-box-shadow: 0 0 0 30px transparent inset !important;
  box-shadow: 0 0 0 30px transparent inset !important;
  -webkit-text-fill-color: #fff !important;
  transition: background-color 5000s ease-in-out 0s;
}

/* Staggered entrance animation */
.stagger-container > .stagger-item {
  opacity: 0;
  animation: fade-in-up 0.5s ease-out forwards;
  animation-delay: calc(var(--i, 0) * 80ms);
}

@keyframes fade-in-up {
  0% {
    opacity: 0;
    transform: translateY(12px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Accessibility & Mobile Override */
@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .glass-elevated-slab,
  .glass-elevated-slab:hover,
  .glass-elevated-slab:active,
  .btn-primary-glass,
  .btn-primary-glass:hover,
  .btn-primary-glass:active,
  .glass-back-btn,
  .glass-back-btn:hover {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .stagger-container > .stagger-item {
    animation: none !important;
    opacity: 1;
  }
  *,
  *::before,
  *::after {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>