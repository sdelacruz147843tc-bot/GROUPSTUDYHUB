<?php

use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\StudyGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('students can create a study group in the database', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $response = $this->actingAs($student)->post(route('studyhub.student.groups.store'), [
        'name' => 'Physics Review Circle',
        'description' => 'Weekly review for mechanics and electromagnetism.',
        'category' => 'Physics',
        'meeting_style' => 'hybrid',
        'visibility' => 'public',
    ]);

    $response->assertRedirect(route('studyhub.student.groups'));

    $group = StudyGroup::where('name', 'Physics Review Circle')->first();

    expect($group)->not->toBeNull();
    expect($group->members()->where('users.id', $student->id)->exists())->toBeTrue();
});

test('students can create discussions in joined groups', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Chemistry 201',
        'description' => 'Organic chemistry study group.',
        'category' => 'Chemistry',
        'meeting_style' => 'in-person',
        'visibility' => 'public',
        'color' => '#4A955F',
    ]);

    $group->members()->attach($student->id);

    $response = $this->actingAs($student)->post(route('studyhub.student.discussions.store'), [
        'title' => 'Best way to review reactions?',
        'group_id' => $group->id,
        'body' => 'What study approach helped you remember the major reaction patterns?',
    ]);

    $response->assertRedirect(route('studyhub.student.discussions'));

    expect(Discussion::where('title', 'Best way to review reactions?')->exists())->toBeTrue();
});

test('students can post direct replies to discussion replies', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'display_name' => 'Taylor Student',
    ]);

    $otherStudent = User::factory()->create([
        'role' => 'student',
        'display_name' => 'Jamie Student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Physics 101',
        'description' => 'Physics fundamentals.',
        'category' => 'Physics',
        'meeting_style' => 'in-person',
        'visibility' => 'public',
        'color' => '#4A955F',
    ]);

    $group->members()->attach([$student->id, $otherStudent->id]);

    $discussion = Discussion::create([
        'group_id' => $group->id,
        'author_id' => $student->id,
        'title' => 'Kinematics review',
        'body' => 'Let us review projectile motion formulas.',
        'views' => 1,
        'trending' => false,
        'last_active_at' => now(),
    ]);

    $parentReply = DiscussionReply::create([
        'discussion_id' => $discussion->id,
        'author_id' => $otherStudent->id,
        'body' => 'I can share my reviewer for this.',
    ]);

    $response = $this->actingAs($student)->post(route('studyhub.student.discussions.reply', $discussion->id), [
        'reply' => 'That would help a lot, please send it.',
        'parent_reply_id' => $parentReply->id,
    ]);

    $response->assertRedirect(route('studyhub.student.discussions.show', $discussion->id));

    $childReply = DiscussionReply::where('body', 'That would help a lot, please send it.')->first();

    expect($childReply)->not->toBeNull();
    expect($childReply->parent_reply_id)->toBe($parentReply->id);
});
