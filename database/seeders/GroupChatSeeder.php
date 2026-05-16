<?php

namespace Database\Seeders;

use App\Models\GroupChatMessage;
use App\Models\GroupChatRead;
use App\Models\StudyGroup;
use App\Models\User;
use Illuminate\Database\Seeder;

class GroupChatSeeder extends Seeder
{
    public function run(): void
    {
        GroupChatRead::query()->delete();
        GroupChatMessage::query()->delete();

        $users = User::query()
            ->where('role', 'student')
            ->get()
            ->keyBy('name');

        $groups = StudyGroup::query()
            ->with('members:id,name')
            ->get()
            ->keyBy('name');

        $topics = [
            'Physics Review Circle' => [
                'focus' => 'finals formula review',
                'artifact' => 'the formula map',
                'session' => 'the review sprint',
                'problem' => 'momentum and energy conservation',
            ],
            'Capstone Study Group' => [
                'focus' => 'capstone defense prep',
                'artifact' => 'the defense checklist',
                'session' => 'the demo dry run',
                'problem' => 'the walkthrough script',
            ],
            'Computer Science 301' => [
                'focus' => 'algorithm analysis',
                'artifact' => 'the DP examples',
                'session' => 'the algorithms review',
                'problem' => 'graph traversal edge cases',
            ],
            'Data Structures Study' => [
                'focus' => 'data structure drills',
                'artifact' => 'the practice queue',
                'session' => 'Friday problem solving',
                'problem' => 'heap and tree rotations',
            ],
            'Calculus II Prep' => [
                'focus' => 'calculus exam prep',
                'artifact' => 'the integration worksheet',
                'session' => 'the midterm prep call',
                'problem' => 'series convergence tests',
            ],
            'Web Development' => [
                'focus' => 'full-stack project work',
                'artifact' => 'the project template',
                'session' => 'the build review',
                'problem' => 'form validation and routing',
            ],
            'Database Systems' => [
                'focus' => 'database design practice',
                'artifact' => 'the schema diagram',
                'session' => 'the normalization workshop',
                'problem' => 'many-to-many relationship modeling',
            ],
            'Machine Learning Basics' => [
                'focus' => 'ML fundamentals',
                'artifact' => 'the notebook examples',
                'session' => 'the model walkthrough',
                'problem' => 'train/test split and overfitting',
            ],
            'Language Exchange Circle' => [
                'focus' => 'conversation practice',
                'artifact' => 'the vocabulary sheet',
                'session' => 'language practice hour',
                'problem' => 'past tense prompts',
            ],
            'Business Analytics Lab' => [
                'focus' => 'dashboard critique',
                'artifact' => 'the case study spreadsheet',
                'session' => 'the analytics lab',
                'problem' => 'chart choice and KPI wording',
            ],
            'UI UX Design Studio' => [
                'focus' => 'prototype review',
                'artifact' => 'the wireframe kit',
                'session' => 'studio critique',
                'problem' => 'mobile spacing and hierarchy',
            ],
            'Cybersecurity Review Team' => [
                'focus' => 'security lab review',
                'artifact' => 'the threat model checklist',
                'session' => 'the lab walkthrough',
                'problem' => 'network scanning notes',
            ],
        ];

        $messageTemplates = [
            'I opened {artifact} and marked the parts that connect to {problem}.',
            'For {session}, can we start with a quick recap of {focus} before questions?',
            'I added two notes in my reviewer. The second one explains {problem} better.',
            'Who can take the first 10 minutes tomorrow? I can handle setup and attendance.',
            'The examples from class match this topic pretty closely, especially {problem}.',
            'I am free after 7 PM if anyone wants a short call about {focus}.',
            'Please check the latest version of {artifact}; I cleaned up the confusing section.',
            'I found one mistake in my notes, so ignore the old screenshot I sent earlier.',
            'Can someone bring one practice item for {problem}? We can compare methods.',
            'I will summarize today in three bullets after {session}.',
            'This is starting to make sense. The key for me was tying {focus} to examples.',
            'I can make flashcards from {artifact} tonight and share the set here.',
            'Let us keep the first half focused and use the last half for open questions.',
            'Does anyone need the link for {session}? I can pin it with the agenda.',
            'I finished my assigned part. Please review the wording before I call it final.',
            'Small update: the resource has the right answer, but the explanation skips a step.',
            'Can we split into pairs for {problem}? It might be faster than one big thread.',
            'I will be five minutes late, but I already reviewed {artifact}.',
        ];

        $now = now()->seconds(0);
        $rows = [];

        collect($topics)->each(function (array $topic, string $groupName) use ($groups, $messageTemplates, $now, &$rows) {
            $group = $groups->get($groupName);

            if (! $group) {
                return;
            }

            $members = $group->members->values();

            if ($members->isEmpty()) {
                return;
            }

            foreach ($messageTemplates as $index => $template) {
                $author = $members[$index % $members->count()];
                $createdAt = $now
                    ->copy()
                    ->subDays((int) floor($index / 6))
                    ->subMinutes(($groups->keys()->search($groupName) * 11) + ((count($messageTemplates) - $index) * 7));

                $rows[] = [
                    'study_group_id' => $group->id,
                    'user_id' => $author->id,
                    'body' => strtr($template, [
                        '{focus}' => $topic['focus'],
                        '{artifact}' => $topic['artifact'],
                        '{session}' => $topic['session'],
                        '{problem}' => $topic['problem'],
                    ]),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }
        });

        collect($rows)
            ->sortBy('created_at')
            ->chunk(100)
            ->each(fn ($chunk) => GroupChatMessage::query()->insert($chunk->values()->all()));

        $groups->each(function (StudyGroup $group) {
            $latestMessageAt = GroupChatMessage::query()
                ->where('study_group_id', $group->id)
                ->max('created_at');

            if (! $latestMessageAt) {
                return;
            }

            $group->members->each(function (User $member, int $index) use ($group, $latestMessageAt) {
                $lastReadAt = now()->parse($latestMessageAt)->subMinutes($index % 3 === 0 ? 35 : 5);

                GroupChatRead::query()->updateOrCreate([
                    'study_group_id' => $group->id,
                    'user_id' => $member->id,
                ], [
                    'last_read_at' => $lastReadAt,
                ]);
            });
        });
    }
}
