<?php

use App\Models\ActivityLog;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\StudyGroup;
use App\Models\StudyResource;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

test('private group resources discussions and sessions are hidden from non members', function () {
    $owner = User::factory()->create([
        'role' => 'student',
    ]);
    $viewer = User::factory()->create([
        'role' => 'student',
    ]);

    $privateGroup = StudyGroup::create([
        'owner_id' => $owner->id,
        'name' => 'Private Capstone Circle',
        'description' => 'Members only capstone planning.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'private',
        'join_code' => 'CAPSTONE',
        'color' => '#3282B8',
    ]);
    $privateGroup->members()->attach($owner->id);

    StudyResource::create([
        'group_id' => $privateGroup->id,
        'uploaded_by' => $owner->id,
        'name' => 'Private rubric.pdf',
        'category' => 'Study Guide',
        'path' => 'studyhub-resources/private-rubric.pdf',
        'size_bytes' => 1024,
        'uploaded_at' => now(),
    ]);

    Discussion::create([
        'group_id' => $privateGroup->id,
        'author_id' => $owner->id,
        'title' => 'Private defense checklist',
        'body' => 'Only members should see this topic.',
        'views' => 1,
        'trending' => false,
        'last_active_at' => now(),
    ]);

    StudySession::create([
        'group_id' => $privateGroup->id,
        'created_by' => $owner->id,
        'title' => 'Private mock defense',
        'session_date' => now()->addDay()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'location' => 'Room 301',
        'type' => 'in-person',
        'max_attendees' => 5,
        'status' => 'confirmed',
    ]);

    $resourcesResponse = $this->actingAs($viewer)->get(route('studyhub.student.resources'));
    $discussionsResponse = $this->actingAs($viewer)->get(route('studyhub.student.discussions'));
    $sessionsResponse = $this->actingAs($viewer)->get(route('studyhub.student.sessions'));
    $groupDetailResponse = $this->actingAs($viewer)->get(route('studyhub.student.group.show', $privateGroup->id));

    $resourcesResponse->assertOk();
    $discussionsResponse->assertOk();
    $sessionsResponse->assertOk();
    $groupDetailResponse->assertRedirect(route('studyhub.student.groups'));

    expect(collect($resourcesResponse->viewData('resources'))->pluck('name'))->not->toContain('Private rubric.pdf');
    expect(collect($discussionsResponse->viewData('discussions'))->pluck('title'))->not->toContain('Private defense checklist');
    expect(collect($sessionsResponse->viewData('upcomingSessions'))->pluck('title'))->not->toContain('Private mock defense');
});

test('students cannot view private groups they have not joined', function () {
    $owner = User::factory()->create([
        'role' => 'student',
    ]);
    $viewer = User::factory()->create([
        'role' => 'student',
    ]);

    $privateGroup = StudyGroup::create([
        'owner_id' => $owner->id,
        'name' => 'Private UX Studio',
        'description' => 'A private design study group.',
        'category' => 'Information Systems',
        'meeting_style' => 'hybrid',
        'visibility' => 'private',
        'join_code' => 'UXONLY',
        'color' => '#FF6B35',
    ]);
    $privateGroup->members()->attach($owner->id);

    $response = $this->actingAs($viewer)->get(route('studyhub.student.group.show', $privateGroup));

    $response->assertRedirect(route('studyhub.student.groups'));
});

test('students cannot directly open private group discussions unless they are members', function () {
    $owner = User::factory()->create([
        'role' => 'student',
    ]);
    $viewer = User::factory()->create([
        'role' => 'student',
    ]);

    $privateGroup = StudyGroup::create([
        'owner_id' => $owner->id,
        'name' => 'Private Algorithms Lab',
        'description' => 'Private algorithms prep.',
        'category' => 'Programming',
        'meeting_style' => 'online',
        'visibility' => 'private',
        'join_code' => 'ALGO',
        'color' => '#3282B8',
    ]);
    $privateGroup->members()->attach($owner->id);

    $discussion = Discussion::create([
        'group_id' => $privateGroup->id,
        'author_id' => $owner->id,
        'title' => 'Private dynamic programming notes',
        'body' => 'Members only notes.',
        'views' => 1,
        'trending' => false,
        'last_active_at' => now(),
    ]);

    $response = $this->actingAs($viewer)->get(route('studyhub.student.discussions.show', $discussion->id));

    $response->assertRedirect(route('studyhub.student.discussions'));
});

test('students cannot upload resources to unjoined groups', function () {
    Storage::fake('local');

    $owner = User::factory()->create([
        'role' => 'student',
    ]);
    $viewer = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $owner->id,
        'name' => 'Unjoined Upload Group',
        'description' => 'Only members should upload.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($owner->id);

    $response = $this->actingAs($viewer)->post(route('studyhub.student.resources.store'), [
        'group_id' => $group->id,
        'category' => 'Study Guide',
        'resource_file' => UploadedFile::fake()->create('outsider.pdf', 12, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors('group_id');
    expect(StudyResource::where('name', 'outsider.pdf')->exists())->toBeFalse();
});

test('wrong private group join code fails', function () {
    $owner = User::factory()->create([
        'role' => 'student',
    ]);
    $viewer = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $owner->id,
        'name' => 'Wrong Code Group',
        'description' => 'Private group with a code.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'private',
        'join_code' => 'RIGHT',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($owner->id);

    $response = $this->actingAs($viewer)->post(route('studyhub.student.groups.join', $group), [
        'join_group_id' => $group->id,
        'join_code' => 'WRONG',
    ]);

    $response->assertSessionHasErrors('join_code', null, 'joinGroup');
    expect($group->members()->where('users.id', $viewer->id)->exists())->toBeFalse();
});

test('students cannot rsvp to private group sessions unless they are members', function () {
    $owner = User::factory()->create([
        'role' => 'student',
    ]);
    $viewer = User::factory()->create([
        'role' => 'student',
    ]);

    $privateGroup = StudyGroup::create([
        'owner_id' => $owner->id,
        'name' => 'Private AI Review',
        'description' => 'Members only AI review.',
        'category' => 'Artificial Intelligence',
        'meeting_style' => 'online',
        'visibility' => 'private',
        'join_code' => 'AI2026',
        'color' => '#3282B8',
    ]);
    $privateGroup->members()->attach($owner->id);

    $session = StudySession::create([
        'group_id' => $privateGroup->id,
        'created_by' => $owner->id,
        'title' => 'Private model review',
        'session_date' => now()->addDay()->toDateString(),
        'start_time' => '11:00',
        'end_time' => '12:00',
        'location' => 'Meet',
        'type' => 'online',
        'max_attendees' => 10,
        'status' => 'confirmed',
    ]);

    $response = $this->actingAs($viewer)->post(route('studyhub.student.sessions.rsvp', $session->id));

    $response->assertRedirect(route('studyhub.student.sessions'));
    expect($session->attendees()->where('users.id', $viewer->id)->exists())->toBeFalse();
});

test('resource uploads allow approved file types and store files privately', function () {
    Storage::fake('local');
    Storage::fake('public');

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Secure Resource Group',
        'description' => 'Uploads are stored privately.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($student->id);

    $response = $this->actingAs($student)->post(route('studyhub.student.resources.store'), [
        'group_id' => $group->id,
        'category' => 'Study Guide',
        'resource_file' => UploadedFile::fake()->create('review.pdf', 128, 'application/pdf'),
    ]);

    $response->assertRedirect(route('studyhub.student.resources'));

    $resource = StudyResource::where('name', 'review.pdf')->first();

    expect($resource)->not->toBeNull();
    Storage::disk('local')->assertExists($resource->path);
    Storage::disk('public')->assertMissing($resource->path);
});

test('resource uploads reject unsupported file types', function () {
    Storage::fake('local');

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Malware Resistant Group',
        'description' => 'Executable uploads should fail.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($student->id);

    $response = $this->actingAs($student)->post(route('studyhub.student.resources.store'), [
        'group_id' => $group->id,
        'category' => 'Code',
        'resource_file' => UploadedFile::fake()->create('payload.exe', 12, 'application/x-msdownload'),
    ]);

    $response->assertSessionHasErrors('resource_file');
    expect(StudyResource::where('name', 'payload.exe')->exists())->toBeFalse();
});

test('members can download private resource files through the authorized route', function () {
    Storage::fake('local');

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Downloadable Private Group',
        'description' => 'Members can download files.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'private',
        'join_code' => 'FILES',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($student->id);

    Storage::disk('local')->put('studyhub-resources/private-notes.pdf', 'private notes');

    $resource = StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $student->id,
        'name' => 'private-notes.pdf',
        'category' => 'Study Guide',
        'path' => 'studyhub-resources/private-notes.pdf',
        'size_bytes' => 13,
        'uploaded_at' => now(),
    ]);

    $response = $this->actingAs($student)->get(route('studyhub.student.resources.download', $resource));

    $response->assertOk();
    $response->assertHeader('content-disposition');
});

test('non members cannot download private group resource files', function () {
    Storage::fake('local');

    $owner = User::factory()->create([
        'role' => 'student',
    ]);
    $viewer = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $owner->id,
        'name' => 'Blocked Download Group',
        'description' => 'Private files stay private.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'private',
        'join_code' => 'LOCKED',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($owner->id);

    Storage::disk('local')->put('studyhub-resources/locked.pdf', 'locked');

    $resource = StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $owner->id,
        'name' => 'locked.pdf',
        'category' => 'Study Guide',
        'path' => 'studyhub-resources/locked.pdf',
        'size_bytes' => 6,
        'uploaded_at' => now(),
    ]);

    $response = $this->actingAs($viewer)->get(route('studyhub.student.resources.download', $resource));

    $response->assertRedirect(route('studyhub.student.resources'));
});

test('resource uploaders can delete their own resources', function () {
    Storage::fake('local');
    Storage::fake('public');

    $student = User::factory()->create([
        'role' => 'student',
        'display_name' => 'Resource Owner',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Uploader Delete Group',
        'description' => 'Uploaders can remove their own files.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($student->id);

    Storage::disk('local')->put('studyhub-resources/uploader-delete.pdf', 'delete me');

    $resource = StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $student->id,
        'name' => 'uploader-delete.pdf',
        'category' => 'Study Guide',
        'path' => 'studyhub-resources/uploader-delete.pdf',
        'size_bytes' => 9,
        'uploaded_at' => now(),
    ]);

    $response = $this->actingAs($student)->delete(route('studyhub.student.resources.delete', $resource));

    $response->assertRedirect(route('studyhub.student.resources'));
    $response->assertSessionHas('status', 'Resource deleted successfully.');

    expect(StudyResource::find($resource->id))->toBeNull();
    expect(ActivityLog::query()->where('type', 'resource_deleted')->where('subject_id', $resource->id)->exists())->toBeTrue();
    Storage::disk('local')->assertMissing('studyhub-resources/uploader-delete.pdf');
});

test('group owners can delete resources uploaded by other members', function () {
    Storage::fake('local');
    Storage::fake('public');

    $owner = User::factory()->create([
        'role' => 'student',
    ]);
    $uploader = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $owner->id,
        'name' => 'Owner Delete Group',
        'description' => 'Owners moderate group resources.',
        'category' => 'Computer Science',
        'meeting_style' => 'hybrid',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);
    $group->members()->attach([$owner->id, $uploader->id]);

    Storage::disk('local')->put('studyhub-resources/owner-delete.pdf', 'delete me');

    $resource = StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $uploader->id,
        'name' => 'owner-delete.pdf',
        'category' => 'Study Guide',
        'path' => 'studyhub-resources/owner-delete.pdf',
        'size_bytes' => 9,
        'uploaded_at' => now(),
    ]);

    $response = $this->actingAs($owner)->delete(route('studyhub.student.resources.delete', $resource));

    $response->assertRedirect(route('studyhub.student.resources'));
    expect(StudyResource::find($resource->id))->toBeNull();
    Storage::disk('local')->assertMissing('studyhub-resources/owner-delete.pdf');
});

test('regular members cannot delete resources uploaded by other members', function () {
    Storage::fake('local');
    Storage::fake('public');

    $owner = User::factory()->create([
        'role' => 'student',
    ]);
    $uploader = User::factory()->create([
        'role' => 'student',
    ]);
    $member = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $owner->id,
        'name' => 'Member Delete Block Group',
        'description' => 'Members cannot moderate other uploads.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);
    $group->members()->attach([$owner->id, $uploader->id, $member->id]);

    Storage::disk('local')->put('studyhub-resources/member-blocked.pdf', 'keep me');

    $resource = StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $uploader->id,
        'name' => 'member-blocked.pdf',
        'category' => 'Study Guide',
        'path' => 'studyhub-resources/member-blocked.pdf',
        'size_bytes' => 7,
        'uploaded_at' => now(),
    ]);

    $response = $this->actingAs($member)->delete(route('studyhub.student.resources.delete', $resource));

    $response->assertRedirect(route('studyhub.student.resources'));
    $response->assertSessionHas('status', 'You can only delete resources you uploaded or resources in groups you own.');

    expect(StudyResource::find($resource->id))->not->toBeNull();
    Storage::disk('local')->assertExists('studyhub-resources/member-blocked.pdf');
});

test('admins can delete any resource from admin routes', function () {
    Storage::fake('local');
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $owner = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $owner->id,
        'name' => 'Admin Resource Delete Group',
        'description' => 'Admins can moderate any resource.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'private',
        'join_code' => 'ADMIN',
        'color' => '#3282B8',
    ]);

    Storage::disk('local')->put('studyhub-resources/admin-resource-delete.pdf', 'delete me');

    $resource = StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $owner->id,
        'name' => 'admin-resource-delete.pdf',
        'category' => 'Study Guide',
        'path' => 'studyhub-resources/admin-resource-delete.pdf',
        'size_bytes' => 9,
        'uploaded_at' => now(),
    ]);

    $response = $this->actingAs($admin)->delete(route('studyhub.admin.resources.delete', $resource));

    $response->assertRedirect(route('studyhub.admin.groups'));
    expect(StudyResource::find($resource->id))->toBeNull();
    Storage::disk('local')->assertMissing('studyhub-resources/admin-resource-delete.pdf');
});

test('student collaboration actions create activity logs with subjects', function () {
    Storage::fake('local');

    $student = User::factory()->create([
        'role' => 'student',
        'display_name' => 'Activity Student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Activity Log Group',
        'description' => 'Activity logging checks.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);

    $this->actingAs($student)->post(route('studyhub.student.groups.join', $group));

    $this->actingAs($student)->post(route('studyhub.student.resources.store'), [
        'group_id' => $group->id,
        'category' => 'Study Guide',
        'resource_file' => UploadedFile::fake()->create('activity.pdf', 12, 'application/pdf'),
    ]);

    $this->actingAs($student)->post(route('studyhub.student.discussions.store'), [
        'title' => 'Activity discussion',
        'group_id' => $group->id,
        'body' => 'This discussion should be logged.',
    ]);

    $session = StudySession::create([
        'group_id' => $group->id,
        'created_by' => $student->id,
        'title' => 'Activity session',
        'session_date' => now()->addDay()->toDateString(),
        'start_time' => '13:00',
        'end_time' => '14:00',
        'location' => 'Meet',
        'type' => 'online',
        'max_attendees' => 10,
        'status' => 'confirmed',
    ]);

    $otherStudent = User::factory()->create([
        'role' => 'student',
        'display_name' => 'RSVP Student',
    ]);
    $group->members()->attach($otherStudent->id);

    $this->actingAs($otherStudent)->post(route('studyhub.student.sessions.rsvp', $session));

    expect(ActivityLog::query()->where('type', 'group_joined')->whereMorphedTo('subject', $group)->exists())->toBeTrue();
    expect(ActivityLog::query()->where('type', 'resource_uploaded')->where('subject_type', StudyResource::class)->exists())->toBeTrue();
    expect(ActivityLog::query()->where('type', 'discussion_posted')->where('subject_type', Discussion::class)->exists())->toBeTrue();
    expect(ActivityLog::query()->where('type', 'session_rsvp')->whereMorphedTo('subject', $session)->exists())->toBeTrue();
});
