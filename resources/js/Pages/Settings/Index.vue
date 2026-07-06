<template>
  <AppLayout>
    <div class="min-h-screen px-4 py-8 sm:py-12 settings-canvas">
      <div class="mx-auto max-w-2xl space-y-6 settings-wrapper">
        <!-- Page Title -->
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white settings-title">
          Settings
        </h1>

        <!-- Email Section (Elevated Glass Slab) -->
        <div
          class="glass-card-elevated rounded-3xl p-6 md:p-8"
          style="--stagger-delay: 0ms"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <h2 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white">
                Email Address
              </h2>
              <p class="text-xs text-gray-500 dark:text-white/40 mt-1 tracking-wide uppercase">
                Primary contact
              </p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
              <div class="relative w-full sm:w-64">
                <GlassInput
                  :model-value="currentEmail"
                  disabled
                  placeholder="Your email address"
                  class="opacity-60 cursor-not-allowed email-display"
                />
              </div>
              <button
                @click="openEmailModal"
                class="btn-micro-primary group relative px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-[#3b82f6] focus-visible:ring-offset-2"
              >
                <span class="relative z-10 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                  Change Email
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
              </button>
            </div>
          </div>
        </div>

        <!-- Two-Factor Authentication (Elevated Glass Slab) -->
        <div
          class="glass-card-elevated rounded-3xl p-6 md:p-8"
          style="--stagger-delay: 100ms"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <h2 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white">
                Two-Factor Authentication
              </h2>
              <p class="text-xs text-gray-500 dark:text-white/40 mt-1 tracking-wide uppercase">
                {{ twoFactorEnabled ? 'Enabled' : 'Disabled' }}
              </p>
            </div>
            <Link
              v-if="!twoFactorEnabled"
              :href="route('auth.2fa.setup')"
              class="btn-micro-primary group relative px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-[#3b82f6] focus-visible:ring-offset-2 inline-flex items-center gap-2"
            >
              <span class="relative z-10 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Enable 2FA
              </span>
              <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </Link>
            <Link
              v-else
              :href="route('auth.2fa.status')"
              class="btn-micro-danger group relative px-5 py-2.5 rounded-xl border border-red-500/30 bg-red-500/10 text-red-400 text-sm font-medium overflow-hidden transition-all duration-300 hover:bg-red-500/20 hover:shadow-[0_0_30px_rgba(239,68,68,0.3)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-[#EF4444] focus-visible:ring-offset-2 inline-flex items-center gap-2"
            >
              <span class="relative z-10 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Manage 2FA
              </span>
              <div class="absolute inset-0 bg-gradient-to-r from-red-400 to-orange-400 opacity-0 group-hover:opacity-20 transition-opacity blur-xl localized-glow"></div>
            </Link>
          </div>
        </div>

        <!-- Account Deletion (Elevated Glass Slab with Red Border) -->
        <div
          class="glass-card-elevated rounded-3xl p-6 md:p-8 border-red-500/20"
          style="--stagger-delay: 200ms"
        >
          <h2 class="text-lg font-semibold tracking-tight text-red-400 mb-4">
            Delete Account
          </h2>
          <div v-if="pendingDeletion" class="space-y-3">
            <p class="text-sm text-gray-700 dark:text-white/70 leading-relaxed deletion-text">
              Your account is scheduled for deletion on
              <strong class="text-gray-900 dark:text-white">{{ formattedDeletionDate }}</strong>.
            </p>
            <button
              @click="cancelDeletion"
              class="btn-micro-success group relative px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(16,185,129,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-[#10B981] focus-visible:ring-offset-2 inline-flex items-center gap-2"
            >
              <span class="relative z-10 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Cancel Deletion
              </span>
              <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-teal-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </button>
          </div>
          <div v-else class="space-y-3">
            <p class="text-sm text-gray-700 dark:text-white/70 leading-relaxed deletion-text">
              Permanently delete your account and all associated data. This action will
              take effect after a 7-day grace period.
            </p>
            <button
              @click="scheduleDeletion"
              class="btn-micro-danger group relative px-5 py-2.5 rounded-xl border border-red-500/30 bg-red-500/10 text-red-400 text-sm font-medium overflow-hidden transition-all duration-300 hover:bg-red-500/20 hover:shadow-[0_0_40px_rgba(239,68,68,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-[#EF4444] focus-visible:ring-offset-2 inline-flex items-center gap-2"
            >
              <span class="relative z-10 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Schedule Account Deletion
              </span>
              <div class="absolute inset-0 bg-gradient-to-r from-red-400 to-orange-400 opacity-0 group-hover:opacity-25 transition-opacity blur-xl localized-glow"></div>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Email Change Modal (Ultra-Hero Depth) -->
    <GlassModal v-model="emailModalOpen" @close="closeEmailModal">
      <div class="space-y-5">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white modal-title">
          Change Email
        </h3>

        <!-- Error banner -->
        <div
          v-if="errorMessage"
          class="p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-sm text-red-400 animate-shake error-banner"
        >
          {{ errorMessage }}
        </div>

        <!-- Step 1: Enter new email -->
        <div v-if="!codeSent" class="space-y-4">
          <GlassInput
            v-model="newEmail"
            type="email"
            placeholder="New email address"
            :error="validationErrors.new_email ? validationErrors.new_email[0] : ''"
          />
          <button
            @click="sendCode"
            :disabled="!newEmail || sendingCode"
            class="btn-modal-primary group relative w-full py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold overflow-hidden transition-all duration-300 hover:shadow-[0_0_60px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-[#3b82f6] focus-visible:ring-offset-2"
          >
            <span class="relative z-10 flex items-center justify-center gap-2">
              <span v-if="sendingCode" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              {{ sendingCode ? 'Sending...' : 'Send Verification Code' }}
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
          </button>
        </div>

        <!-- Step 2: Enter code -->
        <div v-else class="space-y-4">
          <p class="text-sm text-gray-600 dark:text-white/60 leading-relaxed">
            We've sent a 6-digit code to <strong class="text-gray-900 dark:text-white">{{ newEmail }}</strong>. Please enter it below.
          </p>
          <GlassInput
            v-model="verificationCode"
            placeholder="000000"
            maxlength="6"
            inputmode="numeric"
            :error="validationErrors.code ? validationErrors.code[0] : ''"
          />
          <button
            @click="verifyCode"
            :disabled="verificationCode.length !== 6 || verifying"
            class="btn-modal-primary group relative w-full py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold overflow-hidden transition-all duration-300 hover:shadow-[0_0_60px_rgba(16,185,129,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-[#10B981] focus-visible:ring-offset-2"
          >
            <span class="relative z-10 flex items-center justify-center gap-2">
              <span v-if="verifying" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              {{ verifying ? 'Verifying...' : 'Verify & Change Email' }}
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-teal-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
          </button>
          <button
            @click="codeSent = false; verificationCode = ''; errorMessage = ''; validationErrors = {}"
            class="w-full py-2 rounded-xl text-sm text-gray-500 dark:text-white/40 hover:text-gray-700 dark:hover:text-white/60 transition-colors"
          >
            Cancel
          </button>
        </div>
      </div>
    </GlassModal>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassModal from '@/Components/GlassModal.vue';

const props = defineProps({
  email: String,
  twoFactorEnabled: Boolean,
  pendingDeletion: String,
});

const currentEmail = ref(props.email);
const emailModalOpen = ref(false);
const newEmail = ref('');
const verificationCode = ref('');
const codeSent = ref(false);
const sendingCode = ref(false);
const verifying = ref(false);
const errorMessage = ref('');
const validationErrors = ref({});

function openEmailModal() {
  newEmail.value = '';
  verificationCode.value = '';
  codeSent.value = false;
  errorMessage.value = '';
  validationErrors.value = {};
  emailModalOpen.value = true;
}

function closeEmailModal() {
  emailModalOpen.value = false;
}

async function sendCode() {
  sendingCode.value = true;
  errorMessage.value = '';
  validationErrors.value = {};

  try {
    const response = await axios.post(route('settings.email.send-code'), {
      new_email: newEmail.value,
    });
    console.log('[Settings] sendCode success:', response.data);
    codeSent.value = true;
  } catch (error) {
    console.error('[Settings] sendCode error:', error);
    if (error.response) {
      console.error('[Settings] Response status:', error.response.status);
      console.error('[Settings] Response data:', error.response.data);
      if (error.response.data.errors) {
        validationErrors.value = error.response.data.errors;
      }
      errorMessage.value =
        error.response.data.message ||
        'Failed to send verification code. Please try again.';
    } else if (error.request) {
      console.error('[Settings] No response received:', error.request);
      errorMessage.value = 'Network error. Check your connection and try again.';
    } else {
      console.error('[Settings] Request setup error:', error.message);
      errorMessage.value = 'An unexpected error occurred. Please try again.';
    }
  } finally {
    sendingCode.value = false;
  }
}

async function verifyCode() {
  verifying.value = true;
  errorMessage.value = '';
  validationErrors.value = {};

  try {
    const response = await axios.post(route('settings.email.verify'), {
      code: verificationCode.value,
    });
    console.log('[Settings] verifyCode success:', response.data);
    currentEmail.value = newEmail.value;
    emailModalOpen.value = false;
    alert('Email updated successfully!');
  } catch (error) {
    console.error('[Settings] verifyCode error:', error);
    if (error.response) {
      console.error('[Settings] Response status:', error.response.status);
      console.error('[Settings] Response data:', error.response.data);
      if (error.response.data.errors) {
        validationErrors.value = error.response.data.errors;
      }
      errorMessage.value =
        error.response.data.message || 'Failed to verify code. Please try again.';
    } else if (error.request) {
      console.error('[Settings] No response received:', error.request);
      errorMessage.value = 'Network error. Check your connection and try again.';
    } else {
      console.error('[Settings] Request setup error:', error.message);
      errorMessage.value = 'An unexpected error occurred. Please try again.';
    }
  } finally {
    verifying.value = false;
  }
}

function scheduleDeletion() {
  if (confirm('Are you sure? Your account will be deleted in 7 days.')) {
    useForm({}).post(route('settings.deletion.schedule'), {
      preserveScroll: true,
      onSuccess: () => location.reload(),
    });
  }
}

function cancelDeletion() {
  useForm({}).post(route('settings.deletion.cancel'), {
    preserveScroll: true,
    onSuccess: () => location.reload(),
  });
}

const formattedDeletionDate = computed(() => {
  if (!props.pendingDeletion) return '';
  return new Date(props.pendingDeletion).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
});
</script>

<style scoped>
/* ============================================================
   GLASS ARCHITECT'S BLUEPRINT – SETTINGS/INDEX.VUE
   ============================================================ */

/* ------ 1. Foundational Canvas ------ */
.settings-canvas {
  background: transparent;
}

.settings-wrapper {
  position: relative;
}

/* ------ 2. Elevated Glass Slabs ------ */
.glass-card-elevated {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow:
    0 8px 32px rgba(0, 0, 0, 0.10),
    inset 0 1px 0 rgba(255, 255, 255, 0.5);
  transform: rotateY(-2deg) rotateX(0.5deg);
  transition: transform 0.5s cubic-bezier(0.2, 0.8, 0.2, 1),
              box-shadow 0.5s ease,
              background 0.3s ease,
              border-color 0.3s ease;
  will-change: transform, opacity;
  opacity: 0;
  animation: stagger-enter 0.5s ease-out forwards;
  animation-delay: var(--stagger-delay, 0ms);
}

.dark .glass-card-elevated {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow:
    0 8px 32px rgba(0, 0, 0, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.06);
}

.glass-card-elevated:hover {
  transform: rotateY(0deg) rotateX(0deg) translateY(-4px) scale(1.01);
  box-shadow:
    0 16px 48px rgba(0, 0, 0, 0.15),
    inset 0 1px 0 rgba(255, 255, 255, 0.6);
  background: rgba(255, 255, 255, 0.35);
}

.dark .glass-card-elevated:hover {
  background: rgba(255, 255, 255, 0.08);
  box-shadow:
    0 16px 48px rgba(0, 0, 0, 0.5),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.glass-card-elevated:active {
  transform: scale(0.97) translateY(2px);
  transition-duration: 0.1s;
}

/* ------ 3. Micro-Interactive Buttons ------ */
.btn-micro-primary,
.btn-micro-danger,
.btn-micro-success {
  transform: rotateY(-1deg);
  will-change: transform;
  white-space: nowrap;
}

.btn-micro-primary:hover:not(:disabled),
.btn-micro-danger:hover:not(:disabled),
.btn-micro-success:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}

.btn-micro-primary:active:not(:disabled),
.btn-micro-danger:active:not(:disabled),
.btn-micro-success:active:not(:disabled) {
  transform: scale(0.95) translateY(1px);
  transition-duration: 0.1s;
}

/* ------ 4. Ambient Localized Glow ------ */
.localized-glow {
  filter: blur(20px);
  opacity: 0;
  transition: opacity 0.4s ease;
}

.group:hover .localized-glow {
  opacity: 0.3;
}

/* ------ 5. Modal Primary Buttons ------ */
.btn-modal-primary {
  transform: rotateY(-1deg);
  will-change: transform;
}

.btn-modal-primary:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-2px) scale(1.01);
}

.btn-modal-primary:active:not(:disabled) {
  transform: scale(0.97) translateY(1px);
  transition-duration: 0.1s;
}

/* ------ 6. Modal Title Gradient ------ */
.modal-title {
  background: linear-gradient(135deg, #60a5fa, #818cf8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* ------ 7. Error Banner ------ */
.error-banner {
  animation: shake 0.4s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}

/* ------ 8. Deletion Text (Reduced Blur) ------ */
.deletion-text {
  backdrop-filter: none;
}

/* ------ 9. Disabled Email Input ------ */
.email-display {
  cursor: not-allowed;
}

/* ------ 10. Staggered Entrance ------ */
@keyframes stagger-enter {
  0% {
    opacity: 0;
    transform: rotateY(-2deg) rotateX(0.5deg) translateY(16px);
  }
  100% {
    opacity: 1;
    transform: rotateY(-2deg) rotateX(0.5deg) translateY(0);
  }
}

/* ------ 11. Text Shadows for Readability ------ */
.text-gray-900 {
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.3);
}

.dark .text-gray-900,
.dark .text-white {
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

/* ------ 12. Autofill Transparency ------ */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
input:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0 30px transparent inset !important;
  box-shadow: 0 0 0 30px transparent inset !important;
  -webkit-text-fill-color: inherit !important;
  transition: background-color 5000s ease-in-out 0s;
}

/* ------ 13. Focus-Visible Ring ------ */
*:focus-visible {
  outline: none;
  box-shadow: 0 0 0 2px #3b82f6;
  border-radius: 0.75rem;
}

/* ------ 14. Responsive Overrides ------ */
@media (max-width: 767px) {
  .glass-card-elevated,
  .glass-card-elevated:hover,
  .glass-card-elevated:active,
  .btn-micro-primary,
  .btn-micro-primary:hover,
  .btn-micro-primary:active,
  .btn-micro-danger,
  .btn-micro-danger:hover,
  .btn-micro-danger:active,
  .btn-micro-success,
  .btn-micro-success:hover,
  .btn-micro-success:active,
  .btn-modal-primary,
  .btn-modal-primary:hover,
  .btn-modal-primary:active {
    transform: none !important;
  }

  .glass-card-elevated:hover {
    background: rgba(255, 255, 255, 0.3);
  }

  .dark .glass-card-elevated:hover {
    background: rgba(255, 255, 255, 0.06);
  }

  .space-y-6 > * + * {
    margin-top: 0.75rem;
  }
}

/* ------ 15. Reduced Motion ------ */
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