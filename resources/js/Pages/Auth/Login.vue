<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-950 relative overflow-hidden flex items-center justify-center p-4">
    <!-- Animated Background -->
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full bg-blue-600/10 blur-3xl animate-pulse-slow"></div>
      <div class="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] rounded-full bg-indigo-600/10 blur-3xl animate-pulse-slow" style="animation-delay: 1s"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-purple-600/5 blur-3xl"></div>
      <div class="absolute top-20 left-10 w-3 h-3 rounded-full bg-blue-400/30 animate-float"></div>
      <div class="absolute top-40 right-20 w-4 h-4 rounded-full bg-indigo-400/30 animate-float" style="animation-delay: 0.7s"></div>
      <div class="absolute bottom-32 left-1/4 w-2 h-2 rounded-full bg-purple-400/30 animate-float" style="animation-delay: 1.4s"></div>
      <div class="absolute bottom-20 right-1/3 w-3 h-3 rounded-full bg-pink-400/30 animate-float" style="animation-delay: 2s"></div>
    </div>

    <!-- Back to Landing -->
    <Link
      href="/"
      class="absolute top-6 left-6 z-50 glass-nav p-3 rounded-xl text-gray-600 dark:text-white/60 hover:text-gray-900 dark:hover:text-white transition-all hover:scale-105 group"
    >
      <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
    </Link>

    <!-- Main Container -->
    <div class="relative w-full max-w-6xl grid lg:grid-cols-2 gap-8 items-start">

      <!-- LEFT: 3D Self‑Typing Login Demo -->
      <div class="relative perspective-1000 order-1 lg:order-1">
        <div class="glass-hero-card p-6 rounded-3xl transform -rotate-y-12 transition-all duration-700 hover:rotate-y-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center text-white font-bold text-sm shadow-[0_0_30px_rgba(34,197,94,0.3)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
              </div>
              <div>
                <p class="text-gray-900 dark:text-white text-sm font-medium">Live Login Demo</p>
                <p class="text-gray-500 dark:text-white/30 text-xs">Watch how to sign in</p>
              </div>
            </div>
            <span class="text-[10px] text-green-400/60 flex items-center gap-1.5">
              <span class="relative flex h-1.5 w-1.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
              </span>
              Auto‑demo
            </span>
          </div>

          <!-- Demo Form Fields -->
          <div class="space-y-3">
            <div
              class="glass-form-step p-3 rounded-xl transition-all duration-500"
              :class="[
                demoStep === 0 ? 'border-blue-500/30 bg-blue-500/5 shadow-[0_0_40px_rgba(59,130,246,0.1)]' : '',
                demoStep > 0 ? 'border-green-500/20 bg-green-500/5' : ''
              ]"
            >
              <div class="flex items-center gap-3">
                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold"
                     :class="demoStep > 0 ? 'bg-green-500/20 text-green-400' : 'glass-surface text-gray-500 dark:text-white/40'">
                  {{ demoStep > 0 ? '✓' : '1' }}
                </div>
                <div class="flex-1">
                  <p class="text-sm text-gray-600 dark:text-white/60" :class="demoStep > 0 ? 'text-gray-900 dark:text-white/80' : ''">
                    <span v-if="demoStep === 0 && demoData.email === ''"><span class="animate-pulse">|</span></span>
                    <span v-else>{{ demoData.email || 'Email address' }}</span>
                  </p>
                  <div class="w-full h-1 rounded-full bg-gray-200 dark:bg-white/5 mt-1 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-blue-400 to-indigo-400 transition-all duration-700"
                         :style="{ width: demoStep > 0 ? '100%' : (demoStep === 0 ? '50%' : '0%') }"></div>
                  </div>
                </div>
              </div>
            </div>

            <div
              class="glass-form-step p-3 rounded-xl transition-all duration-500"
              :class="[
                demoStep === 1 ? 'border-blue-500/30 bg-blue-500/5 shadow-[0_0_40px_rgba(59,130,246,0.1)]' : '',
                demoStep > 1 ? 'border-green-500/20 bg-green-500/5' : ''
              ]"
            >
              <div class="flex items-center gap-3">
                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold"
                     :class="demoStep > 1 ? 'bg-green-500/20 text-green-400' : 'glass-surface text-gray-500 dark:text-white/40'">
                  {{ demoStep > 1 ? '✓' : '2' }}
                </div>
                <div class="flex-1">
                  <p class="text-sm text-gray-600 dark:text-white/60" :class="demoStep > 1 ? 'text-gray-900 dark:text-white/80' : ''">
                    <span v-if="demoStep === 1 && demoData.password === ''"><span class="animate-pulse">|</span></span>
                    <span v-else>{{ demoData.password ? '••••••••' : 'Password' }}</span>
                  </p>
                  <div class="w-full h-1 rounded-full bg-gray-200 dark:bg-white/5 mt-1 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-blue-400 to-indigo-400 transition-all duration-700"
                         :style="{ width: demoStep > 1 ? '100%' : (demoStep === 1 ? '50%' : '0%') }"></div>
                  </div>
                </div>
              </div>
            </div>

            <div
              class="glass-form-step p-3 rounded-xl transition-all duration-500"
              :class="[
                demoStep === 2 ? 'border-blue-500/30 bg-blue-500/5 shadow-[0_0_40px_rgba(59,130,246,0.1)]' : '',
                demoStep > 2 ? 'border-green-500/20 bg-green-500/5' : ''
              ]"
            >
              <div class="flex items-center gap-3">
                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold"
                     :class="demoStep > 2 ? 'bg-green-500/20 text-green-400' : 'glass-surface text-gray-500 dark:text-white/40'">
                  {{ demoStep > 2 ? '✓' : '3' }}
                </div>
                <div class="flex-1">
                  <p class="text-sm text-gray-600 dark:text-white/60" :class="demoStep > 2 ? 'text-gray-900 dark:text-white/80' : ''">
                    <span v-if="demoStep === 2 && !demoData.remember"><span class="animate-pulse">|</span></span>
                    <span v-else>{{ demoData.remember ? '✓ Remember me' : 'Remember me' }}</span>
                  </p>
                  <div class="w-full h-1 rounded-full bg-gray-200 dark:bg-white/5 mt-1 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-blue-400 to-indigo-400 transition-all duration-700"
                         :style="{ width: demoStep > 2 ? '100%' : (demoStep === 2 ? '50%' : '0%') }"></div>
                  </div>
                </div>
              </div>
            </div>

            <div
              class="glass-form-step p-3 rounded-xl transition-all duration-500"
              :class="[
                demoStep === 3 ? 'border-blue-500/30 bg-blue-500/5 shadow-[0_0_40px_rgba(59,130,246,0.1)]' : '',
                demoStep > 3 ? 'border-green-500/20 bg-green-500/5' : ''
              ]"
            >
              <div class="flex items-center gap-3">
                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold"
                     :class="demoStep > 3 ? 'bg-green-500/20 text-green-400' : 'glass-surface text-gray-500 dark:text-white/40'">
                  {{ demoStep > 3 ? '✓' : '4' }}
                </div>
                <div class="flex-1">
                  <p class="text-sm text-gray-600 dark:text-white/60" :class="demoStep > 3 ? 'text-gray-900 dark:text-white/80' : ''">
                    <span v-if="demoStep === 3 && !demoData.submitted"><span class="animate-pulse">|</span></span>
                    <span v-else>{{ demoData.submitted ? '✓ Signing in...' : 'Sign in' }}</span>
                  </p>
                  <div class="w-full h-1 rounded-full bg-gray-200 dark:bg-white/5 mt-1 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-green-400 to-emerald-400 transition-all duration-700"
                         :style="{ width: demoStep > 3 ? '100%' : (demoStep === 3 ? '50%' : '0%') }"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-5 pt-5 border-t border-gray-200 dark:border-white/5">
            <div class="flex items-center justify-between">
              <span class="text-xs text-gray-500 dark:text-white/30">Demo Progress</span>
              <span class="text-sm font-bold text-gray-900 dark:text-white">{{ demoProgress }}%</span>
            </div>
            <div class="w-full h-1.5 rounded-full bg-gray-200 dark:bg-white/5 mt-2 overflow-hidden">
              <div class="h-full rounded-full bg-gradient-to-r from-blue-400 via-indigo-400 to-purple-400 transition-all duration-700"
                   :style="{ width: demoProgress + '%' }"></div>
            </div>
          </div>

          <div v-if="demoProgress === 100" class="mt-4 p-3 rounded-xl bg-green-500/10 border border-green-500/20 flex items-center gap-3 animate-fade-in">
            <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            </div>
            <div>
              <p class="text-sm text-green-400 font-medium">Demo complete!</p>
              <p class="text-xs text-green-400/50">Now it's your turn to sign in</p>
            </div>
          </div>

          <div class="mt-4 p-3 rounded-xl glass-surface">
            <p class="text-xs text-gray-600 dark:text-white/40 leading-relaxed">
              <span class="text-gray-900 dark:text-white/60 font-medium">💡 Tip:</span>
              {{ demoStep === 0 && demoData.email === '' ? 'Enter your registered email address.' :
                 demoStep === 1 && demoData.password === '' ? 'Enter your account password.' :
                 demoStep === 2 && !demoData.remember ? 'Check "Remember me" to stay logged in.' :
                 demoStep === 3 && !demoData.submitted ? 'Click "Sign in" to access your dashboard.' :
                 demoProgress === 100 ? 'You are now logged in and ready to explore scholarships.' :
                 'Watch the automatic demo to see how to sign in.' }}
            </p>
          </div>
        </div>
      </div>

      <!-- RIGHT: Login Form -->
      <div class="glass-hero-card p-8 rounded-3xl transition-all duration-700 hover:shadow-2xl order-2 lg:order-2">
        <div class="text-center mb-8">
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-surface mb-4">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
            </span>
            <span class="text-xs text-gray-600 dark:text-white/50 font-medium">Welcome Back</span>
          </div>
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
            Sign In to Your
            <span class="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Account</span>
          </h2>
          <p class="mt-2 text-sm text-gray-500 dark:text-white/40">Continue your scholarship journey</p>
        </div>

        <!-- Global error banner -->
        <div v-if="globalError" class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-sm text-red-400 text-center">
          {{ globalError }}
        </div>

        <form @submit.prevent="login" class="space-y-4">
          <GlassInput
            v-model="form.email"
            type="email"
            placeholder="Email address"
            :error="form.errors.email || loginEmailError"
            icon="email"
          />

          <GlassInput
            v-model="form.password"
            type="password"
            placeholder="Password"
            :error="form.errors.password"
            icon="lock"
          />

          <div class="flex items-center justify-between">
            <GlassCheckbox v-model="form.remember" label="Remember me" />
            <Link :href="route('password.request')" class="text-sm text-blue-500 dark:text-blue-400 hover:text-blue-600 dark:hover:text-blue-300 transition-colors hover:underline">
              Forgot password?
            </Link>
          </div>

          <button
            type="submit"
            class="group relative w-full py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold overflow-hidden transition-all duration-300 hover:shadow-[0_0_60px_rgba(59,130,246,0.4)] hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="form.processing"
          >
            <span class="relative z-10 flex items-center justify-center gap-2">
              <span v-if="form.processing" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
              <span v-else>Sign In</span>
              <svg v-if="!form.processing" class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
          </button>
        </form>

        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200 dark:border-white/10"></div></div>
          <div class="relative flex justify-center text-xs"><span class="px-4 glass-surface text-gray-400 dark:text-white/30 rounded-full py-1">or continue with</span></div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <a :href="route('socialite.redirect', 'github')" class="group flex items-center justify-center gap-2 py-3 rounded-xl glass-surface hover:glass-elevated transition-all hover:-translate-y-0.5 text-gray-700 dark:text-white/70 hover:text-gray-900 dark:hover:text-white text-sm">
            <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
            GitHub
          </a>
          <a :href="route('socialite.redirect', 'google')" class="group flex items-center justify-center gap-2 py-3 rounded-xl glass-surface hover:glass-elevated transition-all hover:-translate-y-0.5 text-gray-700 dark:text-white/70 hover:text-gray-900 dark:hover:text-white text-sm">
            <svg class="w-5 h-5 group-hover:scale-110 transition-transform" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            Google
          </a>
        </div>

        <p class="text-center text-sm text-gray-500 dark:text-white/40 mt-4">
          Don't have an account?
          <Link :href="route('register')" class="text-blue-500 dark:text-blue-400 hover:text-blue-600 dark:hover:text-blue-300 transition-colors font-medium hover:underline">Sign up</Link>
        </p>
      </div>
    </div>

    <div class="lg:hidden fixed bottom-4 left-4 right-4">
      <div class="glass-elevated p-4 rounded-2xl flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center text-white font-bold text-sm shadow-[0_0_30px_rgba(34,197,94,0.3)]">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
        </div>
        <div>
          <p class="text-gray-900 dark:text-white text-sm font-medium">3D Login Guide</p>
          <p class="text-gray-500 dark:text-white/30 text-xs">Rotate your device or view on desktop</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import axios from 'axios';
import GlassInput from '@/Components/GlassInput.vue';
import GlassCheckbox from '@/Components/GlassCheckbox.vue';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

// Real‑time email existence check
const emailExists = ref(false);
let emailCheckTimer = null;
watch(() => form.email, (newVal) => {
  emailExists.value = false;
  if (!newVal || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newVal)) return;
  clearTimeout(emailCheckTimer);
  emailCheckTimer = setTimeout(async () => {
    try {
      const { data } = await axios.post('/api/check-email', { email: newVal });
      emailExists.value = data.exists;
    } catch (e) { /* ignore */ }
  }, 500);
});

const loginEmailError = computed(() => {
  if (form.errors.email) return '';
  if (!form.email) return '';
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) return 'Invalid email format.';
  if (emailExists.value === false && form.email.length > 0) return 'No account found with this email.';
  return '';
});

const globalError = computed(() => {
  return form.errors.email ?? form.errors.password ?? '';
});

function login() {
  console.log('[Login] Submitting form...', form.data());
  form.post(route('login'), {
    onSuccess: (page) => {
      console.log('[Login] Success – redirecting...');
    },
    onError: (errors) => {
      console.error('[Login] Errors:', errors);
    },
    onFinish: () => {
      console.log('[Login] Request finished.');
      form.password = '';
    },
  });
}

// Demo simulation
const demoData = ref({ email: '', password: '', remember: false, submitted: false });
const demoStep = ref(0);
const demoProgress = computed(() => (demoStep.value === 4 ? 100 : demoStep.value * 25));

function typeField(field, value, delay = 60) {
  return new Promise(resolve => {
    let i = 0;
    const iv = setInterval(() => {
      if (i <= value.length) { demoData.value[field] = value.substring(0, i); i++; }
      else { clearInterval(iv); resolve(); }
    }, delay);
  });
}

async function runDemo() {
  demoStep.value = 0;
  await typeField('email', 'john@example.com');
  await new Promise(r => setTimeout(r, 800));
  demoStep.value = 1;
  await typeField('password', '••••••••');
  await new Promise(r => setTimeout(r, 800));
  demoStep.value = 2;
  await new Promise(r => setTimeout(r, 400));
  demoData.value.remember = true;
  await new Promise(r => setTimeout(r, 800));
  demoStep.value = 3;
  await new Promise(r => setTimeout(r, 600));
  demoData.value.submitted = true;
  await new Promise(r => setTimeout(r, 800));
  demoStep.value = 4;
}
onMounted(runDemo);
</script>

<style scoped>
.glass-hero-card {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 20px 80px rgba(0, 0, 0, 0.2);
  transition: all 0.7s cubic-bezier(0.4, 0, 0.2, 1);
}
.dark .glass-hero-card {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 20px 80px rgba(0, 0, 0, 0.4);
}

.glass-form-step {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.dark .glass-form-step {
  background: rgba(255, 255, 255, 0.02);
  border-color: rgba(255, 255, 255, 0.06);
}

.glass-nav {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.dark .glass-nav {
  background: rgba(0, 0, 0, 0.3);
  border-color: rgba(255, 255, 255, 0.06);
}

.perspective-1000 { perspective: 1000px; }
.-rotate-y-12 { transform: rotateY(12deg) rotateX(4deg); }
.rotate-y-6 { transform: rotateY(6deg) rotateX(2deg); }

.animate-fade-in { animation: fade-in 0.5s ease-out forwards; }
@keyframes fade-in {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes float {
  0%, 100% { transform: translateY(0px) scale(1); opacity: 0.3; }
  50% { transform: translateY(-20px) scale(1.2); opacity: 0.8; }
}
.animate-float { animation: float 6s ease-in-out infinite; }
@keyframes pulse-slow {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.1); }
}
.animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }
@keyframes ping-slow {
  0%, 100% { transform: scale(1); opacity: 0.3; }
  50% { transform: scale(1.3); opacity: 0; }
}
.animate-ping-slow { animation: ping-slow 3s ease-in-out infinite; }
</style>