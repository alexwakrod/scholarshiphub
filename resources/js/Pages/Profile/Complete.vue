### FILE: resources/js/Pages/Profile/Complete.vue ###
<template>
  <AppLayout>
    <div class="relative min-h-screen overflow-hidden p-4 md:p-6">
      <!-- Animated Background -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/4 right-1/4 w-96 h-96 rounded-full bg-blue-600/5 blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 left-1/4 w-[500px] h-[500px] rounded-full bg-indigo-600/5 blur-3xl animate-pulse-slow" style="animation-delay: 1s"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-purple-600/3 blur-3xl"></div>
        <div class="absolute top-20 right-10 w-3 h-3 rounded-full bg-blue-400/20 animate-float"></div>
        <div class="absolute bottom-40 right-1/4 w-4 h-4 rounded-full bg-indigo-400/20 animate-float" style="animation-delay: 0.7s"></div>
        <div class="absolute top-1/3 left-10 w-2 h-2 rounded-full bg-purple-400/20 animate-float" style="animation-delay: 1.4s"></div>
        <div class="absolute bottom-20 left-1/3 w-3 h-3 rounded-full bg-pink-400/20 animate-float" style="animation-delay: 2s"></div>
      </div>

      <div class="relative max-w-4xl mx-auto space-y-6" v-if="!loading">
        <!-- Header -->
        <div class="perspective-1000">
          <GlassCard variant="elevated" class="p-4 rounded-2xl transform -rotate-y-2 transition-all duration-700 hover:rotate-y-1">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
              <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                  Complete Your <span class="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Profile</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-white/40">Let's get to know you better for perfect matches</p>
              </div>
              <button
                @click="goToDashboard"
                class="text-sm text-gray-500 dark:text-white/30 hover:text-gray-700 dark:hover:text-white/60 transition-colors underline"
              >
                Skip for now
              </button>
            </div>
          </GlassCard>
        </div>

        <!-- Wizard Card -->
        <div class="perspective-1000">
          <GlassCard variant="hero" class="p-4 md:p-6 rounded-3xl transform -rotate-y-3 transition-all duration-700 hover:rotate-y-1">
            <!-- Step Indicator -->
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-white/40">
                <span>Step {{ currentStepIndex + 1 }}</span>
                <span class="w-1 h-1 rounded-full bg-gray-400 dark:bg-white/20"></span>
                <span>of {{ wizardSteps.length }}</span>
              </div>
              <div class="flex items-center gap-1.5">
                <div
                  v-for="i in wizardSteps.length"
                  :key="i"
                  class="h-1.5 w-6 rounded-full transition-all duration-500"
                  :class="[
                    i - 1 < currentStepIndex ? 'bg-gradient-to-r from-blue-400 to-indigo-400' : '',
                    i - 1 === currentStepIndex ? 'bg-blue-400/50 animate-pulse' : '',
                    i - 1 > currentStepIndex ? 'bg-gray-200 dark:bg-white/10' : ''
                  ]"
                ></div>
              </div>
            </div>

            <!-- Wizard Content -->
            <div class="transition-all duration-300">
              <!-- Step 1: About You -->
              <div v-show="currentStepIndex === 0" class="space-y-5">
                <div class="flex items-center gap-2.5 mb-2">
                  <div class="w-8 h-8 rounded-full bg-blue-500/10 dark:bg-blue-500/20 flex items-center justify-center text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">About You</h3>
                </div>

                <div class="space-y-4">
                  <GlassInputWrapper
                    v-model="wizardData.full_name"
                    label="Full Name"
                    required
                    placeholder="e.g. Ahmed Mohamed"
                    :error="stepErrors.full_name"
                    tooltip="Enter your full legal name as it appears on official documents. This helps scholarships verify your identity."
                    example="Ahmed Mohamed Ali"
                  />

                  <GlassInputWrapper
                    v-model="wizardData.date_of_birth"
                    label="Date of Birth"
                    required
                    :error="stepErrors.date_of_birth"
                    tooltip="Your date of birth is used to determine eligibility for age-restricted scholarships."
                    example="15 January 2000"
                  >
                    <GlassDatePicker v-model="wizardData.date_of_birth" placeholder="mm/dd/yyyy" />
                  </GlassInputWrapper>

                  <GlassInputWrapper
                    v-model="wizardData.country"
                    label="Country"
                    required
                    :error="stepErrors.country"
                    tooltip="Select your country of citizenship. Many scholarships are country-specific."
                    example="Egypt, United States, etc."
                  >
                    <GlassSelect
                      v-model="wizardData.country"
                      :options="countryOptions"
                      placeholder="Select your country..."
                      searchable
                    />
                  </GlassInputWrapper>

                  <GlassInputWrapper
                    v-model="wizardData.bio"
                    label="Bio"
                    placeholder="Tell us about yourself..."
                    :error="stepErrors.bio"
                    tooltip="Tell us a bit about yourself, your passions, and what drives you. This helps us personalize your experience."
                    example="I am a passionate computer science student interested in AI and its applications in education."
                  >
                    <GlassTextarea
                      v-model="wizardData.bio"
                      placeholder="Tell us about yourself..."
                      :rows="3"
                      maxlength="2000"
                    />
                    <CharCounter :current="(wizardData.bio || '').length" :max="2000" class="mt-1" />
                  </GlassInputWrapper>
                </div>
              </div>

              <!-- Step 2: Education -->
              <div v-show="currentStepIndex === 1" class="space-y-5">
                <div class="flex items-center gap-2.5 mb-2">
                  <div class="w-8 h-8 rounded-full bg-indigo-500/10 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                  </div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Education</h3>
                </div>

                <div class="space-y-4">
                  <GlassInputWrapper
                    label="Current Education Level"
                    required
                    :error="stepErrors.academic_level"
                    tooltip="Select the highest level of education you have completed or are currently pursuing."
                  >
                    <div class="grid grid-cols-2 gap-3">
                      <button
                        v-for="level in educationLevels"
                        :key="level.value"
                        type="button"
                        @click="wizardData.academic_level = level.value"
                        class="p-3 rounded-xl border transition-all duration-300 text-sm hover:scale-105"
                        :class="wizardData.academic_level === level.value ? 'border-blue-400 bg-blue-500/10 dark:bg-blue-500/20 text-gray-900 dark:text-white shadow-[0_0_30px_rgba(59,130,246,0.1)]' : 'border-gray-300 dark:border-white/10 text-gray-600 dark:text-white/50 hover:border-gray-400 dark:hover:border-white/30 hover:bg-gray-100 dark:hover:bg-white/5'"
                      >
                        {{ level.label }}
                      </button>
                    </div>
                  </GlassInputWrapper>

                  <GlassInputWrapper
                    v-model="wizardData.major"
                    label="Field of Study / Major"
                    required
                    :error="stepErrors.major"
                    tooltip="Select the primary subject area you are studying. This helps match you with relevant scholarships."
                    example="Computer Science, Engineering, Medicine..."
                  >
                    <GlassSelect
                      v-model="wizardData.major"
                      :options="majorOptions"
                      placeholder="Select your major..."
                      searchable
                    />
                  </GlassInputWrapper>

                  <GlassInputWrapper
                    label="Target Degree"
                    :error="stepErrors.target_degree"
                    tooltip="What degree are you aiming for? This helps us recommend scholarships for your educational goal."
                  >
                    <div class="grid grid-cols-2 gap-3">
                      <button
                        v-for="deg in targetDegrees"
                        :key="deg.value"
                        type="button"
                        @click="wizardData.target_degree = deg.value"
                        class="p-3 rounded-xl border transition-all duration-300 text-sm hover:scale-105"
                        :class="wizardData.target_degree === deg.value ? 'border-blue-400 bg-blue-500/10 dark:bg-blue-500/20 text-gray-900 dark:text-white shadow-[0_0_30px_rgba(59,130,246,0.1)]' : 'border-gray-300 dark:border-white/10 text-gray-600 dark:text-white/50 hover:border-gray-400 dark:hover:border-white/30 hover:bg-gray-100 dark:hover:bg-white/5'"
                      >
                        {{ deg.label }}
                      </button>
                    </div>
                  </GlassInputWrapper>

                  <GlassInputWrapper
                    v-model="wizardData.gpa"
                    label="GPA"
                    placeholder="e.g. 3.75"
                    :error="stepErrors.gpa"
                    tooltip="Enter your cumulative GPA on a 0.00 - 4.00 scale. This helps match you with merit-based scholarships."
                    example="3.75"
                  >
                    <GlassNumberInput v-model="wizardData.gpa" :min="0" :max="4.0" :step="0.01" placeholder="e.g. 3.5" />
                  </GlassInputWrapper>

                  <GlassInputWrapper
                    v-model="wizardData.english_proficiency"
                    label="English Proficiency"
                    placeholder="e.g. IELTS 7.0"
                    :error="stepErrors.english_proficiency"
                    tooltip="List your English test scores or self-assessed level. Used for scholarships requiring language proficiency."
                    example="IELTS 7.0, TOEFL 100, Fluent"
                  />
                </div>
              </div>

              <!-- Step 3: Experience -->
              <div v-show="currentStepIndex === 2" class="space-y-5">
                <div class="flex items-center gap-2.5 mb-2">
                  <div class="w-8 h-8 rounded-full bg-green-500/10 dark:bg-green-500/20 flex items-center justify-center text-green-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Experience</h3>
                </div>

                <GlassInputWrapper
                  label="Work Experience"
                  :error="stepErrors.has_work_experience"
                  tooltip="Check if you have any work experience, including internships. This can make you a stronger candidate."
                >
                  <GlassCheckbox v-model="wizardData.has_work_experience" label="I have work experience (including internships)" />
                </GlassInputWrapper>

                <GlassInputWrapper
                  label="Research Experience"
                  :error="stepErrors.has_research_experience"
                  tooltip="Check if you have any research experience, such as publications, projects, or a thesis. Valued for research scholarships."
                >
                  <GlassCheckbox v-model="wizardData.has_research_experience" label="I have research experience (publications, projects, thesis)" />
                </GlassInputWrapper>
              </div>

              <!-- Step 4: Demographics -->
              <div v-show="currentStepIndex === 3" class="space-y-5">
                <div class="flex items-center gap-2.5 mb-2">
                  <div class="w-8 h-8 rounded-full bg-purple-500/10 dark:bg-purple-500/20 flex items-center justify-center text-purple-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Demographics</h3>
                </div>

                <div class="space-y-4">
                  <GlassInputWrapper
                    label="Income"
                    :error="stepErrors.income"
                    tooltip="Select your family income level. Some scholarships are need-based."
                  >
                    <GlassSelect
                      v-model="wizardData.demographics.income"
                      :options="incomeOptions"
                      placeholder="Select income level"
                    />
                  </GlassInputWrapper>

                  <GlassInputWrapper
                    v-model="wizardData.demographics.ethnicity"
                    label="Ethnicity"
                    placeholder="e.g. Arab, Berber, etc."
                    :error="stepErrors.ethnicity"
                    tooltip="Your ethnicity helps us identify diversity scholarships you may qualify for."
                    example="Arab, Berber, etc."
                  />
                </div>
              </div>

              <!-- Step 5: Preferences -->
              <div v-show="currentStepIndex === 4" class="space-y-5">
                <div class="flex items-center gap-2.5 mb-2">
                  <div class="w-8 h-8 rounded-full bg-orange-500/10 dark:bg-orange-500/20 flex items-center justify-center text-orange-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                  </div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Preferences</h3>
                </div>

                <div class="space-y-4">
                  <GlassInputWrapper
                    label="English Level"
                    required
                    :error="stepErrors.english_level"
                    tooltip="Select your English proficiency level. Many scholarships require a certain level."
                  >
                    <div class="grid grid-cols-2 gap-3">
                      <button
                        v-for="level in englishLevels"
                        :key="level.value"
                        type="button"
                        @click="selectEnglishLevel(level)"
                        class="p-3 rounded-xl border transition-all duration-300 text-sm hover:scale-105 text-left"
                        :class="wizardData.english_level === level.value ? 'border-blue-400 bg-blue-500/10 dark:bg-blue-500/20 text-gray-900 dark:text-white shadow-[0_0_30px_rgba(59,130,246,0.1)]' : 'border-gray-300 dark:border-white/10 text-gray-600 dark:text-white/50 hover:border-gray-400 dark:hover:border-white/30 hover:bg-gray-100 dark:hover:bg-white/5'"
                      >
                        <div class="font-medium">{{ level.label }}</div>
                        <div class="text-[10px] text-gray-500 dark:text-white/30">{{ level.description }}</div>
                      </button>
                    </div>
                    <div v-if="showScoreInput" class="mt-4">
                      <GlassInputWrapper
                        v-model="wizardData.english_score"
                        label="Score"
                        :error="stepErrors.english_score"
                        tooltip="Enter your test score for the selected English test."
                      >
                        <GlassNumberInput
                          v-model="wizardData.english_score"
                          :min="scoreMin"
                          :max="scoreMax"
                          :step="scoreMax <= 9 ? 0.5 : 1"
                          :placeholder="'Enter score (' + scoreMin + ' - ' + scoreMax + ')'"
                        />
                      </GlassInputWrapper>
                    </div>
                  </GlassInputWrapper>

                  <GlassInputWrapper
                    label="Budget / Funding"
                    required
                    :error="stepErrors.budget"
                    tooltip="Select your current funding situation. This helps match you with scholarships that fit your financial needs."
                  >
                    <div class="grid grid-cols-2 gap-3">
                      <button
                        v-for="b in budgetOptions"
                        :key="b.value"
                        type="button"
                        @click="wizardData.budget = b.value"
                        class="p-3 rounded-xl border transition-all duration-300 text-sm hover:scale-105"
                        :class="wizardData.budget === b.value ? 'border-blue-400 bg-blue-500/10 dark:bg-blue-500/20 text-gray-900 dark:text-white shadow-[0_0_30px_rgba(59,130,246,0.1)]' : 'border-gray-300 dark:border-white/10 text-gray-600 dark:text-white/50 hover:border-gray-400 dark:hover:border-white/30 hover:bg-gray-100 dark:hover:bg-white/5'"
                      >
                        {{ b.label }}
                      </button>
                    </div>
                  </GlassInputWrapper>

                  <GlassInputWrapper
                    v-model="wizardData.languages"
                    label="Languages you speak"
                    required
                    :error="stepErrors.languages"
                    tooltip="Select all languages you speak. Some scholarships require specific language skills."
                  >
                    <GlassSelect
                      v-model="wizardData.languages"
                      :options="languageOptions"
                      placeholder="Select languages..."
                      :multiple="true"
                      searchable
                    />
                  </GlassInputWrapper>

                  <GlassInputWrapper
                    v-model="wizardData.interests"
                    label="Interests"
                    required
                    :error="stepErrors.interests"
                    tooltip="Select your academic and career interests. This helps us find scholarships aligned with your goals."
                  >
                    <GlassSelect
                      v-model="wizardData.interests"
                      :options="interestOptions"
                      placeholder="Select your interests..."
                      :multiple="true"
                      searchable
                    />
                  </GlassInputWrapper>
                </div>
              </div>

              <!-- Step 6: Review -->
              <div v-show="currentStepIndex === 5" class="space-y-5">
                <div class="flex items-center gap-2.5 mb-2">
                  <div class="w-8 h-8 rounded-full bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                  </div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Review</h3>
                </div>
                <p class="text-sm text-gray-500 dark:text-white/40">Make sure everything looks right before saving.</p>
                <div class="space-y-2 text-sm max-h-60 overflow-y-auto custom-scrollbar">
                  <div v-for="row in reviewData" :key="row.question" class="flex justify-between py-1.5 border-b border-gray-200 dark:border-white/5">
                    <span class="text-gray-500 dark:text-white/40">{{ row.question }}</span>
                    <span class="text-gray-900 dark:text-white/80 font-mono text-xs">{{ row.answer || '—' }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex items-center justify-between mt-8 pt-4 border-t border-gray-200 dark:border-white/5">
              <button
                v-if="currentStepIndex > 0"
                @click="prevStep"
                class="group relative px-5 py-2.5 rounded-xl glass-surface hover:glass-elevated transition-all duration-300 text-gray-600 dark:text-white/50 hover:text-gray-900 dark:hover:text-white flex items-center gap-2 text-sm"
              >
                <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
              </button>
              <div v-else></div>

              <button
                v-if="currentStepIndex < wizardSteps.length - 1"
                @click="nextStep"
                class="group relative px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(59,130,246,0.3)] hover:-translate-y-0.5 flex items-center gap-2 text-sm"
              >
                <span class="relative z-10">Next</span>
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
              </button>

              <button
                v-else
                @click="submitProfile"
                class="group relative px-8 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(16,185,129,0.3)] hover:-translate-y-0.5 flex items-center gap-2 text-sm"
                :disabled="submitting"
              >
                <span v-if="submitting" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
                <span v-else>Save Profile</span>
                <svg v-if="!submitting" class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-teal-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
              </button>
            </div>
          </GlassCard>
        </div>
      </div>

      <div v-else class="flex items-center justify-center min-h-[60vh]">
        <Spinner size="32" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue';
import { usePage, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassDatePicker from '@/Components/GlassDatePicker.vue';
import GlassSelect from '@/Components/GlassSelect.vue';
import GlassNumberInput from '@/Components/GlassNumberInput.vue';
import GlassCheckbox from '@/Components/GlassCheckbox.vue';
import GlassTextarea from '@/Components/GlassTextarea.vue';
import CharCounter from '@/Components/CharCounter.vue';
import Spinner from '@/Components/Spinner.vue';
import GlassInputWrapper from '@/Components/GlassInputWrapper.vue';

const page = usePage();
const existingProfile = computed(() => page.props.auth?.profile);
const loading = ref(false);
const submitting = ref(false);
const currentStepIndex = ref(0);

// Redirect if already complete
onMounted(() => {
  const profile = existingProfile.value;
  if (profile && profile.profile_completed === true) {
    router.visit('/profile');
  }
});

const stepErrors = reactive({});

const initWizardData = () => {
  const p = existingProfile.value || {};
  return {
    full_name: p.full_name || '',
    date_of_birth: p.date_of_birth || '',
    country: p.country || '',
    academic_level: p.academic_level || '',
    major: p.major || '',
    target_degree: p.demographics?.target_degree || '',
    gpa: p.gpa || '',
    english_proficiency: p.english_proficiency || '',
    has_work_experience: p.demographics?.has_work_experience || false,
    has_research_experience: p.demographics?.has_research_experience || false,
    english_level: p.demographics?.english_level || '',
    english_score: p.demographics?.english_score || '',
    budget: p.demographics?.budget || '',
    languages: p.languages || [],
    interests: p.interests || [],
    bio: p.bio || '',
    demographics: {
      income: p.demographics?.income || '',
      ethnicity: p.demographics?.ethnicity || '',
    },
  };
};

const wizardData = reactive(initWizardData());

// All option arrays unchanged from original specification
const countryOptions = [
  { value: 'Afghanistan', label: 'Afghanistan' },
  { value: 'Albania', label: 'Albania' },
  { value: 'Algeria', label: 'Algeria' },
  { value: 'Andorra', label: 'Andorra' },
  { value: 'Angola', label: 'Angola' },
  { value: 'Argentina', label: 'Argentina' },
  { value: 'Armenia', label: 'Armenia' },
  { value: 'Australia', label: 'Australia' },
  { value: 'Austria', label: 'Austria' },
  { value: 'Azerbaijan', label: 'Azerbaijan' },
  { value: 'Bahamas', label: 'Bahamas' },
  { value: 'Bahrain', label: 'Bahrain' },
  { value: 'Bangladesh', label: 'Bangladesh' },
  { value: 'Barbados', label: 'Barbados' },
  { value: 'Belarus', label: 'Belarus' },
  { value: 'Belgium', label: 'Belgium' },
  { value: 'Belize', label: 'Belize' },
  { value: 'Benin', label: 'Benin' },
  { value: 'Bhutan', label: 'Bhutan' },
  { value: 'Bolivia', label: 'Bolivia' },
  { value: 'Bosnia and Herzegovina', label: 'Bosnia and Herzegovina' },
  { value: 'Botswana', label: 'Botswana' },
  { value: 'Brazil', label: 'Brazil' },
  { value: 'Brunei', label: 'Brunei' },
  { value: 'Bulgaria', label: 'Bulgaria' },
  { value: 'Burkina Faso', label: 'Burkina Faso' },
  { value: 'Burundi', label: 'Burundi' },
  { value: 'Cabo Verde', label: 'Cabo Verde' },
  { value: 'Cambodia', label: 'Cambodia' },
  { value: 'Cameroon', label: 'Cameroon' },
  { value: 'Canada', label: 'Canada' },
  { value: 'Central African Republic', label: 'Central African Republic' },
  { value: 'Chad', label: 'Chad' },
  { value: 'Chile', label: 'Chile' },
  { value: 'China', label: 'China' },
  { value: 'Colombia', label: 'Colombia' },
  { value: 'Comoros', label: 'Comoros' },
  { value: 'Congo', label: 'Congo' },
  { value: 'Costa Rica', label: 'Costa Rica' },
  { value: 'Croatia', label: 'Croatia' },
  { value: 'Cuba', label: 'Cuba' },
  { value: 'Cyprus', label: 'Cyprus' },
  { value: 'Czech Republic', label: 'Czech Republic' },
  { value: 'Denmark', label: 'Denmark' },
  { value: 'Djibouti', label: 'Djibouti' },
  { value: 'Dominica', label: 'Dominica' },
  { value: 'Dominican Republic', label: 'Dominican Republic' },
  { value: 'Ecuador', label: 'Ecuador' },
  { value: 'Egypt', label: 'Egypt' },
  { value: 'El Salvador', label: 'El Salvador' },
  { value: 'Equatorial Guinea', label: 'Equatorial Guinea' },
  { value: 'Eritrea', label: 'Eritrea' },
  { value: 'Estonia', label: 'Estonia' },
  { value: 'Eswatini', label: 'Eswatini' },
  { value: 'Ethiopia', label: 'Ethiopia' },
  { value: 'Fiji', label: 'Fiji' },
  { value: 'Finland', label: 'Finland' },
  { value: 'France', label: 'France' },
  { value: 'Gabon', label: 'Gabon' },
  { value: 'Gambia', label: 'Gambia' },
  { value: 'Georgia', label: 'Georgia' },
  { value: 'Germany', label: 'Germany' },
  { value: 'Ghana', label: 'Ghana' },
  { value: 'Greece', label: 'Greece' },
  { value: 'Grenada', label: 'Grenada' },
  { value: 'Guatemala', label: 'Guatemala' },
  { value: 'Guinea', label: 'Guinea' },
  { value: 'Guinea-Bissau', label: 'Guinea-Bissau' },
  { value: 'Guyana', label: 'Guyana' },
  { value: 'Haiti', label: 'Haiti' },
  { value: 'Honduras', label: 'Honduras' },
  { value: 'Hungary', label: 'Hungary' },
  { value: 'Iceland', label: 'Iceland' },
  { value: 'India', label: 'India' },
  { value: 'Indonesia', label: 'Indonesia' },
  { value: 'Iran', label: 'Iran' },
  { value: 'Iraq', label: 'Iraq' },
  { value: 'Ireland', label: 'Ireland' },
  { value: 'Israel', label: 'Israel' },
  { value: 'Italy', label: 'Italy' },
  { value: 'Jamaica', label: 'Jamaica' },
  { value: 'Japan', label: 'Japan' },
  { value: 'Jordan', label: 'Jordan' },
  { value: 'Kazakhstan', label: 'Kazakhstan' },
  { value: 'Kenya', label: 'Kenya' },
  { value: 'Kiribati', label: 'Kiribati' },
  { value: 'Kuwait', label: 'Kuwait' },
  { value: 'Kyrgyzstan', label: 'Kyrgyzstan' },
  { value: 'Laos', label: 'Laos' },
  { value: 'Latvia', label: 'Latvia' },
  { value: 'Lebanon', label: 'Lebanon' },
  { value: 'Lesotho', label: 'Lesotho' },
  { value: 'Liberia', label: 'Liberia' },
  { value: 'Libya', label: 'Libya' },
  { value: 'Liechtenstein', label: 'Liechtenstein' },
  { value: 'Lithuania', label: 'Lithuania' },
  { value: 'Luxembourg', label: 'Luxembourg' },
  { value: 'Madagascar', label: 'Madagascar' },
  { value: 'Malawi', label: 'Malawi' },
  { value: 'Malaysia', label: 'Malaysia' },
  { value: 'Maldives', label: 'Maldives' },
  { value: 'Mali', label: 'Mali' },
  { value: 'Malta', label: 'Malta' },
  { value: 'Marshall Islands', label: 'Marshall Islands' },
  { value: 'Mauritania', label: 'Mauritania' },
  { value: 'Mauritius', label: 'Mauritius' },
  { value: 'Mexico', label: 'Mexico' },
  { value: 'Micronesia', label: 'Micronesia' },
  { value: 'Moldova', label: 'Moldova' },
  { value: 'Monaco', label: 'Monaco' },
  { value: 'Mongolia', label: 'Mongolia' },
  { value: 'Montenegro', label: 'Montenegro' },
  { value: 'Morocco', label: 'Morocco' },
  { value: 'Mozambique', label: 'Mozambique' },
  { value: 'Myanmar', label: 'Myanmar' },
  { value: 'Namibia', label: 'Namibia' },
  { value: 'Nauru', label: 'Nauru' },
  { value: 'Nepal', label: 'Nepal' },
  { value: 'Netherlands', label: 'Netherlands' },
  { value: 'New Zealand', label: 'New Zealand' },
  { value: 'Nicaragua', label: 'Nicaragua' },
  { value: 'Niger', label: 'Niger' },
  { value: 'Nigeria', label: 'Nigeria' },
  { value: 'North Korea', label: 'North Korea' },
  { value: 'North Macedonia', label: 'North Macedonia' },
  { value: 'Norway', label: 'Norway' },
  { value: 'Oman', label: 'Oman' },
  { value: 'Pakistan', label: 'Pakistan' },
  { value: 'Palau', label: 'Palau' },
  { value: 'Palestine', label: 'Palestine' },
  { value: 'Panama', label: 'Panama' },
  { value: 'Papua New Guinea', label: 'Papua New Guinea' },
  { value: 'Paraguay', label: 'Paraguay' },
  { value: 'Peru', label: 'Peru' },
  { value: 'Philippines', label: 'Philippines' },
  { value: 'Poland', label: 'Poland' },
  { value: 'Portugal', label: 'Portugal' },
  { value: 'Qatar', label: 'Qatar' },
  { value: 'Romania', label: 'Romania' },
  { value: 'Russia', label: 'Russia' },
  { value: 'Rwanda', label: 'Rwanda' },
  { value: 'Saint Kitts and Nevis', label: 'Saint Kitts and Nevis' },
  { value: 'Saint Lucia', label: 'Saint Lucia' },
  { value: 'Saint Vincent and the Grenadines', label: 'Saint Vincent and the Grenadines' },
  { value: 'Samoa', label: 'Samoa' },
  { value: 'San Marino', label: 'San Marino' },
  { value: 'Sao Tome and Principe', label: 'Sao Tome and Principe' },
  { value: 'Saudi Arabia', label: 'Saudi Arabia' },
  { value: 'Senegal', label: 'Senegal' },
  { value: 'Serbia', label: 'Serbia' },
  { value: 'Seychelles', label: 'Seychelles' },
  { value: 'Sierra Leone', label: 'Sierra Leone' },
  { value: 'Singapore', label: 'Singapore' },
  { value: 'Slovakia', label: 'Slovakia' },
  { value: 'Slovenia', label: 'Slovenia' },
  { value: 'Solomon Islands', label: 'Solomon Islands' },
  { value: 'Somalia', label: 'Somalia' },
  { value: 'South Africa', label: 'South Africa' },
  { value: 'South Korea', label: 'South Korea' },
  { value: 'South Sudan', label: 'South Sudan' },
  { value: 'Spain', label: 'Spain' },
  { value: 'Sri Lanka', label: 'Sri Lanka' },
  { value: 'Sudan', label: 'Sudan' },
  { value: 'Suriname', label: 'Suriname' },
  { value: 'Sweden', label: 'Sweden' },
  { value: 'Switzerland', label: 'Switzerland' },
  { value: 'Syria', label: 'Syria' },
  { value: 'Taiwan', label: 'Taiwan' },
  { value: 'Tajikistan', label: 'Tajikistan' },
  { value: 'Tanzania', label: 'Tanzania' },
  { value: 'Thailand', label: 'Thailand' },
  { value: 'Timor-Leste', label: 'Timor-Leste' },
  { value: 'Togo', label: 'Togo' },
  { value: 'Tonga', label: 'Tonga' },
  { value: 'Trinidad and Tobago', label: 'Trinidad and Tobago' },
  { value: 'Tunisia', label: 'Tunisia' },
  { value: 'Turkey', label: 'Turkey' },
  { value: 'Turkmenistan', label: 'Turkmenistan' },
  { value: 'Tuvalu', label: 'Tuvalu' },
  { value: 'Uganda', label: 'Uganda' },
  { value: 'Ukraine', label: 'Ukraine' },
  { value: 'United Arab Emirates', label: 'United Arab Emirates' },
  { value: 'United Kingdom', label: 'United Kingdom' },
  { value: 'United States', label: 'United States' },
  { value: 'Uruguay', label: 'Uruguay' },
  { value: 'Uzbekistan', label: 'Uzbekistan' },
  { value: 'Vanuatu', label: 'Vanuatu' },
  { value: 'Venezuela', label: 'Venezuela' },
  { value: 'Vietnam', label: 'Vietnam' },
  { value: 'Yemen', label: 'Yemen' },
  { value: 'Zambia', label: 'Zambia' },
  { value: 'Zimbabwe', label: 'Zimbabwe' },
];

const educationLevels = [
  { value: 'high_school', label: 'High School' },
  { value: 'bachelors', label: "Bachelor's Degree" },
  { value: 'masters', label: "Master's Degree" },
  { value: 'phd', label: 'PhD / Doctorate' },
];

const majorOptions = [
  { value: 'Computer Science', label: 'Computer Science' },
  { value: 'Engineering', label: 'Engineering' },
  { value: 'Electrical Engineering', label: 'Electrical Engineering' },
  { value: 'Mechanical Engineering', label: 'Mechanical Engineering' },
  { value: 'Civil Engineering', label: 'Civil Engineering' },
  { value: 'Chemical Engineering', label: 'Chemical Engineering' },
  { value: 'Medicine', label: 'Medicine' },
  { value: 'Nursing', label: 'Nursing' },
  { value: 'Pharmacy', label: 'Pharmacy' },
  { value: 'Dentistry', label: 'Dentistry' },
  { value: 'Business Administration', label: 'Business Administration' },
  { value: 'Accounting', label: 'Accounting' },
  { value: 'Finance', label: 'Finance' },
  { value: 'Marketing', label: 'Marketing' },
  { value: 'Economics', label: 'Economics' },
  { value: 'Political Science', label: 'Political Science' },
  { value: 'International Relations', label: 'International Relations' },
  { value: 'Law', label: 'Law' },
  { value: 'Psychology', label: 'Psychology' },
  { value: 'Sociology', label: 'Sociology' },
  { value: 'Anthropology', label: 'Anthropology' },
  { value: 'History', label: 'History' },
  { value: 'Philosophy', label: 'Philosophy' },
  { value: 'English Literature', label: 'English Literature' },
  { value: 'Linguistics', label: 'Linguistics' },
  { value: 'Mathematics', label: 'Mathematics' },
  { value: 'Physics', label: 'Physics' },
  { value: 'Chemistry', label: 'Chemistry' },
  { value: 'Biology', label: 'Biology' },
  { value: 'Environmental Science', label: 'Environmental Science' },
  { value: 'Geology', label: 'Geology' },
  { value: 'Architecture', label: 'Architecture' },
  { value: 'Art & Design', label: 'Art & Design' },
  { value: 'Music', label: 'Music' },
  { value: 'Film & Media', label: 'Film & Media' },
  { value: 'Education', label: 'Education' },
  { value: 'Agriculture', label: 'Agriculture' },
  { value: 'Food Science', label: 'Food Science' },
  { value: 'Hospitality & Tourism', label: 'Hospitality & Tourism' },
  { value: 'Sports Science', label: 'Sports Science' },
];

const targetDegrees = [
  { value: 'bachelors', label: "Bachelor's" },
  { value: 'masters', label: "Master's" },
  { value: 'phd', label: 'PhD' },
  { value: 'exchange', label: 'Exchange Program' },
  { value: 'summer', label: 'Summer School' },
];

const englishLevels = [
  { value: 'beginner', label: 'Beginner', description: 'Basic understanding', needsScore: false },
  { value: 'intermediate', label: 'Intermediate', description: 'Can read and write', needsScore: false },
  { value: 'advanced', label: 'Advanced', description: 'Good academic English', needsScore: false },
  { value: 'fluent', label: 'Fluent', description: 'Confident in all situations', needsScore: false },
  { value: 'native', label: 'Native', description: 'Native speaker', needsScore: false },
  { value: 'toefl', label: 'TOEFL', description: 'Test score available', needsScore: true, scoreMin: 0, scoreMax: 120 },
  { value: 'ielts', label: 'IELTS', description: 'Test score available', needsScore: true, scoreMin: 0, scoreMax: 9 },
];

const budgetOptions = [
  { value: 'none', label: 'No funding available' },
  { value: 'limited', label: 'Limited (partial coverage needed)' },
  { value: 'moderate', label: 'Moderate (can cover some costs)' },
  { value: 'full', label: 'Full (can self-fund)' },
];

const incomeOptions = [
  { value: 'low', label: 'Low Income' },
  { value: 'middle', label: 'Middle Income' },
  { value: 'high', label: 'High Income' },
  { value: 'prefer_not', label: 'Prefer Not to Say' },
];

const languageOptions = [
  { value: 'Arabic', label: 'Arabic' },
  { value: 'English', label: 'English' },
  { value: 'French', label: 'French' },
  { value: 'Turkish', label: 'Turkish' },
  { value: 'Persian', label: 'Persian' },
  { value: 'Kurdish', label: 'Kurdish' },
  { value: 'Hebrew', label: 'Hebrew' },
  { value: 'Urdu', label: 'Urdu' },
  { value: 'Bengali', label: 'Bengali' },
  { value: 'Swahili', label: 'Swahili' },
  { value: 'Amharic', label: 'Amharic' },
  { value: 'Somali', label: 'Somali' },
  { value: 'Berber', label: 'Berber' },
  { value: 'Spanish', label: 'Spanish' },
  { value: 'German', label: 'German' },
  { value: 'Italian', label: 'Italian' },
  { value: 'Russian', label: 'Russian' },
  { value: 'Chinese', label: 'Chinese' },
  { value: 'Hindi', label: 'Hindi' },
  { value: 'Portuguese', label: 'Portuguese' },
];

const interestOptions = [
  { value: 'fully_funded', label: 'Fully Funded' },
  { value: 'europe', label: 'Europe' },
  { value: 'asia', label: 'Asia' },
  { value: 'north_america', label: 'North America' },
  { value: 'engineering', label: 'Engineering' },
  { value: 'medicine', label: 'Medicine' },
  { value: 'business', label: 'Business' },
  { value: 'arts', label: 'Arts' },
  { value: 'technology', label: 'Technology' },
  { value: 'science', label: 'Science' },
  { value: 'humanities', label: 'Humanities' },
  { value: 'law', label: 'Law' },
  { value: 'masters', label: "Master's" },
  { value: 'phd', label: 'PhD' },
  { value: 'undergraduate', label: 'Undergraduate' },
];

const showScoreInput = computed(() => {
  const sel = englishLevels.find(l => l.value === wizardData.english_level);
  return sel?.needsScore || false;
});

const scoreMin = computed(() => englishLevels.find(l => l.value === wizardData.english_level)?.scoreMin || 0);
const scoreMax = computed(() => englishLevels.find(l => l.value === wizardData.english_level)?.scoreMax || 120);

function selectEnglishLevel(level) {
  wizardData.english_level = level.value;
  wizardData.english_score = '';
}

const reviewData = computed(() => [
  { question: 'Full Name', answer: wizardData.full_name },
  { question: 'Date of Birth', answer: wizardData.date_of_birth },
  { question: 'Country', answer: wizardData.country },
  { question: 'Bio', answer: wizardData.bio || '—' },
  { question: 'Education Level', answer: educationLevels.find(l => l.value === wizardData.academic_level)?.label || '—' },
  { question: 'Major', answer: wizardData.major || '—' },
  { question: 'Target Degree', answer: targetDegrees.find(d => d.value === wizardData.target_degree)?.label || '—' },
  { question: 'GPA', answer: wizardData.gpa || '—' },
  { question: 'English Proficiency', answer: wizardData.english_proficiency || '—' },
  { question: 'Work Experience', answer: wizardData.has_work_experience ? 'Yes' : 'No' },
  { question: 'Research Experience', answer: wizardData.has_research_experience ? 'Yes' : 'No' },
  { question: 'Income', answer: wizardData.demographics.income || '—' },
  { question: 'Ethnicity', answer: wizardData.demographics.ethnicity || '—' },
  { question: 'English Level', answer: wizardData.english_level ? (englishLevels.find(l => l.value === wizardData.english_level)?.label + (wizardData.english_score ? ` (Score: ${wizardData.english_score})` : '')) : '—' },
  { question: 'Budget', answer: budgetOptions.find(b => b.value === wizardData.budget)?.label || '—' },
  { question: 'Languages', answer: wizardData.languages?.join(', ') || '—' },
  { question: 'Interests', answer: wizardData.interests?.join(', ') || '—' },
]);

const wizardSteps = [
  {
    title: 'About You',
    validate: () => {
      const errors = {};
      if (!wizardData.full_name?.trim()) errors.full_name = 'Full name is required';
      if (!wizardData.date_of_birth) errors.date_of_birth = 'Date of birth is required';
      if (!wizardData.country) errors.country = 'Country is required';
      if (Object.keys(errors).length) throw errors;
      return true;
    },
  },
  {
    title: 'Education',
    validate: () => {
      const errors = {};
      if (!wizardData.academic_level) errors.academic_level = 'Education level is required';
      if (!wizardData.major?.trim()) errors.major = 'Field of study is required';
      if (Object.keys(errors).length) throw errors;
      return true;
    },
  },
  { title: 'Experience', validate: () => true },
  { title: 'Demographics', validate: () => true },
  {
    title: 'Preferences',
    validate: () => {
      const errors = {};
      if (!wizardData.english_level) errors.english_level = 'English level is required';
      if (!wizardData.budget) errors.budget = 'Budget is required';
      if (!wizardData.languages?.length) errors.languages = 'At least one language is required';
      if (!wizardData.interests?.length) errors.interests = 'At least one interest is required';
      if (Object.keys(errors).length) throw errors;
      return true;
    },
  },
  { title: 'Review', validate: () => true },
];

function nextStep() {
  try {
    const result = wizardSteps[currentStepIndex.value].validate();
    if (result === true || result === undefined) {
      Object.keys(stepErrors).forEach(k => delete stepErrors[k]);
      currentStepIndex.value++;
    }
  } catch (errors) {
    Object.assign(stepErrors, errors);
  }
}

function prevStep() {
  currentStepIndex.value--;
  Object.keys(stepErrors).forEach(k => delete stepErrors[k]);
}

function goToDashboard() {
  router.visit('/dashboard');
}

function submitProfile() {
  submitting.value = true;
  const demographics = {
    ...(existingProfile.value?.demographics || {}),
    target_degree: wizardData.target_degree,
    has_work_experience: wizardData.has_work_experience,
    has_research_experience: wizardData.has_research_experience,
    english_level: wizardData.english_level,
    english_score: wizardData.english_score,
    budget: wizardData.budget,
    income: wizardData.demographics.income,
    ethnicity: wizardData.demographics.ethnicity,
  };

  const payload = {
    full_name: wizardData.full_name,
    academic_level: wizardData.academic_level,
    major: wizardData.major,
    gpa: wizardData.gpa,
    date_of_birth: wizardData.date_of_birth,
    country: wizardData.country,
    english_proficiency: wizardData.english_proficiency,
    languages: JSON.stringify(wizardData.languages),
    interests: JSON.stringify(wizardData.interests),
    demographics: JSON.stringify(demographics),
    bio: wizardData.bio || '',
  };

  const form = useForm(payload);
  form.put(route('profile.update'), {
    preserveScroll: true,
    onSuccess: () => {
      router.visit('/dashboard');
    },
    onError: () => {
      submitting.value = false;
    },
    onFinish: () => {
      submitting.value = false;
    },
  });
}
</script>

<style scoped>
/* Theme‑aware glass utilities */
.glass-surface {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.dark .glass-surface {
  background: rgba(255, 255, 255, 0.03);
  border-color: rgba(255, 255, 255, 0.06);
}
.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.07);
  border-color: rgba(255, 255, 255, 0.12);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}
.glass-hero {
  background: rgba(255, 255, 255, 0.45);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(0, 0, 0, 0.12);
  box-shadow: 0 16px 64px rgba(0, 0, 0, 0.2);
}
.dark .glass-hero {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.18);
  box-shadow: 0 16px 64px rgba(0, 0, 0, 0.4);
}

.perspective-1000 { perspective: 1000px; }
.-rotate-y-2 { transform: rotateY(-2deg) rotateX(1deg); }
.-rotate-y-3 { transform: rotateY(-3deg) rotateX(1.5deg); }
.rotate-y-1 { transform: rotateY(1deg) rotateX(0.5deg); }

.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 999px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
}

/* Animations */
@keyframes float {
  0%, 100% { transform: translateY(0px) scale(1); opacity: 0.2; }
  50% { transform: translateY(-20px) scale(1.2); opacity: 0.6; }
}
.animate-float { animation: float 6s ease-in-out infinite; }
.animate-float.delay-700 { animation-delay: 700ms; }
.animate-float.delay-1400 { animation-delay: 1400ms; }
.animate-float.delay-2000 { animation-delay: 2000ms; }

@keyframes pulse-slow {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.1); }
}
.animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }

@media (max-width: 768px) {
  .perspective-1000 { perspective: none; }
  .-rotate-y-2, .-rotate-y-3, .rotate-y-1 { transform: none !important; }
}
</style>