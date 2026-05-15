<?php

use App\Models\ActivityLog;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\StudyGroup;
use App\Models\StudyResource;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

test('admins can create users from admin user management', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('studyhub.admin.users.store'), [
        'name' => 'Morgan Lee',
        'email' => 'morgan.lee@studyhub.test',
        'role' => 'student',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('studyhub.admin.users'));
    $response->assertSessionHas('status', 'User account created successfully.');

    $user = User::where('email', 'morgan.lee@studyhub.test')->first();

    expect($user)->not->toBeNull();
    expect($user->role)->toBe('student');
    expect($user->display_name)->toBe('Morgan Lee');
    expect($user->email_verified_at)->not->toBeNull();
});

test('admins can update users from admin user management', function () {
    $admin = User::factory()->admin()->create();
    $targetUser = User::factory()->create([
        'display_name' => 'Old Name',
        'role' => 'student',
    ]);

    $response = $this->actingAs($admin)->put(route('studyhub.admin.users.update', $targetUser), [
        'name' => 'Updated Admin',
        'email' => 'updated.admin@studyhub.test',
        'role' => 'admin',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect(route('studyhub.admin.users.edit', $targetUser));
    $response->assertSessionHas('status', 'User account updated successfully.');

    $targetUser->refresh();

    expect($targetUser->display_name)->toBe('Updated Admin');
    expect($targetUser->email)->toBe('updated.admin@studyhub.test');
    expect($targetUser->role)->toBe('admin');
    expect(Hash::check('new-password', $targetUser->password))->toBeTrue();
});

test('admin management pages render', function () {
    $admin = User::factory()->admin()->create();
    $targetUser = User::factory()->create();

    $group = StudyGroup::create([
        'owner_id' => $targetUser->id,
        'name' => 'Renderable Group',
        'description' => 'A group that proves the admin detail page can render.',
        'category' => 'General',
        'meeting_style' => 'hybrid',
        'visibility' => 'public',
        'color' => '#FF6B35',
    ]);

    $this->actingAs($admin)
        ->get(route('studyhub.admin.dashboard'))
        ->assertOk()
        ->assertSee('Manage Users')
        ->assertSee('Recent Activity')
        ->assertSee('Resource Distribution');
    $this->actingAs($admin)->get(route('studyhub.admin.users.edit', $targetUser))->assertOk();
    $this->actingAs($admin)->get(route('studyhub.admin.groups.show', $group))->assertOk();
    $this->actingAs($admin)->get(route('studyhub.admin.reports'))->assertOk();
});

test('admins can delete other users from admin user management', function () {
    $admin = User::factory()->admin()->create();
    $targetUser = User::factory()->create([
        'display_name' => 'Delete Me',
    ]);

    $response = $this->actingAs($admin)->delete(route('studyhub.admin.users.delete', $targetUser));

    $response->assertRedirect(route('studyhub.admin.users'));
    $response->assertSessionHas('status', 'Delete Me was deleted successfully.');
    expect(User::find($targetUser->id))->toBeNull();
});

test('admins can not delete their own account from admin user management', function () {
    $admin = User::factory()->admin()->create([
        'display_name' => 'Self Admin',
    ]);

    $response = $this->actingAs($admin)->delete(route('studyhub.admin.users.delete', $admin));

    $response->assertRedirect(route('studyhub.admin.users'));
    $response->assertSessionHas('status', 'You cannot delete your own admin account from this page.');
    expect(User::find($admin->id))->not->toBeNull();
});

test('admins can update and delete groups from admin monitoring', function () {
    Storage::fake('local');

    $admin = User::factory()->admin()->create();
    $owner = User::factory()->create();

    $group = StudyGroup::create([
        'owner_id' => $owner->id,
        'name' => 'Original Group',
        'description' => 'Original description.',
        'category' => 'General',
        'meeting_style' => 'in-person',
        'visibility' => 'public',
        'color' => '#4A955F',
    ]);

    Storage::disk('local')->put('studyhub-resources/admin-delete.pdf', 'delete me');

    StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $owner->id,
        'name' => 'admin-delete.pdf',
        'category' => 'Study Guide',
        'path' => 'studyhub-resources/admin-delete.pdf',
        'size_bytes' => 9,
        'uploaded_at' => now(),
    ]);

    $updateResponse = $this->actingAs($admin)->put(route('studyhub.admin.groups.update', $group), [
        'name' => 'Renamed Group',
        'description' => 'Updated by an admin.',
        'category' => 'Programming',
        'meeting_style' => 'online',
        'visibility' => 'private',
        'join_code' => 'secret',
    ]);

    $updateResponse->assertRedirect(route('studyhub.admin.groups.show', $group));
    $updateResponse->assertSessionHas('status', 'Study group updated successfully.');

    $group->refresh();

    expect($group->name)->toBe('Renamed Group');
    expect($group->visibility)->toBe('private');
    expect($group->join_code)->toBe('SECRET');
    expect($group->color)->toBe('#3282B8');

    $deleteResponse = $this->actingAs($admin)->delete(route('studyhub.admin.groups.delete', $group));

    $deleteResponse->assertRedirect(route('studyhub.admin.groups'));
    $deleteResponse->assertSessionHas('status', 'Renamed Group was deleted successfully.');

    expect(StudyGroup::find($group->id))->toBeNull();
    Storage::disk('local')->assertMissing('studyhub-resources/admin-delete.pdf');
});

test('admins can delete study sessions from admin group monitoring', function () {
    $admin = User::factory()->admin()->create([
        'display_name' => 'Admin Moderator',
    ]);
    $owner = User::factory()->create();
    $attendee = User::factory()->create();

    $group = StudyGroup::create([
        'owner_id' => $owner->id,
        'name' => 'Session Delete Group',
        'description' => 'A group with a removable study session.',
        'category' => 'General',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);

    $session = StudySession::create([
        'group_id' => $group->id,
        'created_by' => $owner->id,
        'title' => 'Admin Review Session',
        'session_date' => now()->addDay()->toDateString(),
        'start_time' => '13:00',
        'end_time' => '14:00',
        'location' => 'Online',
        'type' => 'online',
        'max_attendees' => 8,
        'status' => 'confirmed',
    ]);
    $session->attendees()->attach($attendee->id);

    $response = $this->actingAs($admin)->delete(route('studyhub.admin.sessions.delete', $session));

    $response->assertRedirect(route('studyhub.admin.groups.show', $group));
    $response->assertSessionHas('status', 'Admin Review Session was deleted successfully.');

    expect(StudySession::find($session->id))->toBeNull();
    expect(DB::table('session_attendees')->where('study_session_id', $session->id)->exists())->toBeFalse();
    expect(ActivityLog::query()->where('type', 'session_deleted')->where('subject_id', $session->id)->exists())->toBeTrue();
});

test('admins can moderate discussions and replies from admin group monitoring', function () {
    $admin = User::factory()->admin()->create([
        'display_name' => 'Admin Moderator',
    ]);
    $student = User::factory()->create([
        'role' => 'student',
        'display_name' => 'Discussion Student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Moderated Discussion Group',
        'description' => 'A group that needs admin moderation.',
        'category' => 'General',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);

    $discussion = Discussion::create([
        'group_id' => $group->id,
        'author_id' => $student->id,
        'title' => 'Off topic discussion',
        'body' => 'This should be removable by an admin.',
        'last_active_at' => now(),
    ]);

    $reply = DiscussionReply::create([
        'discussion_id' => $discussion->id,
        'author_id' => $student->id,
        'body' => 'This reply should be removable too.',
    ]);

    $this->actingAs($admin)
        ->get(route('studyhub.admin.groups.show', $group))
        ->assertOk()
        ->assertSee('Recent Replies')
        ->assertSee('Off topic discussion');

    $replyResponse = $this->actingAs($admin)->delete(route('studyhub.admin.discussion-replies.delete', $reply), [
        'redirect_to' => route('studyhub.admin.groups.show', $group),
    ]);

    $replyResponse->assertRedirect(route('studyhub.admin.groups.show', $group));
    $replyResponse->assertSessionHas('status', 'Discussion reply deleted successfully.');
    expect(DiscussionReply::find($reply->id))->toBeNull();
    expect(ActivityLog::query()->where('type', 'discussion_reply_deleted')->where('subject_id', $reply->id)->exists())->toBeTrue();

    $discussionResponse = $this->actingAs($admin)->delete(route('studyhub.admin.discussions.delete', $discussion), [
        'redirect_to' => route('studyhub.admin.groups.show', $group),
    ]);

    $discussionResponse->assertRedirect(route('studyhub.admin.groups.show', $group));
    $discussionResponse->assertSessionHas('status', 'Off topic discussion was deleted successfully.');
    expect(Discussion::find($discussion->id))->toBeNull();
    expect(ActivityLog::query()->where('type', 'discussion_deleted')->where('subject_id', $discussion->id)->exists())->toBeTrue();
});

test('admins can export reports as csv', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create([
        'role' => 'student',
    ]);

    $response = $this->actingAs($admin)->get(route('studyhub.admin.reports.export'));

    $response->assertOk();
    $response->assertDownload('studyhub-report-'.now()->format('Y-m-d').'.csv');
    expect($response->streamedContent())->toContain('StudyHub Admin Report');
});
