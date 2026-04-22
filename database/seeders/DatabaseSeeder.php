<?php

namespace Database\Seeders;

use App\Models\Discussion;
use App\Models\DiscussionReply;
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

        DiscussionReply::query()->delete();
        Discussion::query()->delete();
        StudyResource::query()->delete();
        StudySession::query()->delete();

        collect([
            ['group' => 'Computer Science 301', 'uploader' => 'Sarah Chen', 'name' => 'Algorithm Analysis - Chapter 5.pdf', 'category' => 'Lecture Notes', 'size_bytes' => 3355443, 'uploaded_at' => now()->subDays(6)],
            ['group' => 'Data Structures Study', 'uploader' => 'Mike Johnson', 'name' => 'Data Structures Cheat Sheet.pdf', 'category' => 'Study Guide', 'size_bytes' => 1572864, 'uploaded_at' => now()->subDays(5)],
            ['group' => 'Calculus II Prep', 'uploader' => 'Emma Davis', 'name' => 'Calculus Practice Problems.docx', 'category' => 'Assignments', 'size_bytes' => 911360, 'uploaded_at' => now()->subDays(4)],
            ['group' => 'Web Development', 'uploader' => 'Alex Wong', 'name' => 'Web Dev Project Template.zip', 'category' => 'Code', 'size_bytes' => 2202009, 'uploaded_at' => now()->subDays(3)],
            ['group' => 'Database Systems', 'uploader' => 'Lisa Park', 'name' => 'Database Schema Design.pdf', 'category' => 'Lecture Notes', 'size_bytes' => 1887436, 'uploaded_at' => now()->subDays(2)],
            ['group' => 'Machine Learning Basics', 'uploader' => 'Rachel Green', 'name' => 'ML Algorithm Implementations.py', 'category' => 'Code', 'size_bytes' => 46080, 'uploaded_at' => now()->subDay()],
        ])->each(function (array $resource) use ($groups, $sampleUsers) {
            StudyResource::create([
                'group_id' => $groups[$resource['group']]->id,
                'uploaded_by' => $sampleUsers[$resource['uploader']]->id,
                'name' => $resource['name'],
                'category' => $resource['category'],
                'path' => null,
                'size_bytes' => $resource['size_bytes'],
                'uploaded_at' => $resource['uploaded_at'],
            ]);
        });

        $discussions = collect([
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
        ])->map(function (array $discussion) use ($groups, $sampleUsers) {
            $record = Discussion::create([
                'group_id' => $groups[$discussion['group']]->id,
                'author_id' => $sampleUsers[$discussion['author']]->id,
                'title' => $discussion['title'],
                'body' => $discussion['body'],
                'views' => $discussion['views'],
                'trending' => $discussion['trending'],
                'last_active_at' => $discussion['last_active_at'],
            ]);

            collect($discussion['replies'])->each(function (array $reply) use ($record, $sampleUsers) {
                $created = DiscussionReply::create([
                    'discussion_id' => $record->id,
                    'author_id' => $sampleUsers[$reply['author']]->id,
                    'body' => $reply['body'],
                    'created_at' => $reply['created_at'],
                    'updated_at' => $reply['created_at'],
                ]);
            });

            return $record;
        });

        collect([
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
    }
}
