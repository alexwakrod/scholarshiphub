<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-950 text-gray-900 dark:text-white overflow-x-hidden">
    <!-- ================================================================ -->
    <!-- 1. Glass Orbital Nav (auth‑aware) – Elevated depth                  -->
    <!-- ================================================================ -->
    <nav class="sticky top-0 z-50 px-4 py-3">
      <div class="max-w-7xl mx-auto glass-nav-elevated rounded-2xl px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3 group">
          <div class="relative w-10 h-10">
            <div class="absolute inset-0 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 blur-xl opacity-50 group-hover:opacity-70 transition-opacity"></div>
            <div class="relative w-full h-full rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-[0_0_40px_rgba(59,130,246,0.3)]">
              <span class="text-white font-bold text-lg">S</span>
            </div>
            <div class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_12px_rgba(16,185,129,0.6)]"></div>
          </div>
          <!-- Always hidden on mobile, visible on sm and above -->
          <span class="hidden sm:inline text-xl font-bold bg-gradient-to-r from-blue-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent">
            ScholarshipHub
          </span>
        </div>

        <div class="hidden md:flex items-center gap-2">
          <a href="#how-it-works" class="nav-link">How it Works</a>
          <a href="#features" class="nav-link">Features</a>
          <a href="#testimonials" class="nav-link">Testimonials</a>
        </div>

        <div class="flex items-center gap-3">
          <!-- Navbar theme toggle hidden on mobile, replaced by floating button -->
          <ThemeToggle class="hidden sm:flex" />

          <!-- Logged in state -->
          <template v-if="currentUser">
            <div class="relative" ref="profileMenuRef">
              <button
                @click="profileMenuOpen = !profileMenuOpen"
                class="nav-link flex items-center gap-2 px-3 py-1.5"
              >
                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-400 to-indigo-400 flex items-center justify-center text-white text-xs font-bold overflow-hidden">
                  <img
                    v-if="currentUser.avatar_url"
                    :src="currentUser.avatar_url.startsWith('http') ? currentUser.avatar_url : '/storage/' + currentUser.avatar_url"
                    class="w-full h-full object-cover"
                    alt="Avatar"
                  />
                  <span v-else>{{ currentUser.name.charAt(0).toUpperCase() }}</span>
                </div>
                <!-- User name hidden on mobile to save space -->
                <span class="hidden sm:inline text-gray-700 dark:text-white/80">{{ currentUser.name }}</span>
                <svg class="w-4 h-4 text-gray-500 dark:text-white/50 transition-transform" :class="{ 'rotate-180': profileMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <Transition name="menu-fade">
                <div v-if="profileMenuOpen" class="absolute right-0 mt-2 w-48 glass-hero rounded-xl border border-gray-200 dark:border-white/10 shadow-xl z-50">
                  <Link :href="route('dashboard')" @click="profileMenuOpen = false" class="profile-menu-link rounded-t-xl">
                    Dashboard
                  </Link>
                  <Link :href="route('logout')" method="post" @click="profileMenuOpen = false" class="profile-menu-link rounded-b-xl">
                    Sign out
                  </Link>
                </div>
              </Transition>
            </div>
          </template>

          <!-- Guest state -->
          <template v-else>
            <Link :href="route('login')" class="nav-link px-4 py-2">
              Log in
            </Link>
            <Link
              :href="route('register')"
              class="btn-primary"
            >
              <span class="relative z-10">Get Started</span>
              <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-20 transition-opacity blur-xl"></div>
            </Link>
          </template>
        </div>
      </div>
    </nav>

    <!-- Mobile Floating Theme Toggle (visible only on small screens) -->
    <div class="sm:hidden fixed bottom-6 right-6 z-40">
      <ThemeToggle size="lg" />
    </div>

    <!-- ================================================================ -->
    <!-- 2. Cosmic Glass Portal Hero                                         -->
    <!-- ================================================================ -->
    <section class="relative min-h-[90vh] flex items-center overflow-hidden">
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full bg-blue-600/10 blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] rounded-full bg-indigo-600/10 blur-3xl animate-pulse-slow" style="animation-delay:1s"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-purple-600/5 blur-3xl"></div>
        <div class="absolute top-20 left-10 w-3 h-3 rounded-full bg-blue-400/30 animate-float"></div>
        <div class="absolute top-40 right-20 w-4 h-4 rounded-full bg-indigo-400/30 animate-float" style="animation-delay:0.7s"></div>
        <div class="absolute bottom-32 left-1/4 w-2 h-2 rounded-full bg-purple-400/30 animate-float" style="animation-delay:1.4s"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
          <div class="text-center lg:text-left">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-surface mb-6">
              <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
              </span>
              <span class="text-xs text-gray-600 dark:text-white/60 font-medium">AI-Powered · Free Forever</span>
            </div>

            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.1] text-gray-900 dark:text-white">
              Find Your Perfect
              <span class="relative inline-block">
                <span class="bg-gradient-to-r from-blue-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent animate-gradient">
                  Scholarship
                </span>
                <svg class="absolute -bottom-2 left-0 w-full h-2" viewBox="0 0 200 8">
                  <defs><linearGradient id="underline2" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#3b82f6" stop-opacity="0.3"/><stop offset="50%" stop-color="#8b5cf6" stop-opacity="0.6"/><stop offset="100%" stop-color="#3b82f6" stop-opacity="0.3"/></linearGradient></defs>
                  <ellipse cx="100" cy="4" rx="100" ry="3" fill="url(#underline2)" />
                </svg>
              </span>
              <span class="block mt-2">in Minutes</span>
            </h1>

            <p class="mt-6 text-lg text-gray-600 dark:text-white/60 max-w-xl mx-auto lg:mx-0 leading-relaxed">
              AI‑powered matching and document coaching for fully‑funded international scholarships. Built by a student, for students.
            </p>

            <div class="grid grid-cols-3 gap-3 mt-8 max-w-md mx-auto lg:mx-0">
              <div class="stat-card">
                <p class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ landing.activeScholarships }}</p>
                <p class="stat-label">Scholarships</p>
              </div>
              <div class="stat-card" style="transition-delay:0.1s">
                <p class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ landing.registeredStudents }}</p>
                <p class="stat-label">Students</p>
              </div>
              <div class="stat-card" style="transition-delay:0.2s">
                <p class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ landing.documentsReviewed }}</p>
                <p class="stat-label">Documents</p>
              </div>
            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
              <Link
                :href="currentUser ? route('dashboard') : route('register')"
                class="btn-primary"
              >
                <span class="relative z-10 flex items-center gap-2">
                  {{ currentUser ? 'Go to Dashboard' : 'Start Finding Scholarships' }}
                  <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
              </Link>

              <a
                href="#how-it-works"
                class="btn-secondary"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                How It Works
              </a>
            </div>

            <div class="mt-6 flex flex-wrap gap-4 justify-center lg:justify-start text-xs text-gray-400 dark:text-white/30">
              <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> No credit card required</span>
              <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Free forever</span>
              <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Open source</span>
            </div>
          </div>

          <!-- Right: 3D Glass Dashboard Preview -->
          <div class="relative hidden lg:block">
            <div class="relative perspective-1000">
              <div class="glass-hero-card p-6 rounded-3xl transition-all duration-700 hover:rotate-y-6 hover:scale-[1.02]">
                <div class="flex items-center justify-between mb-6">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500"></div>
                    <div>
                      <p class="text-gray-900 dark:text-white text-sm font-medium">Alex's Dashboard</p>
                      <p class="text-gray-500 dark:text-white/30 text-xs">Welcome back!</p>
                    </div>
                  </div>
                  <div class="flex gap-1.5"><div class="w-2 h-2 rounded-full bg-emerald-400/40"></div><div class="w-2 h-2 rounded-full bg-yellow-400/40"></div><div class="w-2 h-2 rounded-full bg-red-400/40"></div></div>
                </div>
                <div class="grid grid-cols-4 gap-2 mb-4">
                  <div class="glass-surface p-2.5 rounded-xl text-center"><p class="text-xs text-gray-500 dark:text-white/40">Matches</p><p class="text-sm font-bold text-gray-900 dark:text-white">12</p></div>
                  <div class="glass-surface p-2.5 rounded-xl text-center"><p class="text-xs text-gray-500 dark:text-white/40">Documents</p><p class="text-sm font-bold text-gray-900 dark:text-white">4</p></div>
                  <div class="glass-surface p-2.5 rounded-xl text-center"><p class="text-xs text-gray-500 dark:text-white/40">Score</p><p class="text-sm font-bold text-emerald-400">92%</p></div>
                  <div class="glass-surface p-2.5 rounded-xl text-center"><p class="text-xs text-gray-500 dark:text-white/40">Deadlines</p><p class="text-sm font-bold text-red-400">3</p></div>
                </div>
                <div class="h-[1px] bg-white/10 mb-4"></div>
                <div class="space-y-2.5">
                  <div class="glass-elevated p-3 rounded-xl flex items-center justify-between group hover:scale-[1.01] transition-transform">
                    <div><p class="text-gray-900 dark:text-white text-sm font-medium">MEXT Scholarship</p><p class="text-gray-500 dark:text-white/30 text-xs">Japan · Fully Funded</p></div>
                    <div class="flex items-center gap-2"><span class="text-emerald-400 text-sm font-bold">92%</span><div class="w-12 h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full w-[92%] rounded-full bg-gradient-to-r from-emerald-400 to-blue-400"></div></div></div>
                  </div>
                  <div class="glass-elevated p-3 rounded-xl flex items-center justify-between group hover:scale-[1.01] transition-transform">
                    <div><p class="text-gray-900 dark:text-white text-sm font-medium">Chevening Award</p><p class="text-gray-500 dark:text-white/30 text-xs">UK · Leadership</p></div>
                    <div class="flex items-center gap-2"><span class="text-yellow-400 text-sm font-bold">78%</span><div class="w-12 h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full w-[78%] rounded-full bg-gradient-to-r from-yellow-400 to-orange-400"></div></div></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================================================================ -->
    <!-- 3. Glass Pain Points (Problem Section)                              -->
    <!-- ================================================================ -->
    <section class="py-24 relative overflow-hidden">
      <div class="absolute inset-0 pointer-events-none"><div class="absolute top-0 right-0 w-96 h-96 rounded-full bg-red-600/5 blur-3xl"></div></div>
      <div class="relative max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
          <span class="inline-block px-4 py-1.5 rounded-full glass-surface text-xs font-medium text-gray-600 dark:text-white/50 uppercase tracking-wider mb-4">The Challenge</span>
          <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white">The Scholarship Search <span class="bg-gradient-to-r from-red-400 to-orange-400 bg-clip-text text-transparent">is Broken</span></h2>
          <p class="mt-4 text-gray-500 dark:text-white/40 max-w-2xl mx-auto">Students face three major barriers that prevent them from finding and securing funding.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
          <div class="card-interactive glass-elevated p-8 rounded-3xl" @click="toggleCard(0)">
            <div class="absolute -inset-0.5 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 blur-xl bg-red-500/10"></div>
            <div class="relative">
              <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-5 bg-red-500/10 text-red-400"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Information Overload</h3>
              <p class="text-gray-600 dark:text-white/50 leading-relaxed">Thousands of scholarships scattered across hundreds of websites. Finding the right one feels like searching for a needle in a haystack.</p>
              <Transition name="expand">
                <div v-if="expandedCard === 0" class="mt-4 pt-4 border-t border-gray-200 dark:border-white/10 text-sm text-gray-500 dark:text-white/40 leading-relaxed">{{ cardDetails[0] }}</div>
              </Transition>
              <div class="mt-4 flex items-center gap-1.5 text-xs font-medium text-gray-400 dark:text-white/20 group-hover:text-gray-600 dark:group-hover:text-white/60 transition-colors">
                <span>{{ expandedCard === 0 ? 'Show less' : 'Learn more' }}</span>
                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': expandedCard === 0 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </div>
            </div>
          </div>

          <div class="card-interactive glass-elevated p-8 rounded-3xl" @click="toggleCard(1)">
            <div class="absolute -inset-0.5 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 blur-xl bg-orange-500/10"></div>
            <div class="relative">
              <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-5 bg-orange-500/10 text-orange-400"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Weak Applications</h3>
              <p class="text-gray-600 dark:text-white/50 leading-relaxed">Even qualified students fail because their CVs and personal statements lack the polish and structure that selection committees expect.</p>
              <Transition name="expand">
                <div v-if="expandedCard === 1" class="mt-4 pt-4 border-t border-gray-200 dark:border-white/10 text-sm text-gray-500 dark:text-white/40 leading-relaxed">{{ cardDetails[1] }}</div>
              </Transition>
              <div class="mt-4 flex items-center gap-1.5 text-xs font-medium text-gray-400 dark:text-white/20 group-hover:text-gray-600 dark:group-hover:text-white/60 transition-colors">
                <span>{{ expandedCard === 1 ? 'Show less' : 'Learn more' }}</span>
                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': expandedCard === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </div>
            </div>
          </div>

          <div class="card-interactive glass-elevated p-8 rounded-3xl" @click="toggleCard(2)">
            <div class="absolute -inset-0.5 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 blur-xl bg-blue-500/10"></div>
            <div class="relative">
              <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-5 bg-blue-500/10 text-blue-400"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Clear Strategy</h3>
              <p class="text-gray-600 dark:text-white/50 leading-relaxed">Students apply to random scholarships without a roadmap, missing deadlines and wasting time on the wrong opportunities.</p>
              <Transition name="expand">
                <div v-if="expandedCard === 2" class="mt-4 pt-4 border-t border-gray-200 dark:border-white/10 text-sm text-gray-500 dark:text-white/40 leading-relaxed">{{ cardDetails[2] }}</div>
              </Transition>
              <div class="mt-4 flex items-center gap-1.5 text-xs font-medium text-gray-400 dark:text-white/20 group-hover:text-gray-600 dark:group-hover:text-white/60 transition-colors">
                <span>{{ expandedCard === 2 ? 'Show less' : 'Learn more' }}</span>
                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': expandedCard === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================================================================ -->
    <!-- 4. Feature Glass Grid (Solution)                                    -->
    <!-- ================================================================ -->
    <section id="features" class="py-24 relative">
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute bottom-0 left-0 w-96 h-96 rounded-full bg-blue-600/5 blur-3xl"></div>
        <div class="absolute top-1/2 right-0 w-96 h-96 rounded-full bg-indigo-600/5 blur-3xl"></div>
      </div>
      <div class="relative max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
          <span class="inline-block px-4 py-1.5 rounded-full glass-surface text-xs font-medium text-gray-600 dark:text-white/50 uppercase tracking-wider mb-4">Features</span>
          <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white">Designed to <span class="text-gradient">Supercharge</span> Your Success</h2>
          <p class="mt-4 text-gray-500 dark:text-white/40 max-w-2xl mx-auto">Three powerful features that transform how you find, apply for, and win scholarships.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
          <div class="card-interactive glass-elevated p-8 rounded-3xl" @click="toggleCard(3)">
            <div class="relative w-16 h-16 mb-5">
              <div class="absolute inset-0 rounded-2xl animate-ping-slow opacity-30 bg-blue-500/20"></div>
              <div class="relative w-full h-full rounded-2xl flex items-center justify-center bg-blue-500/10 text-blue-400"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">AI-Powered Matching</h3>
            <p class="text-gray-600 dark:text-white/50 leading-relaxed">Our algorithm analyzes your profile and ranks scholarships by your eligibility and preferences.</p>
            <Transition name="expand">
              <div v-if="expandedCard === 3" class="mt-4 pt-4 border-t border-gray-200 dark:border-white/10 text-sm text-gray-500 dark:text-white/40 leading-relaxed">{{ cardDetails[3] }}</div>
            </Transition>
            <div class="mt-4 flex items-center gap-1.5 text-xs font-medium text-gray-400 dark:text-white/20 group-hover:text-gray-600 dark:group-hover:text-white/60 transition-colors">
              <span>{{ expandedCard === 3 ? 'Show less' : 'Learn more' }}</span>
              <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': expandedCard === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
          </div>

          <div class="card-interactive glass-elevated p-8 rounded-3xl" @click="toggleCard(4)">
            <div class="relative w-16 h-16 mb-5" style="animation-delay:0.5s">
              <div class="absolute inset-0 rounded-2xl animate-ping-slow opacity-30 bg-purple-500/20"></div>
              <div class="relative w-full h-full rounded-2xl flex items-center justify-center bg-purple-500/10 text-purple-400"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Document Review</h3>
            <p class="text-gray-600 dark:text-white/50 leading-relaxed">Get instant AI feedback on your CV, personal statement, and motivation letters to strengthen them.</p>
            <Transition name="expand">
              <div v-if="expandedCard === 4" class="mt-4 pt-4 border-t border-gray-200 dark:border-white/10 text-sm text-gray-500 dark:text-white/40 leading-relaxed">{{ cardDetails[4] }}</div>
            </Transition>
            <div class="mt-4 flex items-center gap-1.5 text-xs font-medium text-gray-400 dark:text-white/20 group-hover:text-gray-600 dark:group-hover:text-white/60 transition-colors">
              <span>{{ expandedCard === 4 ? 'Show less' : 'Learn more' }}</span>
              <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': expandedCard === 4 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
          </div>

          <div class="card-interactive glass-elevated p-8 rounded-3xl" @click="toggleCard(5)">
            <div class="relative w-16 h-16 mb-5" style="animation-delay:1s">
              <div class="absolute inset-0 rounded-2xl animate-ping-slow opacity-30 bg-yellow-500/20"></div>
              <div class="relative w-full h-full rounded-2xl flex items-center justify-center bg-yellow-500/10 text-yellow-400"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Strategic Pathway</h3>
            <p class="text-gray-600 dark:text-white/50 leading-relaxed">Receive a personalised timeline and checklist to stay on track and boost your chances.</p>
            <Transition name="expand">
              <div v-if="expandedCard === 5" class="mt-4 pt-4 border-t border-gray-200 dark:border-white/10 text-sm text-gray-500 dark:text-white/40 leading-relaxed">{{ cardDetails[5] }}</div>
            </Transition>
            <div class="mt-4 flex items-center gap-1.5 text-xs font-medium text-gray-400 dark:text-white/20 group-hover:text-gray-600 dark:group-hover:text-white/60 transition-colors">
              <span>{{ expandedCard === 5 ? 'Show less' : 'Learn more' }}</span>
              <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': expandedCard === 5 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================================================================ -->
    <!-- 5. Step Timeline Glass (How It Works)                              -->
    <!-- ================================================================ -->
    <section id="how-it-works" class="py-24 relative">
      <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16">
          <span class="inline-block px-4 py-1.5 rounded-full glass-surface text-xs font-medium text-gray-600 dark:text-white/50 uppercase tracking-wider mb-4">Process</span>
          <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white">Get Started in <span class="text-gradient-green">4 Simple Steps</span></h2>
        </div>
        <div class="grid lg:grid-cols-4 gap-6 relative">
          <div class="absolute top-1/2 left-0 right-0 h-[2px] bg-gradient-to-r from-blue-500/20 via-indigo-500/20 to-purple-500/20 hidden lg:block -translate-y-1/2"></div>
          <div class="relative group"><div class="step-card"><div class="step-number step-1">1</div><h3 class="text-lg font-bold text-gray-900 dark:text-white">Create Profile</h3><p class="mt-2 text-sm text-gray-500 dark:text-white/40 leading-relaxed">Fill in your academic and personal details.</p></div></div>
          <div class="relative group"><div class="step-card"><div class="step-number step-2">2</div><h3 class="text-lg font-bold text-gray-900 dark:text-white">Get Matched</h3><p class="mt-2 text-sm text-gray-500 dark:text-white/40 leading-relaxed">See scholarships ranked by how well you fit.</p></div></div>
          <div class="relative group"><div class="step-card"><div class="step-number step-3">3</div><h3 class="text-lg font-bold text-gray-900 dark:text-white">Improve Documents</h3><p class="mt-2 text-sm text-gray-500 dark:text-white/40 leading-relaxed">Upload your documents for AI review and feedback.</p></div></div>
          <div class="relative group"><div class="step-card"><div class="step-number step-4">4</div><h3 class="text-lg font-bold text-gray-900 dark:text-white">Apply & Track</h3><p class="mt-2 text-sm text-gray-500 dark:text-white/40 leading-relaxed">Use the journey tracker to manage deadlines and tasks.</p></div></div>
        </div>
      </div>
    </section>

    <!-- ================================================================ -->
    <!-- 6. Glass Quote Cards (Testimonials)                                 -->
    <!-- ================================================================ -->
    <section id="testimonials" class="py-24 relative">
      <div class="absolute inset-0 pointer-events-none"><div class="absolute top-0 right-0 w-96 h-96 rounded-full bg-purple-600/5 blur-3xl"></div></div>
      <div class="relative max-w-5xl mx-auto px-4">
        <div class="text-center mb-16">
          <span class="inline-block px-4 py-1.5 rounded-full glass-surface text-xs font-medium text-gray-600 dark:text-white/50 uppercase tracking-wider mb-4">Testimonials</span>
          <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white">Real Stories from <span class="text-gradient-amber">Real Students</span></h2>
        </div>

        <div v-if="landing.testimonials.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="t in landing.testimonials" :key="t.id" class="card-interactive glass-elevated p-6 rounded-2xl">
            <svg class="w-8 h-8 text-gray-300 dark:text-white/10 mb-3" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
            <p class="text-gray-700 dark:text-white/70 text-sm leading-relaxed italic">"{{ t.quote }}"</p>
            <div class="mt-4 flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-white/5">
              <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500/20 to-indigo-500/20 flex items-center justify-center text-gray-700 dark:text-white font-medium text-sm ring-1 ring-gray-300 dark:ring-white/10">{{ t.name_display.charAt(0) }}</div>
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ t.name_display }}</p>
                <p v-if="t.grade" class="text-xs text-gray-500 dark:text-white/30">{{ t.grade }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="text-center mt-10">
          <button @click="showTestimonialModal = true" class="btn-secondary group mx-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg> Share Your Story
            <svg class="w-3 h-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
      </div>
    </section>

    <!-- ================================================================ -->
    <!-- 7. Glass Research Data (Survey)                                     -->
    <!-- ================================================================ -->
    <section class="py-24 relative">
      <div class="absolute inset-0 pointer-events-none"><div class="absolute bottom-0 left-0 w-96 h-96 rounded-full bg-blue-600/5 blur-3xl"></div></div>
      <div class="relative max-w-4xl mx-auto px-4 text-center">
        <span class="inline-block px-4 py-1.5 rounded-full glass-surface text-xs font-medium text-gray-600 dark:text-white/50 uppercase tracking-wider mb-4">Research</span>
        <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white mb-4">Built on <span class="text-gradient">Real Student Data</span></h2>
        <p class="text-gray-500 dark:text-white/40 max-w-2xl mx-auto mb-12">We surveyed <span class="text-gray-900 dark:text-white font-semibold">{{ landing.surveyRespondents }}</span> students to understand exactly what they need to succeed.</p>
        <div v-if="landing.surveyStats && Object.keys(landing.surveyStats).length" class="grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div class="survey-card">
            <div class="text-4xl font-bold text-gradient">{{ landing.surveyStats.interested_percent }}%</div>
            <p class="text-sm text-gray-600 dark:text-white/50 mt-2">Want better scholarship matching</p>
            <div class="mt-3 w-full h-1.5 rounded-full bg-gray-200 dark:bg-white/5 overflow-hidden"><div class="h-full rounded-full bg-gradient-to-r from-blue-400 to-indigo-400 transition-all duration-1000" :style="{ width: landing.surveyStats.interested_percent + '%' }"></div></div>
          </div>
          <div class="survey-card">
            <div class="text-4xl font-bold text-gradient-green">{{ landing.surveyStats.want_ai_feedback_percent }}%</div>
            <p class="text-sm text-gray-600 dark:text-white/50 mt-2">Want AI feedback on documents</p>
            <div class="mt-3 w-full h-1.5 rounded-full bg-gray-200 dark:bg-white/5 overflow-hidden"><div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-400 transition-all duration-1000" :style="{ width: landing.surveyStats.want_ai_feedback_percent + '%' }"></div></div>
          </div>
          <div class="survey-card">
            <div class="text-4xl font-bold text-gradient-amber">{{ landing.surveyStats.willing_to_pay_percent }}%</div>
            <p class="text-sm text-gray-600 dark:text-white/50 mt-2">Would pay for premium features</p>
            <div class="mt-3 w-full h-1.5 rounded-full bg-gray-200 dark:bg-white/5 overflow-hidden"><div class="h-full rounded-full bg-gradient-to-r from-yellow-400 to-orange-400 transition-all duration-1000" :style="{ width: landing.surveyStats.willing_to_pay_percent + '%' }"></div></div>
          </div>
        </div>
        <div v-else class="glass-elevated p-8 rounded-2xl max-w-md mx-auto">
          <p class="text-gray-600 dark:text-white/50 text-sm">Help us build a better platform. Take our quick survey.</p>
          <button @click="showSurveyModal = true" class="mt-4 btn-primary">Take Survey</button>
        </div>
      </div>
    </section>

    <!-- ================================================================ -->
    <!-- 8. Glass Accordion (FAQ)                                            -->
    <!-- ================================================================ -->
    <section v-if="landing.faqs.length" class="py-24 relative">
      <div class="relative max-w-3xl mx-auto px-4">
        <div class="text-center mb-12">
          <span class="inline-block px-4 py-1.5 rounded-full glass-surface text-xs font-medium text-gray-600 dark:text-white/50 uppercase tracking-wider mb-4">Questions</span>
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Common <span class="text-gradient">Questions</span></h2>
        </div>
        <div class="space-y-3">
          <div v-for="faq in landing.faqs" :key="faq.id" class="glass-elevated rounded-2xl overflow-hidden transition-all duration-300 border border-gray-200 dark:border-white/5 hover:border-gray-300 dark:hover:border-white/10">
            <button @click="toggleFaq(faq.id)" class="faq-btn w-full flex items-center justify-between p-5 text-left">
              <span class="text-gray-900 dark:text-white font-medium">{{ faq.question }}</span>
              <div class="w-8 h-8 rounded-full glass-surface flex items-center justify-center shrink-0 transition-all duration-300" :class="{ 'rotate-180': openFaqs.includes(faq.id) }">
                <svg class="w-4 h-4 text-gray-500 dark:text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </div>
            </button>
            <div v-if="openFaqs.includes(faq.id)" class="px-5 pb-5 text-sm text-gray-600 dark:text-white/50 leading-relaxed border-t border-gray-200 dark:border-white/5 pt-4" v-html="faq.answer"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================================================================ -->
    <!-- 9. Glass Minimal Footer                                            -->
    <!-- ================================================================ -->
    <footer class="py-12 border-t border-gray-200 dark:border-white/5">
      <div class="max-w-7xl mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-8 mb-8">
          <div>
            <div class="flex items-center gap-2 mb-4"><div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center text-white font-bold text-sm">S</div><span class="text-gray-900 dark:text-white font-bold">ScholarshipHub</span></div>
            <p class="text-sm text-gray-500 dark:text-white/30">AI-powered scholarship matching for students worldwide.</p>
          </div>
          <div><h4 class="text-gray-600 dark:text-white/60 text-sm font-medium mb-3">Product</h4><ul class="space-y-2"><li><a href="#" class="footer-link">Features</a></li><li><a href="#" class="footer-link">Pricing</a></li><li><a href="#" class="footer-link">Changelog</a></li></ul></div>
          <div><h4 class="text-gray-600 dark:text-white/60 text-sm font-medium mb-3">Company</h4><ul class="space-y-2"><li><a href="#" class="footer-link">About</a></li><li><a href="#" class="footer-link">Blog</a></li><li><a href="#" class="footer-link">Contact</a></li></ul></div>
          <div><h4 class="text-gray-600 dark:text-white/60 text-sm font-medium mb-3">Legal</h4><ul class="space-y-2"><li><a href="#" class="footer-link">Privacy Policy</a></li><li><a href="#" class="footer-link">Terms of Service</a></li></ul></div>
        </div>
        <div class="pt-6 border-t border-gray-200 dark:border-white/5 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p class="text-xs text-gray-400 dark:text-white/20">&copy; {{ new Date().getFullYear() }} ScholarshipHub. Built with ❤️ for students.</p>
          <div class="flex gap-4">
            <a href="#" class="footer-social-link"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.302 3.438 9.8 8.205 11.387.6.113.82-.26.82-.58 0-.287-.01-1.05-.015-2.06-3.338.726-4.042-1.416-4.042-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.809 1.304 3.495.997.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.468-2.381 1.235-3.221-.123-.3-.535-1.52.117-3.16 0 0 1.008-.322 3.3 1.23.96-.267 1.98-.399 3-.399s2.04.132 3 .399c2.292-1.552 3.3-1.23 3.3-1.23.653 1.64.24 2.86.118 3.16.768.84 1.233 1.91 1.233 3.22 0 4.61-2.804 5.62-5.476 5.92.43.37.823 1.102.823 2.22 0 1.602-.015 2.894-.015 3.287 0 .322.216.698.825.578C20.565 21.795 24 17.298 24 12c0-6.627-5.373-12-12-12z"/></svg></a>
            <a href="#" class="footer-social-link"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.937 4.937 0 004.604 3.417 9.868 9.868 0 01-6.102 2.104c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 0021.967-3.48 13.94 13.94 0 001.51-6.187c0-.21-.006-.42-.02-.63A9.936 9.936 0 0024 4.59z"/></svg></a>
          </div>
        </div>
      </div>
    </footer>

    <!-- Testimonial modal (Ultra-Hero) -->
    <GlassModal v-model="showTestimonialModal" @close="showTestimonialModal = false">
      <div class="space-y-4">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Share Your Story</h3>
        <GlassInput v-model="testimonialForm.name_display" placeholder="Your name" />
        <GlassInput v-model="testimonialForm.grade" placeholder="Grade (optional)" />
        <GlassTextarea v-model="testimonialForm.quote" placeholder="Your experience..." rows="4" />
        <button @click="submitTestimonial" class="w-full py-2 bg-blue-600 rounded-lg text-white hover:bg-blue-700 disabled:opacity-50" :disabled="!testimonialForm.name_display || !testimonialForm.quote">Submit</button>
        <p v-if="testimonialSuccess" class="text-emerald-400 text-sm mt-2">Thank you! Your story has been submitted for review.</p>
      </div>
    </GlassModal>

    <!-- Survey modal (Ultra-Hero) -->
    <SurveyModal v-model="showSurveyModal" @submitted="onSurveySubmitted" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import GlassModal from '@/Components/GlassModal.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassTextarea from '@/Components/GlassTextarea.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import SurveyModal from '@/Components/SurveyModal.vue';

const page = usePage();

// Auth state
const currentUser = computed(() => page.props.auth?.user || null);

// Profile dropdown
const profileMenuOpen = ref(false);
const profileMenuRef = ref(null);

function handleClickOutside(event) {
  if (profileMenuRef.value && !profileMenuRef.value.contains(event.target)) {
    profileMenuOpen.value = false;
  }
}
onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));

const props = defineProps({
  activeScholarships: { type: Number, default: 0 },
  registeredStudents: { type: Number, default: 0 },
  documentsReviewed: { type: Number, default: 0 },
  faqs: { type: Array, default: () => [] },
  testimonials: { type: Array, default: () => [] },
  surveyStats: { type: Object, default: () => ({}) },
  surveyRespondents: { type: Number, default: 0 },
});

const openFaqs = ref([]);
const showTestimonialModal = ref(false);
const showSurveyModal = ref(false);
const testimonialForm = reactive({ name_display: '', grade: '', quote: '' });
const testimonialSuccess = ref(false);

const expandedCard = ref(null);

const cardDetails = [
  `Our platform aggregates scholarships from over 200 trusted sources, automatically filtering out duplicates and outdated listings. You’ll never need to visit dozens of websites again.`,
  `Our AI reviews check grammar, structure, ATS compatibility, and competitiveness. You’ll know exactly what to improve before you hit submit.`,
  `The pathway generator builds a personalised timeline based on your profile and deadlines. Each step includes concrete tasks and estimated score improvements.`,
  `Our matching algorithm weighs 40% academic fit, 30% demographic eligibility, 20% interest alignment, and 10% other requirements to rank scholarships by your unique profile.`,
  `Upload your CV, personal statement, or motivation letter and receive a detailed report with strengths, weaknesses, and actionable suggestions within seconds.`,
  `Receive a step‑by‑step roadmap tailored to your top scholarship matches. The pathway adapts as you complete tasks and new opportunities arise.`,
];

// Reactive local copy of all landing data
const landing = reactive({
  activeScholarships: props.activeScholarships,
  registeredStudents: props.registeredStudents,
  documentsReviewed: props.documentsReviewed,
  faqs: props.faqs,
  testimonials: props.testimonials,
  surveyStats: props.surveyStats,
  surveyRespondents: props.surveyRespondents,
});

async function refreshLandingData() {
  try {
    const { data } = await axios.get('/api/landing-data');
    Object.assign(landing, data);
  } catch (e) {
    console.error('Failed to refresh landing data:', e);
  }
}

function toggleCard(index) { expandedCard.value = expandedCard.value === index ? null : index; }
function toggleFaq(id) {
  const idx = openFaqs.value.indexOf(id);
  if (idx > -1) openFaqs.value.splice(idx, 1);
  else openFaqs.value.push(id);
}

async function submitTestimonial() {
  try {
    await axios.post('/api/testimonials', testimonialForm);
    testimonialSuccess.value = true;
    testimonialForm.name_display = '';
    testimonialForm.grade = '';
    testimonialForm.quote = '';
    await refreshLandingData();
    setTimeout(() => { testimonialSuccess.value = false; showTestimonialModal.value = false; }, 2000);
  } catch (e) { console.error('Testimonial submit error:', e); }
}

async function onSurveySubmitted() {
  showSurveyModal.value = false;
  await refreshLandingData();
}
</script>

<style scoped>
/* ================================================================
   PREMIUM GLASSMORPHISM LANDING – THEME-AWARE
   ================================================================ */

/* ---------- Z-PLANE GLASS DEPTHS ---------- */

/* Surface – light, subtle glass */
.glass-surface {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.3);
}
.dark .glass-surface {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.06);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

/* Elevated – thicker glass slab for interactive cards/buttons */
.glass-elevated,
.glass-nav-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.10);
  box-shadow:
    0 8px 32px rgba(0, 0, 0, 0.10),
    inset 0 1px 0 rgba(255, 255, 255, 0.5);
}
.dark .glass-elevated,
.dark .glass-nav-elevated {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.10);
  box-shadow:
    0 8px 32px rgba(0, 0, 0, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.08);
}

/* Hero – heavy glass for overlays (profile dropdown) */
.glass-hero {
  background: rgba(255, 255, 255, 0.45);
  backdrop-filter: blur(40px);
  -webkit-backdrop-filter: blur(40px);
  border: 1px solid rgba(0, 0, 0, 0.12);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}
.dark .glass-hero {
  background: rgba(255, 255, 255, 0.10);
  border-color: rgba(255, 255, 255, 0.12);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

/* ---------- 3D PERSPECTIVE & PHYSICS ---------- */
.perspective-1000 { perspective: 1000px; }

/* ---------- INTERACTIVE ELEMENTS ---------- */

/* Navigation links */
.nav-link {
  padding: 0.5rem 1rem;
  border-radius: 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.3s ease;
  will-change: transform;
  color: rgb(55 65 81);
  transform: rotateY(2deg);
}
.nav-link:hover {
  color: rgb(17 24 39);
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}
.nav-link:active {
  transform: scale(0.95) translateY(1px);
}
.dark .nav-link {
  color: rgba(255,255,255,0.6);
}
.dark .nav-link:hover {
  color: #fff;
}

/* Primary buttons */
.btn-primary {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  border-radius: 0.75rem;
  font-weight: 600;
  overflow: hidden;
  transition: all 0.3s ease;
  will-change: transform;
  background: linear-gradient(135deg, #3b82f6, #4f46e5);
  color: #fff;
  transform: rotateY(2deg);
  box-shadow: inset 0 1px 0 rgba(255,255,255,0.4), 0 4px 16px rgba(59,130,246,0.3);
  cursor: pointer;
}
.btn-primary:hover {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
  box-shadow: 0 0 60px rgba(59,130,246,0.4), inset 0 1px 0 rgba(255,255,255,0.4);
}
.btn-primary:active {
  transform: scale(0.95) translateY(1px);
}

/* Secondary buttons */
.btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 600;
  transition: all 0.3s ease;
  will-change: transform;
  background: rgba(255,255,255,0.25);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(0,0,0,0.08);
  color: #111827;
  transform: rotateY(2deg);
  cursor: pointer;
}
.dark .btn-secondary {
  background: rgba(255,255,255,0.05);
  border-color: rgba(255,255,255,0.10);
  color: #f3f4f6;
}
.btn-secondary:hover {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
  background: rgba(255,255,255,0.4);
}
.dark .btn-secondary:hover {
  background: rgba(255,255,255,0.1);
}
.btn-secondary:active {
  transform: scale(0.95) translateY(1px);
}

/* Profile menu links */
.profile-menu-link {
  display: block;
  padding: 0.625rem 1rem;
  font-size: 0.875rem;
  transition: all 0.2s ease;
  color: rgb(55 65 81);
  transform: rotateY(2deg);
  will-change: transform;
}
.profile-menu-link:hover {
  background: rgb(243 244 246);
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}
.dark .profile-menu-link {
  color: rgba(255,255,255,0.8);
}
.dark .profile-menu-link:hover {
  background: rgba(255,255,255,0.1);
}

/* Generic interactive card (problem, feature, testimonial) */
.card-interactive {
  position: relative;
  transition: all 0.5s ease;
  will-change: transform;
  cursor: pointer;
  transform: rotateY(2deg);
}
.card-interactive:hover {
  transform: rotateY(0deg) translateY(-4px) scale(1.02);
  box-shadow: 0 24px 60px rgba(0,0,0,0.15);
}
.card-interactive:active {
  transform: scale(0.95) translateY(2px);
}

/* Stat cards */
.stat-card {
  text-align: center;
  padding: 0.75rem;
  border-radius: 0.75rem;
  transition: all 0.3s ease;
  will-change: transform;
  background: rgba(255,255,255,0.25);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(0,0,0,0.08);
  transform: rotateY(2deg);
}
.stat-card:hover {
  transform: rotateY(0deg) translateY(-2px) scale(1.05);
  background: rgba(255,255,255,0.5);
  box-shadow: 0 8px 32px rgba(0,0,0,0.15);
}
.stat-card:active {
  transform: scale(0.95) translateY(1px);
}
.dark .stat-card {
  background: rgba(255,255,255,0.03);
  border-color: rgba(255,255,255,0.06);
}
.dark .stat-card:hover {
  background: rgba(255,255,255,0.08);
  box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}

.stat-label {
  font-size: 0.625rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: rgb(107 114 128);
}
.dark .stat-label {
  color: rgba(255,255,255,0.4);
}

/* Step cards */
.step-card {
  padding: 1.5rem;
  border-radius: 1rem;
  text-align: center;
  transition: all 0.5s ease;
  will-change: transform;
  background: rgba(255,255,255,0.25);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(0,0,0,0.06);
  transform: rotateY(2deg);
}
.step-card:hover {
  transform: rotateY(0deg) translateY(-4px) scale(1.02);
  background: rgba(255,255,255,0.5);
  box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
.step-card:active {
  transform: scale(0.95) translateY(2px);
}
.dark .step-card {
  background: rgba(255,255,255,0.02);
  border-color: rgba(255,255,255,0.06);
}
.dark .step-card:hover {
  background: rgba(255,255,255,0.06);
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

/* Step numbers */
.step-number {
  position: relative;
  width: 3.5rem;
  height: 3.5rem;
  margin: 0 auto 1rem;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: 1.25rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.step-number::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 9999px;
  filter: blur(24px);
  background: inherit;
  opacity: 0.2;
}
.step-1 { background: linear-gradient(135deg, #3b82f6, #4f46e5); }
.step-2 { background: linear-gradient(135deg, #4f46e5, #7c3aed); }
.step-3 { background: linear-gradient(135deg, #7c3aed, #ec4899); }
.step-4 { background: linear-gradient(135deg, #10b981, #059669); }

/* Survey cards */
.survey-card {
  padding: 1.5rem;
  border-radius: 1rem;
  transition: all 0.5s ease;
  will-change: transform;
  background: rgba(255,255,255,0.25);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(0,0,0,0.06);
  transform: rotateY(2deg);
}
.survey-card:hover {
  transform: rotateY(0deg) translateY(-4px) scale(1.05);
  background: rgba(255,255,255,0.5);
  box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
.survey-card:active {
  transform: scale(0.95) translateY(2px);
}
.dark .survey-card {
  background: rgba(255,255,255,0.02);
  border-color: rgba(255,255,255,0.06);
}
.dark .survey-card:hover {
  background: rgba(255,255,255,0.06);
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

/* FAQ button */
.faq-btn {
  transition: background-color 0.2s ease;
}
.faq-btn:hover {
  background: rgb(243 244 246);
}
.dark .faq-btn:hover {
  background: rgba(255,255,255,0.05);
}

/* Footer links */
.footer-link {
  font-size: 0.875rem;
  transition: all 0.2s ease;
  will-change: transform;
  display: inline-block;
  color: rgb(107 114 128);
  transform: rotateY(1deg);
}
.footer-link:hover {
  color: rgb(17 24 39);
  transform: rotateY(0deg) translateY(-1px) scale(1.02);
}
.dark .footer-link {
  color: rgba(255,255,255,0.3);
}
.dark .footer-link:hover {
  color: rgba(255,255,255,0.6);
}

.footer-social-link {
  color: rgb(156 163 175);
  transition: all 0.2s ease;
  transform: rotateY(1deg);
}
.footer-social-link:hover {
  color: rgb(75 85 99);
  transform: rotateY(0deg) translateY(-1px) scale(1.05);
}
.dark .footer-social-link {
  color: rgba(255,255,255,0.2);
}
.dark .footer-social-link:hover {
  color: rgba(255,255,255,0.4);
}

/* ---------- TYPOGRAPHY GRADIENTS ---------- */
.text-gradient {
  background: linear-gradient(135deg, #60a5fa, #818cf8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.text-gradient-green {
  background: linear-gradient(135deg, #34d399, #059669);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.text-gradient-amber {
  background: linear-gradient(135deg, #fbbf24, #f59e0b);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* ---------- GLASS HERO CARD (Dashboard preview) ---------- */
.glass-hero-card {
  background: rgba(255,255,255,0.25);
  backdrop-filter: blur(24px);
  border: 1px solid rgba(0,0,0,0.08);
  box-shadow: 0 20px 80px rgba(0,0,0,0.15), inset 0 1px 0 rgba(255,255,255,0.5);
  transform: rotateY(-2deg) rotateX(1deg);
  transition: all 0.7s cubic-bezier(0.4,0,0.2,1);
}
.glass-hero-card:hover {
  transform: rotateY(0deg) rotateX(0deg) scale(1.02);
  box-shadow: 0 30px 80px rgba(0,0,0,0.25), inset 0 1px 0 rgba(255,255,255,0.6);
}
.dark .glass-hero-card {
  background: rgba(255,255,255,0.05);
  border-color: rgba(255,255,255,0.08);
  box-shadow: 0 20px 80px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.08);
}
.dark .glass-hero-card:hover {
  border-color: rgba(255,255,255,0.15);
  box-shadow: 0 30px 80px rgba(0,0,0,0.6);
}

/* ---------- AMBIENT BACKLIGHT GLOWS ---------- */
.nav-link::before,
.btn-secondary::before,
.profile-menu-link::before,
.footer-link::before {
  content: '';
  position: absolute;
  inset: -4px;
  border-radius: inherit;
  background: linear-gradient(135deg, #60a5fa, #818cf8);
  opacity: 0;
  transition: opacity 0.4s;
  filter: blur(12px);
  z-index: -1;
}
.nav-link:hover::before,
.btn-secondary:hover::before,
.profile-menu-link:hover::before,
.footer-link:hover::before {
  opacity: 0.3;
}

/* ---------- ANIMATIONS ---------- */
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

@keyframes gradient {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
.animate-gradient { background-size: 200% 200%; animation: gradient 4s ease-in-out infinite; }

@keyframes ping-slow {
  0%, 100% { transform: scale(1); opacity: 0.3; }
  50% { transform: scale(1.3); opacity: 0; }
}
.animate-ping-slow { animation: ping-slow 3s ease-in-out infinite; }

/* ---------- TRANSITIONS ---------- */
.menu-fade-enter-active, .menu-fade-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.menu-fade-enter-from, .menu-fade-leave-to { opacity: 0; transform: translateY(-4px); }

.expand-enter-active, .expand-leave-active { transition: all 0.3s ease; overflow: hidden; }
.expand-enter-from, .expand-leave-to { opacity: 0; max-height: 0; }
.expand-enter-to, .expand-leave-from { opacity: 1; max-height: 500px; }

/* ---------- ACCESSIBILITY ---------- */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation: none !important;
    transition: none !important;
  }
  .card-interactive,
  .nav-link,
  .btn-primary,
  .btn-secondary,
  .stat-card,
  .step-card,
  .survey-card {
    transform: none !important;
  }
}

@media (max-width: 768px) {
  .card-interactive,
  .nav-link,
  .btn-primary,
  .btn-secondary,
  .stat-card,
  .step-card,
  .survey-card {
    transform: none !important;
  }
  .card-interactive:hover,
  .nav-link:hover,
  .btn-primary:hover,
  .btn-secondary:hover,
  .stat-card:hover,
  .step-card:hover,
  .survey-card:hover {
    transform: none !important;
  }
}
</style>