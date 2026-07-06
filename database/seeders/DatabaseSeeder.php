<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Scholarship;
use App\Models\Faq;
use App\Models\Testimonial;
use App\Models\Notification;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user – create only if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@scholarshiphub.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password'),
                'role'     => 'admin',
                'locale'   => 'en',
            ]
        );

        // Sample student – also firstOrCreate
        $student = User::firstOrCreate(
            ['email' => 'alex@example.com'],
            [
                'name'     => 'Alex Wakrod',
                'password' => Hash::make('password'),
                'role'     => 'student',
                'locale'   => 'en',
            ]
        );

        // Student profile – only if not already present
        if (!$student->studentProfile) {
            $student->studentProfile()->create([
                'full_name'          => 'Alex Wakrod',
                'academic_level'     => 'undergraduate',
                'major'              => 'Computer Science',
                'gpa'                => 3.7,
                'date_of_birth'      => '2002-05-15',
                'country'            => 'Egypt',
                'english_proficiency'=> 'IELTS 7.0',
                'languages'          => json_encode(['Arabic', 'English']),
                'interests'          => json_encode(['fully_funded', 'europe', 'engineering']),
                'profile_completed'  => true,
            ]);
        }

        // Sample scholarships – insert only if table is empty
        if (Scholarship::count() === 0) {
            Scholarship::insert([
                [
                    'title'               => 'DAAD Masters Scholarship',
                    'description'         => 'Fully-funded scholarship for international students to pursue a Masters degree in Germany in all fields.',
                    'provider'            => 'DAAD',
                    'country'             => 'Germany',
                    'amount'              => 1200.00,
                    'deadline'            => now()->addDays(15),
                    'status'              => 'active',
                    'parsed_requirements' => json_encode([
                        'academic_level'      => 'undergraduate',
                        'minimum_gpa'         => 3.0,
                        'majors'              => ['Computer Science', 'Engineering', 'Mathematics'],
                        'countries'           => ['Egypt', 'Jordan', 'Morocco'],
                        'english_proficiency' => 'IELTS 6.5',
                        'other_requirements'  => ['Must have bachelor degree', 'Two years of work experience preferred'],
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title'               => 'Erasmus Mundus Joint Master Degree',
                    'description'         => 'A fully-funded scholarship program for students worldwide to study at multiple European universities.',
                    'provider'            => 'European Commission',
                    'country'             => 'Multiple',
                    'amount'              => 1400.00,
                    'deadline'            => now()->addDays(30),
                    'status'              => 'active',
                    'parsed_requirements' => json_encode([
                        'academic_level'      => 'undergraduate',
                        'minimum_gpa'         => 3.5,
                        'majors'              => ['Any'],
                        'countries'           => [],
                        'english_proficiency' => 'IELTS 7.0',
                        'other_requirements'  => [],
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title'               => 'Chevening Scholarship',
                    'description'         => 'UK government global scholarship programme for future leaders to study a one-year Masters degree.',
                    'provider'            => 'Chevening',
                    'country'             => 'United Kingdom',
                    'amount'              => 18000.00,
                    'deadline'            => now()->addDays(60),
                    'status'              => 'active',
                    'parsed_requirements' => json_encode([
                        'academic_level'      => 'undergraduate',
                        'minimum_gpa'         => 3.2,
                        'majors'              => ['Any'],
                        'countries'           => ['Egypt', 'Pakistan', 'Nigeria'],
                        'english_proficiency' => 'IELTS 6.5',
                        'other_requirements'  => ['Two years work experience', 'Leadership potential'],
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // Sample FAQs – insert only if empty
        if (Faq::count() === 0) {
            Faq::insert([
                [
                    'question'     => 'Is ScholarshipHub free?',
                    'answer'       => 'Yes, ScholarshipHub is completely free for students. No credit card required.',
                    'order'        => 1,
                    'is_published' => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ],
                [
                    'question'     => 'How does AI document review work?',
                    'answer'       => 'Upload your CV or personal statement, and our AI will analyze it for grammar, structure, ATS compatibility, and competitiveness. You\'ll receive a detailed report with suggestions.',
                    'order'        => 2,
                    'is_published' => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ],
                [
                    'question'     => 'Can I trust the match scores?',
                    'answer'       => 'Our match algorithm uses your profile data and scholarship requirements to calculate a percentage match. While highly accurate, always verify eligibility yourself before applying.',
                    'order'        => 3,
                    'is_published' => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ],
            ]);
        }

        // Sample approved testimonial (only if not exists)
        if (!Testimonial::where('user_id', $student->id)->exists()) {
            Testimonial::create([
                'user_id'      => $student->id,
                'quote'        => 'ScholarshipHub helped me find and apply to scholarships I never knew existed. The AI review feature is a game-changer!',
                'name_display' => 'Alex W.',
                'grade'        => 'Grade 11',
                'is_approved'  => true,
            ]);
        }

        // Sample notification for admin (only if none)
        if (Notification::count() === 0) {
            Notification::create([
                'user_id' => $admin->id,
                'type'    => 'system',
                'data'    => json_encode(['message' => 'Welcome to ScholarshipHub Admin Panel.']),
            ]);
        }
    }
}