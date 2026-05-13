<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Discussion;
use App\Models\DiscussionNotificationPreference;
use App\Models\DiscussionReply;
use App\Models\GroupChatMessage;
use App\Models\GroupChatRead;
use App\Models\StudyGroup;
use App\Models\StudyResource;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
        GroupChatRead::query()->delete();
        GroupChatMessage::query()->delete();
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
        ])->each(function (array $resource) use ($groups, $sampleUsers) {
            StudyResource::create([
                'group_id' => $groups[$resource['group']]->id,
                'uploaded_by' => $sampleUsers[$resource['uploader']]->id,
                'name' => $resource['name'],
                'category' => $resource['category'],
                'path' => null,
                'file_type' => strtolower(pathinfo($resource['name'], PATHINFO_EXTENSION) ?: 'file'),
                'download_count' => $resource['download_count'] ?? 0,
                'rating_average' => $resource['rating_average'] ?? 0,
                'rating_count' => $resource['rating_count'] ?? 0,
                'size_bytes' => $resource['size_bytes'],
                'uploaded_at' => $resource['uploaded_at'],
            ]);
        });

        $discussions = collect([
            [
                'title' => 'Finals prep: which topics should we prioritize?',
                'body' => 'Let us build one clear review plan for formulas, sample problems, and short explanations before the exam.',
                'group' => 'Physics Review Circle',
                'author' => 'Alex Student',
                'views' => 228,
                'trending' => true,
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
                'last_active_at' => now()->subHours(2),
                'replies' => [
                    ['author' => 'Lisa Park', 'body' => 'Past papers helped me the most. Focus on time-limited practice.', 'created_at' => now()->subHour()],
                ],
            ],
        ])->map(function (array $discussion) use ($groups, $sampleUsers, $student) {
            $author = $discussion['author'] === 'Alex Student' ? $student : $sampleUsers[$discussion['author']];

            $record = Discussion::create([
                'group_id' => $groups[$discussion['group']]->id,
                'author_id' => $author->id,
                'title' => $discussion['title'],
                'body' => $discussion['body'],
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

        collect([
            ['group' => 'Physics Review Circle', 'author' => 'Sarah Chen', 'body' => 'I uploaded the formula map. Can someone check the momentum section?', 'created_at' => now()->subMinutes(42)],
            ['group' => 'Physics Review Circle', 'author' => 'Alex Student', 'body' => 'I can review it before our session and add sample problems.', 'created_at' => now()->subMinutes(35)],
            ['group' => 'Capstone Study Group', 'author' => 'Mia Cruz', 'body' => 'Please drop defense questions here so we can assign answers.', 'created_at' => now()->subMinutes(28)],
            ['group' => 'Capstone Study Group', 'author' => 'Noah Tan', 'body' => 'I will prepare the target users answer and add it to the checklist.', 'created_at' => now()->subMinutes(17)],
            ['group' => 'Computer Science 301', 'author' => 'Sarah Chen', 'body' => 'Graph algorithms review starts with Dijkstra then DP examples.', 'created_at' => now()->subMinutes(22)],
        ])->each(function (array $chat) use ($groups, $sampleUsers, $student) {
            $author = $chat['author'] === 'Alex Student' ? $student : $sampleUsers[$chat['author']];

            GroupChatMessage::create([
                'study_group_id' => $groups[$chat['group']]->id,
                'user_id' => $author->id,
                'body' => $chat['body'],
                'created_at' => $chat['created_at'],
                'updated_at' => $chat['created_at'],
            ]);
        });

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
