<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Discussion;
use App\Models\DiscussionHelpfulVote;
use App\Models\DiscussionNotificationPreference;
use App\Models\DiscussionReply;
use App\Models\StudyGroup;
use App\Models\StudyResource;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $student = User::updateOrCreate([
            'email' => 'student@studyhub.test',
        ], [
            'name' => 'Alex Student',
            'display_name' => 'Alex Student',
            'role' => 'student',
            'bio' => 'Focused on collaborative learning, cleaner study routines, and building momentum every week.',
            'theme' => 'forest',
            'surface_style' => 'soft',
            'interface_density' => 'comfortable',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $admin = User::updateOrCreate([
            'email' => 'admin@studyhub.test',
        ], [
            'name' => 'Casey Admin',
            'display_name' => 'Casey Admin',
            'role' => 'admin',
            'bio' => 'StudyHub administrator for user access, moderation, and reporting.',
            'theme' => 'forest',
            'surface_style' => 'soft',
            'interface_density' => 'comfortable',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $sampleUsers = collect([
            ['name' => 'Sarah Chen', 'email' => 'sarah.chen@studyhub.test'],
            ['name' => 'Mike Johnson', 'email' => 'mike.johnson@studyhub.test'],
            ['name' => 'Emma Davis', 'email' => 'emma.davis@studyhub.test'],
            ['name' => 'John Smith', 'email' => 'john.smith@studyhub.test'],
            ['name' => 'Lisa Park', 'email' => 'lisa.park@studyhub.test'],
            ['name' => 'David Kim', 'email' => 'david.kim@studyhub.test'],
            ['name' => 'Rachel Green', 'email' => 'rachel.green@studyhub.test'],
            ['name' => 'Alex Wong', 'email' => 'alex.wong@studyhub.test'],
            ['name' => 'Mia Cruz', 'email' => 'mia.cruz@studyhub.test'],
            ['name' => 'Noah Tan', 'email' => 'noah.tan@studyhub.test'],
        ])->mapWithKeys(function (array $data) {
            $user = User::updateOrCreate([
                'email' => $data['email'],
            ], [
                'name' => $data['name'],
                'display_name' => $data['name'],
                'role' => 'student',
                'bio' => 'Active StudyHub member.',
                'theme' => 'forest',
                'surface_style' => 'soft',
                'interface_density' => 'comfortable',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]);

            return [$data['name'] => $user];
        });

        $groups = collect([
            [
                'name' => 'Physics Review Circle',
                'description' => 'Finals prep, formula drills, and peer explanations',
                'category' => 'Science',
                'meeting_style' => 'hybrid',
                'visibility' => 'public',
                'join_code' => null,
                'color' => '#4A955F',
                'owner' => 'Alex Student',
                'members' => ['Alex Student', 'Sarah Chen', 'Mike Johnson', 'Emma Davis'],
            ],
            [
                'name' => 'Capstone Study Group',
                'description' => 'Private project planning, defense prep, and shared deliverables',
                'category' => 'Computer Science',
                'meeting_style' => 'online',
                'visibility' => 'private',
                'join_code' => 'CAPSTONE',
                'color' => '#FF6B35',
                'owner' => 'Alex Student',
                'members' => ['Alex Student', 'Mia Cruz', 'Noah Tan'],
            ],
            [
                'name' => 'Computer Science 301',
                'description' => 'Advanced algorithms and data structures',
                'category' => 'Computer Science',
                'meeting_style' => 'in-person',
                'visibility' => 'public',
                'join_code' => null,
                'color' => '#0F4C75',
                'owner' => 'Sarah Chen',
                'members' => ['Sarah Chen', 'Mike Johnson', 'Emma Davis', 'John Smith', 'Alex Student'],
            ],
            [
                'name' => 'Data Structures Study',
                'description' => 'Weekly problem-solving sessions',
                'category' => 'Computer Science',
                'meeting_style' => 'hybrid',
                'visibility' => 'public',
                'join_code' => null,
                'color' => '#3282B8',
                'owner' => 'Mike Johnson',
                'members' => ['Sarah Chen', 'Mike Johnson', 'Rachel Green', 'Alex Student'],
            ],
            [
                'name' => 'Calculus II Prep',
                'description' => 'Exam preparation and practice problems',
                'category' => 'Mathematics',
                'meeting_style' => 'in-person',
                'visibility' => 'public',
                'join_code' => null,
                'color' => '#06D6A0',
                'owner' => 'Emma Davis',
                'members' => ['Emma Davis', 'John Smith', 'Lisa Park', 'Alex Student'],
            ],
            [
                'name' => 'Web Development',
                'description' => 'Full-stack development projects',
                'category' => 'Programming',
                'meeting_style' => 'hybrid',
                'visibility' => 'private',
                'join_code' => 'WEBDEV',
                'color' => '#FF6B35',
                'owner' => 'Alex Wong',
                'members' => ['Alex Wong', 'Emma Davis', 'David Kim', 'Rachel Green'],
            ],
            [
                'name' => 'Database Systems',
                'description' => 'SQL and NoSQL database design',
                'category' => 'Information Systems',
                'meeting_style' => 'online',
                'visibility' => 'public',
                'join_code' => null,
                'color' => '#FFD166',
                'owner' => 'Lisa Park',
                'members' => ['Lisa Park', 'Alex Wong', 'Noah Tan'],
            ],
            [
                'name' => 'Machine Learning Basics',
                'description' => 'Introduction to ML algorithms',
                'category' => 'Artificial Intelligence',
                'meeting_style' => 'online',
                'visibility' => 'private',
                'join_code' => 'MLBASICS',
                'color' => '#973e5d',
                'owner' => 'Rachel Green',
                'members' => ['Rachel Green', 'Mia Cruz', 'David Kim'],
            ],
            [
                'name' => 'Language Exchange Circle',
                'description' => 'Conversation practice, vocabulary drills, and peer feedback',
                'category' => 'Language',
                'meeting_style' => 'online',
                'visibility' => 'public',
                'join_code' => null,
                'color' => '#7C3AED',
                'owner' => 'Mia Cruz',
                'members' => ['Mia Cruz', 'Noah Tan', 'Emma Davis', 'Alex Student'],
            ],
            [
                'name' => 'Business Analytics Lab',
                'description' => 'Dashboard practice, case studies, and spreadsheet modeling',
                'category' => 'Business',
                'meeting_style' => 'hybrid',
                'visibility' => 'public',
                'join_code' => null,
                'color' => '#0EA5E9',
                'owner' => 'David Kim',
                'members' => ['David Kim', 'Lisa Park', 'Rachel Green', 'Alex Student'],
            ],
            [
                'name' => 'UI UX Design Studio',
                'description' => 'Wireframes, prototypes, usability notes, and portfolio reviews',
                'category' => 'Design',
                'meeting_style' => 'in-person',
                'visibility' => 'public',
                'join_code' => null,
                'color' => '#F97316',
                'owner' => 'Alex Wong',
                'members' => ['Alex Wong', 'Mia Cruz', 'Sarah Chen', 'Alex Student'],
            ],
            [
                'name' => 'Cybersecurity Review Team',
                'description' => 'Security basics, threat modeling, and lab walkthroughs',
                'category' => 'Programming',
                'meeting_style' => 'online',
                'visibility' => 'private',
                'join_code' => 'SECURE',
                'color' => '#DC2626',
                'owner' => 'Noah Tan',
                'members' => ['Noah Tan', 'David Kim', 'Sarah Chen'],
            ],
        ])->mapWithKeys(function (array $data) use ($sampleUsers, $student) {
            $owner = $data['owner'] === 'Alex Student' ? $student : $sampleUsers[$data['owner']];

            $group = StudyGroup::updateOrCreate([
                'name' => $data['name'],
            ], [
                'owner_id' => $owner->id,
                'description' => $data['description'],
                'category' => $data['category'],
                'meeting_style' => $data['meeting_style'],
                'visibility' => $data['visibility'],
                'join_code' => $data['join_code'],
                'color' => $data['color'],
            ]);

            $memberIds = collect($data['members'])
                ->map(fn (string $name) => $name === 'Alex Student' ? $student->id : $sampleUsers[$name]->id)
                ->all();

            $group->members()->sync($memberIds);

            return [$data['name'] => $group];
        });

        ActivityLog::query()->delete();
        DiscussionHelpfulVote::query()->delete();
        DiscussionNotificationPreference::query()->delete();
        DiscussionReply::query()->delete();
        Discussion::query()->delete();
        StudyResource::query()->delete();
        StudySession::query()->delete();

        collect([
            ['group' => 'Physics Review Circle', 'uploader' => 'Sarah Chen', 'name' => 'Finals Formula Map.pdf', 'category' => 'Study Guide', 'download_count' => 142, 'rating_average' => 4.8, 'rating_count' => 31, 'size_bytes' => 1269760, 'uploaded_at' => now()->subHours(8)],
            ['group' => 'Capstone Study Group', 'uploader' => 'Mia Cruz', 'name' => 'Defense Checklist.md', 'category' => 'Study Guide', 'download_count' => 53, 'rating_average' => 4.6, 'rating_count' => 14, 'size_bytes' => 24576, 'uploaded_at' => now()->subHours(6)],
            ['group' => 'Computer Science 301', 'uploader' => 'Sarah Chen', 'name' => 'Algorithm Analysis - Chapter 5.pdf', 'category' => 'Lecture Notes', 'download_count' => 188, 'rating_average' => 4.9, 'rating_count' => 47, 'size_bytes' => 3355443, 'uploaded_at' => now()->subDays(6)],
            ['group' => 'Data Structures Study', 'uploader' => 'Mike Johnson', 'name' => 'Data Structures Cheat Sheet.pdf', 'category' => 'Study Guide', 'download_count' => 231, 'rating_average' => 4.7, 'rating_count' => 39, 'size_bytes' => 1572864, 'uploaded_at' => now()->subDays(5)],
            ['group' => 'Calculus II Prep', 'uploader' => 'Emma Davis', 'name' => 'Calculus Practice Problems.docx', 'category' => 'Assignments', 'download_count' => 117, 'rating_average' => 4.5, 'rating_count' => 22, 'size_bytes' => 911360, 'uploaded_at' => now()->subDays(4)],
            ['group' => 'Web Development', 'uploader' => 'Alex Wong', 'name' => 'Web Dev Project Template.zip', 'category' => 'Code', 'download_count' => 96, 'rating_average' => 4.3, 'rating_count' => 18, 'size_bytes' => 2202009, 'uploaded_at' => now()->subDays(3)],
            ['group' => 'Database Systems', 'uploader' => 'Lisa Park', 'name' => 'Database Schema Design.pdf', 'category' => 'Lecture Notes', 'download_count' => 84, 'rating_average' => 4.4, 'rating_count' => 16, 'size_bytes' => 1887436, 'uploaded_at' => now()->subDays(2)],
            ['group' => 'Machine Learning Basics', 'uploader' => 'Rachel Green', 'name' => 'ML Algorithm Implementations.py', 'category' => 'Code', 'download_count' => 76, 'rating_average' => 4.2, 'rating_count' => 12, 'size_bytes' => 46080, 'uploaded_at' => now()->subDay()],
            ['group' => 'Language Exchange Circle', 'uploader' => 'Mia Cruz', 'name' => 'Spanish Conversation Prompts.pdf', 'category' => 'Study Guide', 'download_count' => 64, 'rating_average' => 4.5, 'rating_count' => 11, 'size_bytes' => 704512, 'uploaded_at' => now()->subHours(20)],
            ['group' => 'Language Exchange Circle', 'uploader' => 'Noah Tan', 'name' => 'Vocabulary Builder Week 3.xlsx', 'category' => 'Assignments', 'download_count' => 38, 'rating_average' => 4.1, 'rating_count' => 7, 'size_bytes' => 120832, 'uploaded_at' => now()->subHours(14)],
            ['group' => 'Business Analytics Lab', 'uploader' => 'David Kim', 'name' => 'Sales Dashboard Case Study.xlsx', 'category' => 'Datasets', 'download_count' => 91, 'rating_average' => 4.6, 'rating_count' => 19, 'size_bytes' => 832512, 'uploaded_at' => now()->subHours(18)],
            ['group' => 'Business Analytics Lab', 'uploader' => 'Lisa Park', 'name' => 'Pivot Table Practice Pack.pdf', 'category' => 'Study Guide', 'download_count' => 72, 'rating_average' => 4.4, 'rating_count' => 13, 'size_bytes' => 1048576, 'uploaded_at' => now()->subHours(11)],
            ['group' => 'UI UX Design Studio', 'uploader' => 'Alex Wong', 'name' => 'Mobile App Wireframe Kit.fig', 'category' => 'Design Files', 'download_count' => 58, 'rating_average' => 4.8, 'rating_count' => 10, 'size_bytes' => 5242880, 'uploaded_at' => now()->subHours(16)],
            ['group' => 'UI UX Design Studio', 'uploader' => 'Mia Cruz', 'name' => 'Usability Testing Notes.md', 'category' => 'Lecture Notes', 'download_count' => 44, 'rating_average' => 4.3, 'rating_count' => 8, 'size_bytes' => 32768, 'uploaded_at' => now()->subHours(9)],
            ['group' => 'Cybersecurity Review Team', 'uploader' => 'Noah Tan', 'name' => 'Network Security Lab Guide.pdf', 'category' => 'Lab Manual', 'download_count' => 67, 'rating_average' => 4.7, 'rating_count' => 15, 'size_bytes' => 2097152, 'uploaded_at' => now()->subHours(12)],
            ['group' => 'Cybersecurity Review Team', 'uploader' => 'David Kim', 'name' => 'Threat Modeling Checklist.md', 'category' => 'Study Guide', 'download_count' => 49, 'rating_average' => 4.5, 'rating_count' => 9, 'size_bytes' => 40960, 'uploaded_at' => now()->subHours(7)],
        ])->each(function (array $resource) use ($groups, $sampleUsers) {
            $extension = strtolower(pathinfo($resource['name'], PATHINFO_EXTENSION) ?: 'file');
            $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '-', $resource['name']);
            $path = 'studyhub/resources/seeded/'.strtolower($safeName);

            Storage::disk('local')->put($path, "StudyHub sample download placeholder.\n");

            StudyResource::create([
                'group_id' => $groups[$resource['group']]->id,
                'uploaded_by' => $sampleUsers[$resource['uploader']]->id,
                'name' => $resource['name'],
                'category' => $resource['category'],
                'path' => $path,
                'file_type' => $extension,
                'download_count' => $resource['download_count'] ?? 0,
                'rating_average' => $resource['rating_average'] ?? 0,
                'rating_count' => $resource['rating_count'] ?? 0,
                'size_bytes' => Storage::disk('local')->size($path),
                'uploaded_at' => $resource['uploaded_at'],
            ]);
        });

        $seedDiscussionImage = function (string $name, string $color): array {
            $path = 'studyhub-discussion-images/seeded/'.$name.'.svg';

            Storage::disk('local')->put($path, '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="760" viewBox="0 0 1200 760"><rect width="1200" height="760" rx="42" fill="'.$color.'"/><circle cx="210" cy="180" r="92" fill="#ffffff" opacity=".24"/><path d="M120 610 390 360l180 150 150-120 360 220H120Z" fill="#ffffff" opacity=".42"/><path d="M164 116h872" stroke="#ffffff" stroke-width="32" stroke-linecap="round" opacity=".5"/><path d="M164 194h520" stroke="#ffffff" stroke-width="26" stroke-linecap="round" opacity=".35"/></svg>');

            return [
                'path' => $path,
                'name' => $name.'.svg',
                'mime_type' => 'image/svg+xml',
            ];
        };

        $discussions = collect([
            [
                'title' => 'Finals prep: which topics should we prioritize?',
                'body' => 'Let us build one clear review plan for formulas, sample problems, and short explanations before the exam.',
                'group' => 'Physics Review Circle',
                'author' => 'Alex Student',
                'views' => 228,
                'trending' => true,
                'images' => [
                    $seedDiscussionImage('physics-formula-board', '#4f46e5'),
                    $seedDiscussionImage('physics-review-plan', '#0891b2'),
                ],
                'last_active_at' => now()->subMinutes(2),
                'replies' => [
                    [
                        'author' => 'Sarah Chen',
                        'body' => 'I think projectile motion and energy conservation should go first because they appear in most practice sets.',
                        'created_at' => now()->subMinutes(18),
                        'children' => [
                            [
                                'author' => 'Alex Student',
                                'body' => 'Good point. I will add those to the first part of the review checklist.',
                                'created_at' => now()->subMinutes(14),
                            ],
                            [
                                'author' => 'Mike Johnson',
                                'body' => 'I can explain the energy problems during the session and upload two sample solutions.',
                                'created_at' => now()->subMinutes(2),
                            ],
                        ],
                    ],
                    [
                        'author' => 'Emma Davis',
                        'body' => 'Let us reserve the last 20 minutes for quick questions so everyone leaves with a complete plan.',
                        'created_at' => now()->subMinutes(9),
                    ],
                ],
            ],
            [
                'title' => 'Defense checklist and task owners',
                'body' => 'Private capstone thread for assigning presentation slides, demo flow, and final revisions.',
                'group' => 'Capstone Study Group',
                'author' => 'Mia Cruz',
                'views' => 74,
                'trending' => false,
                'images' => [
                    $seedDiscussionImage('capstone-demo-flow', '#f97316'),
                ],
                'last_active_at' => now()->subMinutes(25),
                'replies' => [
                    ['author' => 'Alex Student', 'body' => 'I will handle the system demo and make sure the discussion hub flow is ready.', 'created_at' => now()->subMinutes(32)],
                    ['author' => 'Noah Tan', 'body' => 'I will prepare the poster section for technologies and target users.', 'created_at' => now()->subMinutes(25)],
                ],
            ],
            [
                'title' => 'Help with Dynamic Programming Problems',
                'body' => 'Need help understanding memoization and bottom-up approaches.',
                'group' => 'Computer Science 301',
                'author' => 'John Smith',
                'views' => 156,
                'trending' => true,
                'last_active_at' => now()->subMinutes(5),
                'replies' => [
                    ['author' => 'Sarah Chen', 'body' => 'Try starting with memoization first, then compare it with bottom-up so the pattern is easier to see.', 'created_at' => now()->subMinutes(12)],
                    ['author' => 'Mike Johnson', 'body' => 'I uploaded a small example in our resources that shows the difference pretty clearly.', 'created_at' => now()->subMinutes(8)],
                ],
            ],
            [
                'title' => 'Study Group Meetup - Friday 3PM',
                'body' => 'Planning a meetup this Friday. Who is available?',
                'group' => 'Data Structures Study',
                'author' => 'Sarah Chen',
                'views' => 89,
                'trending' => false,
                'last_active_at' => now()->subHour(),
                'replies' => [
                    ['author' => 'Emma Davis', 'body' => 'Friday works for me. I can bring the reviewer and some practice questions.', 'created_at' => now()->subMinutes(35)],
                ],
            ],
            [
                'title' => 'Exam Preparation Tips and Resources',
                'body' => 'Sharing review tips and practice sets before the exam.',
                'group' => 'Calculus II Prep',
                'author' => 'Mike Johnson',
                'views' => 312,
                'trending' => true,
                'images' => [
                    $seedDiscussionImage('calculus-series-notes', '#16a34a'),
                ],
                'last_active_at' => now()->subHours(2),
                'replies' => [
                    ['author' => 'Lisa Park', 'body' => 'Past papers helped me the most. Focus on time-limited practice.', 'created_at' => now()->subHour()],
                ],
            ],
            [
                'title' => 'Can someone check this ERD relationship?',
                'body' => 'I am unsure if the enrollment table should hold status and joined date, or if those belong on a separate history table.',
                'group' => 'Database Systems',
                'author' => 'Lisa Park',
                'views' => 134,
                'trending' => true,
                'images' => [
                    $seedDiscussionImage('database-erd-sketch', '#0ea5e9'),
                ],
                'last_active_at' => now()->subMinutes(16),
                'replies' => [
                    ['author' => 'Noah Tan', 'body' => 'Status fits on the pivot table if you only need the current membership state.', 'created_at' => now()->subMinutes(22)],
                    ['author' => 'Alex Wong', 'body' => 'Use a history table if you need audit-friendly changes over time.', 'created_at' => now()->subMinutes(16)],
                ],
            ],
            [
                'title' => 'Best way to explain train/test split?',
                'body' => 'I need a simple explanation for our ML presentation that avoids too much math but still sounds correct.',
                'group' => 'Machine Learning Basics',
                'author' => 'Rachel Green',
                'views' => 97,
                'trending' => false,
                'last_active_at' => now()->subHours(3),
                'replies' => [
                    ['author' => 'Mia Cruz', 'body' => 'You can describe it as practicing on one pile of examples and checking understanding on another unseen pile.', 'created_at' => now()->subHours(3)],
                ],
            ],
            [
                'title' => 'Prototype spacing feedback before critique',
                'body' => 'The mobile screen feels crowded around the primary action. Which spacing option reads cleaner?',
                'group' => 'UI UX Design Studio',
                'author' => 'Alex Wong',
                'views' => 183,
                'trending' => true,
                'images' => [
                    $seedDiscussionImage('ux-spacing-option-a', '#8b5cf6'),
                    $seedDiscussionImage('ux-spacing-option-b', '#ec4899'),
                ],
                'last_active_at' => now()->subMinutes(48),
                'replies' => [
                    ['author' => 'Mia Cruz', 'body' => 'Option B gives the button more breathing room without making the card feel empty.', 'created_at' => now()->subMinutes(52)],
                    ['author' => 'Sarah Chen', 'body' => 'I agree. The hierarchy is clearer when the label has more vertical spacing.', 'created_at' => now()->subMinutes(48)],
                ],
            ],
            [
                'title' => 'Quick vocabulary rotation for Thursday',
                'body' => 'Can each person bring five verbs and one short dialogue prompt? We can rotate partners every ten minutes.',
                'group' => 'Language Exchange Circle',
                'author' => 'Mia Cruz',
                'views' => 66,
                'trending' => false,
                'last_active_at' => now()->subHours(5),
                'replies' => [
                    ['author' => 'Noah Tan', 'body' => 'I can prepare travel and restaurant prompts.', 'created_at' => now()->subHours(5)],
                ],
            ],
            [
                'title' => 'Dashboard chart choice: bar or line?',
                'body' => 'For weekly sales grouped by category, would a grouped bar chart be easier to read than a line chart?',
                'group' => 'Business Analytics Lab',
                'author' => 'David Kim',
                'views' => 118,
                'trending' => true,
                'images' => [
                    $seedDiscussionImage('analytics-chart-comparison', '#0284c7'),
                ],
                'last_active_at' => now()->subHours(7),
                'replies' => [
                    ['author' => 'Lisa Park', 'body' => 'Grouped bars are better if category comparison matters more than trend shape.', 'created_at' => now()->subHours(7)],
                    ['author' => 'Rachel Green', 'body' => 'Line chart works if the story is movement over time, but it may get noisy with many categories.', 'created_at' => now()->subHours(6)],
                ],
            ],
            [
                'title' => 'Threat modeling checklist order',
                'body' => 'Should we identify assets before entry points, or start from possible attackers and work backward?',
                'group' => 'Cybersecurity Review Team',
                'author' => 'Noah Tan',
                'views' => 141,
                'trending' => false,
                'last_active_at' => now()->subHours(8),
                'replies' => [
                    ['author' => 'David Kim', 'body' => 'I would start with assets so the rest of the model stays grounded.', 'created_at' => now()->subHours(8)],
                ],
            ],
            [
                'title' => 'React form validation pattern',
                'body' => 'I am deciding between validating on blur or on submit for the project form. Which feels better for users?',
                'group' => 'Web Development',
                'author' => 'Emma Davis',
                'views' => 105,
                'trending' => false,
                'last_active_at' => now()->subHours(10),
                'replies' => [
                    ['author' => 'David Kim', 'body' => 'Validate on blur for specific fields, then show a final summary on submit.', 'created_at' => now()->subHours(10)],
                ],
            ],
            [
                'title' => 'Heap practice question set',
                'body' => 'I collected five heap operations that usually trip me up. Can we solve these before tree rotations?',
                'group' => 'Data Structures Study',
                'author' => 'Mike Johnson',
                'views' => 172,
                'trending' => true,
                'images' => [
                    $seedDiscussionImage('heap-practice-notes', '#0f766e'),
                ],
                'last_active_at' => now()->subHours(12),
                'replies' => [
                    ['author' => 'Rachel Green', 'body' => 'Yes. The delete-min trace is the one I want to walk through slowly.', 'created_at' => now()->subHours(12)],
                    ['author' => 'Alex Student', 'body' => 'I can bring a whiteboard version so everyone can follow each swap.', 'created_at' => now()->subHours(11)],
                ],
            ],
        ])->map(function (array $discussion) use ($groups, $sampleUsers, $student) {
            $author = $discussion['author'] === 'Alex Student' ? $student : $sampleUsers[$discussion['author']];
            $images = $discussion['images'] ?? [];
            $firstImage = $images[0] ?? null;

            $record = Discussion::create([
                'group_id' => $groups[$discussion['group']]->id,
                'author_id' => $author->id,
                'title' => $discussion['title'],
                'body' => $discussion['body'],
                'image_path' => $firstImage['path'] ?? null,
                'image_original_name' => $firstImage['name'] ?? null,
                'image_mime_type' => $firstImage['mime_type'] ?? null,
                'images' => $images ?: null,
                'views' => $discussion['views'],
                'trending' => $discussion['trending'],
                'last_active_at' => $discussion['last_active_at'],
            ]);

            collect($discussion['replies'])->each(function (array $reply) use ($record, $sampleUsers, $student) {
                $replyAuthor = $reply['author'] === 'Alex Student' ? $student : $sampleUsers[$reply['author']];

                $created = DiscussionReply::create([
                    'discussion_id' => $record->id,
                    'author_id' => $replyAuthor->id,
                    'body' => $reply['body'],
                    'created_at' => $reply['created_at'],
                    'updated_at' => $reply['created_at'],
                ]);

                collect($reply['children'] ?? [])->each(function (array $childReply) use ($record, $created, $sampleUsers, $student) {
                    $childAuthor = $childReply['author'] === 'Alex Student' ? $student : $sampleUsers[$childReply['author']];

                    DiscussionReply::create([
                        'discussion_id' => $record->id,
                        'parent_reply_id' => $created->id,
                        'author_id' => $childAuthor->id,
                        'body' => $childReply['body'],
                        'created_at' => $childReply['created_at'],
                        'updated_at' => $childReply['created_at'],
                    ]);
                });
            });

            return $record;
        });

        $helpfulVoters = collect([
            'Sarah Chen',
            'Mike Johnson',
            'Emma Davis',
            'John Smith',
            'Lisa Park',
            'David Kim',
            'Rachel Green',
            'Alex Wong',
            'Mia Cruz',
            'Noah Tan',
            'Alex Student',
        ]);

        $discussions->values()->each(function (Discussion $discussion, int $index) use ($helpfulVoters, $sampleUsers, $student) {
            $voteCount = min($helpfulVoters->count(), 2 + (($index * 3) % 7));

            $helpfulVoters
                ->skip($index % $helpfulVoters->count())
                ->concat($helpfulVoters->take($index % $helpfulVoters->count()))
                ->take($voteCount)
                ->each(function (string $name) use ($discussion, $sampleUsers, $student) {
                    $user = $name === 'Alex Student' ? $student : $sampleUsers[$name];

                    DiscussionHelpfulVote::query()->updateOrCreate([
                        'discussion_id' => $discussion->id,
                        'user_id' => $user->id,
                    ]);
                });
        });

        if ($presentationDiscussion = Discussion::query()->where('title', 'Finals prep: which topics should we prioritize?')->first()) {
            DiscussionNotificationPreference::updateOrCreate([
                'user_id' => $student->id,
                'discussion_id' => $presentationDiscussion->id,
            ], [
                'notify_replies' => true,
                'last_read_at' => now()->subMinutes(10),
            ])->forceFill([
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(10),
            ])->save();
        }

        collect([
            [
                'title' => 'Finals Review Sprint',
                'group' => 'Physics Review Circle',
                'creator' => 'Sarah Chen',
                'session_date' => now()->addDay()->toDateString(),
                'start_time' => '16:00:00',
                'end_time' => '17:30:00',
                'location' => 'Library Room 301',
                'type' => 'in-person',
                'max_attendees' => 15,
                'status' => 'confirmed',
                'notes' => 'Use the watched discussion thread to collect last-minute questions.',
                'attendees' => ['Alex Student', 'Sarah Chen', 'Mike Johnson', 'Emma Davis'],
            ],
            [
                'title' => 'Capstone Demo Dry Run',
                'group' => 'Capstone Study Group',
                'creator' => 'Alex Student',
                'session_date' => now()->addDays(2)->toDateString(),
                'start_time' => '19:00:00',
                'end_time' => '20:00:00',
                'location' => 'https://meet.google.com/studyhub-demo',
                'type' => 'online',
                'max_attendees' => 8,
                'status' => 'confirmed',
                'notes' => 'Run the 3-minute tutorial and tighten the panel explanation.',
                'attendees' => ['Alex Student', 'Mia Cruz', 'Noah Tan'],
            ],
            [
                'title' => 'Algorithms Review Session',
                'group' => 'Computer Science 301',
                'creator' => 'Sarah Chen',
                'session_date' => now()->addDay()->toDateString(),
                'start_time' => '15:00:00',
                'end_time' => '17:00:00',
                'location' => 'Library Room 204',
                'type' => 'in-person',
                'max_attendees' => 12,
                'status' => 'confirmed',
                'notes' => 'Bring your reviewer and focus on graph and DP problems.',
                'attendees' => ['Sarah Chen', 'Mike Johnson', 'Emma Davis', 'John Smith', 'Alex Student'],
            ],
            [
                'title' => 'Midterm Preparation',
                'group' => 'Calculus II Prep',
                'creator' => 'Mike Johnson',
                'session_date' => now()->addDays(3)->toDateString(),
                'start_time' => '17:00:00',
                'end_time' => '19:00:00',
                'location' => 'Online (Zoom)',
                'type' => 'online',
                'max_attendees' => 20,
                'status' => 'confirmed',
                'notes' => 'Zoom link will be shared in the group chat 15 minutes before start.',
                'attendees' => ['Mike Johnson', 'Emma Davis', 'Lisa Park', 'Sarah Chen', 'Alex Student'],
            ],
            [
                'title' => 'Database Design Workshop',
                'group' => 'Database Systems',
                'creator' => 'Alex Wong',
                'session_date' => now()->subDays(2)->toDateString(),
                'start_time' => '15:00:00',
                'end_time' => '17:00:00',
                'location' => 'Online (Meet)',
                'type' => 'online',
                'max_attendees' => 12,
                'status' => 'completed',
                'notes' => 'Reviewed normalization, schema patterns, and sample ER diagrams.',
                'attendees' => ['Alex Wong', 'Lisa Park', 'Sarah Chen', 'Emma Davis', 'Alex Student'],
            ],
            [
                'title' => 'Language Practice Hour',
                'group' => 'Language Exchange Circle',
                'creator' => 'Mia Cruz',
                'session_date' => now()->addDays(4)->toDateString(),
                'start_time' => '18:00:00',
                'end_time' => '19:00:00',
                'location' => 'Online (Meet)',
                'type' => 'online',
                'max_attendees' => 10,
                'status' => 'confirmed',
                'notes' => 'Use the prompt sheet and pair up for 10-minute rotations.',
                'attendees' => ['Mia Cruz', 'Noah Tan', 'Emma Davis', 'Alex Student'],
            ],
            [
                'title' => 'Analytics Dashboard Critique',
                'group' => 'Business Analytics Lab',
                'creator' => 'David Kim',
                'session_date' => now()->addDays(5)->toDateString(),
                'start_time' => '14:00:00',
                'end_time' => '15:30:00',
                'location' => 'Business Lab 2',
                'type' => 'in-person',
                'max_attendees' => 14,
                'status' => 'confirmed',
                'notes' => 'Bring one chart you want feedback on.',
                'attendees' => ['David Kim', 'Lisa Park', 'Rachel Green', 'Alex Student'],
            ],
            [
                'title' => 'Prototype Review Session',
                'group' => 'UI UX Design Studio',
                'creator' => 'Alex Wong',
                'session_date' => now()->addDays(6)->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'location' => 'Design Studio A',
                'type' => 'in-person',
                'max_attendees' => 12,
                'status' => 'confirmed',
                'notes' => 'Review flows, visual hierarchy, and mobile spacing.',
                'attendees' => ['Alex Wong', 'Mia Cruz', 'Sarah Chen', 'Alex Student'],
            ],
            [
                'title' => 'Security Lab Walkthrough',
                'group' => 'Cybersecurity Review Team',
                'creator' => 'Noah Tan',
                'session_date' => now()->addDays(7)->toDateString(),
                'start_time' => '20:00:00',
                'end_time' => '21:30:00',
                'location' => 'Online (Discord)',
                'type' => 'online',
                'max_attendees' => 8,
                'status' => 'confirmed',
                'notes' => 'Private lab walkthrough for network security practice.',
                'attendees' => ['Noah Tan', 'David Kim', 'Sarah Chen'],
            ],
        ])->each(function (array $sessionData) use ($groups, $sampleUsers, $student) {
            $creator = $sessionData['creator'] === 'Alex Student' ? $student : $sampleUsers[$sessionData['creator']];

            $session = StudySession::create([
                'group_id' => $groups[$sessionData['group']]->id,
                'created_by' => $creator->id,
                'title' => $sessionData['title'],
                'session_date' => $sessionData['session_date'],
                'start_time' => $sessionData['start_time'],
                'end_time' => $sessionData['end_time'],
                'location' => $sessionData['location'],
                'type' => $sessionData['type'],
                'max_attendees' => $sessionData['max_attendees'],
                'status' => $sessionData['status'],
                'notes' => $sessionData['notes'],
            ]);

            $attendeeIds = collect($sessionData['attendees'])
                ->map(fn (string $name) => $name === 'Alex Student' ? $student->id : $sampleUsers[$name]->id)
                ->all();

            $session->attendees()->sync($attendeeIds);
        });

        $this->call(GroupChatSeeder::class);

        collect([
            ['type' => 'discussion_posted', 'title' => 'Discussion posted', 'description' => 'Alex Student started a finals prep thread in Physics Review Circle.', 'group' => 'Physics Review Circle', 'subject' => $discussions->firstWhere('title', 'Finals prep: which topics should we prioritize?'), 'created_at' => now()->subMinutes(35)],
            ['type' => 'resource_uploaded', 'title' => 'Resource uploaded', 'description' => 'Sarah Chen uploaded Finals Formula Map.pdf to Physics Review Circle.', 'group' => 'Physics Review Circle', 'subject' => StudyResource::query()->where('name', 'Finals Formula Map.pdf')->first(), 'created_at' => now()->subHours(8)],
            ['type' => 'session_rsvp', 'title' => 'Session RSVP', 'description' => 'Alex Student joined Finals Review Sprint.', 'group' => 'Physics Review Circle', 'subject' => StudySession::query()->where('title', 'Finals Review Sprint')->first(), 'created_at' => now()->subMinutes(20)],
        ])->each(function (array $activity) use ($groups, $student) {
            ActivityLog::create([
                'user_id' => $student->id,
                'group_id' => $groups[$activity['group']]->id,
                'type' => $activity['type'],
                'title' => $activity['title'],
                'description' => $activity['description'],
                'subject_type' => $activity['subject'] ? $activity['subject']::class : null,
                'subject_id' => $activity['subject']?->getKey(),
                'created_at' => $activity['created_at'],
                'updated_at' => $activity['created_at'],
            ]);
        });
    }
}
