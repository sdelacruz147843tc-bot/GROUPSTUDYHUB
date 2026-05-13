<?php

use App\Models\ActivityLog;
use App\Models\Discussion;
use App\Models\DiscussionHelpfulVote;
use App\Models\DiscussionReply;
use App\Models\GroupChatMessage;
use App\Models\ResourceFolder;
use App\Models\ResourceView;
use App\Models\SavedResource;
use App\Models\StudyGroup;
use App\Models\StudyResource;
use App\Models\StudyResourceReview;
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

test('group members can post and view group chat messages', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'display_name' => 'Chat Student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Chat Enabled Group',
        'description' => 'Members coordinate in chat.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($student->id);

    $response = $this->actingAs($student)->post(route('studyhub.student.groups.messages.store', $group), [
        'body' => 'Can we review the project checklist tonight?',
    ]);

    $response->assertRedirect(route('studyhub.student.group.show', $group));

    expect(GroupChatMessage::where('study_group_id', $group->id)->where('body', 'Can we review the project checklist tonight?')->exists())->toBeTrue();

    $detailResponse = $this->actingAs($student)->get(route('studyhub.student.group.show', $group));
    $detailResponse->assertOk();
    $detailResponse->assertSee('Can we review the project checklist tonight?');
});

test('non members cannot post to group chat', function () {
    $owner = User::factory()->create([
        'role' => 'student',
    ]);
    $viewer = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $owner->id,
        'name' => 'Members Only Chat Group',
        'description' => 'Chat should stay members only.',
        'category' => 'Science',
        'meeting_style' => 'hybrid',
        'visibility' => 'public',
        'color' => '#4A955F',
    ]);
    $group->members()->attach($owner->id);

    $response = $this->actingAs($viewer)->post(route('studyhub.student.groups.messages.store', $group), [
        'body' => 'I should not be able to send this.',
    ]);

    $response->assertRedirect(route('studyhub.student.group.show', $group));
    expect(GroupChatMessage::where('study_group_id', $group->id)->exists())->toBeFalse();

    $detailResponse = $this->actingAs($viewer)->get(route('studyhub.student.group.show', $group));
    $detailResponse->assertOk();
    $detailResponse->assertSee('No chats yet');
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

test('students can create discussions with images', function () {
    Storage::fake('local');

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Image Question Group',
        'description' => 'Questions can include screenshots.',
        'category' => 'Physics',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#4A955F',
    ]);

    $group->members()->attach($student->id);

    $response = $this->actingAs($student)->post(route('studyhub.student.discussions.store'), [
        'title' => 'Can someone explain this graph?',
        'group_id' => $group->id,
        'body' => 'I attached the graph from my notes.',
        'discussion_image' => UploadedFile::fake()->createWithContent(
            'graph.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
        ),
    ]);

    $response->assertRedirect(route('studyhub.student.discussions'));

    $discussion = Discussion::where('title', 'Can someone explain this graph?')->first();

    expect($discussion)->not->toBeNull();
    expect($discussion->image_path)->not->toBeNull();
    expect($discussion->image_original_name)->toBe('graph.png');
    Storage::disk('local')->assertExists($discussion->image_path);

    $imageResponse = $this->actingAs($student)->get(route('studyhub.student.discussions.image', $discussion));
    $imageResponse->assertOk();
});

test('students can mark discussions as helpful', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Helpful Discussion Group',
        'description' => 'Votes surface useful posts.',
        'category' => 'Mathematics',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#4A955F',
    ]);

    $group->members()->attach($student->id);

    $discussion = Discussion::create([
        'group_id' => $group->id,
        'author_id' => $student->id,
        'title' => 'Helpful proof explanation',
        'body' => 'This explanation should be easy to upvote.',
        'views' => 1,
        'trending' => false,
        'last_active_at' => now(),
    ]);

    $this->actingAs($student)
        ->post(route('studyhub.student.discussions.helpful', $discussion))
        ->assertRedirect();

    expect(DiscussionHelpfulVote::where('discussion_id', $discussion->id)->where('user_id', $student->id)->exists())->toBeTrue();

    $response = $this->actingAs($student)->get(route('studyhub.student.discussions'));
    $formatted = collect($response->viewData('discussions')->items())->firstWhere('title', 'Helpful proof explanation');

    expect($formatted['helpful_votes'])->toBe(1);
    expect($formatted['viewer_voted_helpful'])->toBeTrue();

    $this->actingAs($student)
        ->postJson(route('studyhub.student.discussions.helpful', $discussion))
        ->assertOk()
        ->assertJson([
            'helpful_votes' => 0,
            'viewer_voted_helpful' => false,
        ]);

    expect(DiscussionHelpfulVote::where('discussion_id', $discussion->id)->where('user_id', $student->id)->exists())->toBeFalse();
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

test('students can filter sessions and create sessions', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'display_name' => 'Session Planner',
    ]);

    $algorithms = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Algorithms Circle',
        'description' => 'Algorithm practice.',
        'category' => 'Computer Science',
        'meeting_style' => 'hybrid',
        'visibility' => 'public',
        'color' => '#22c55e',
    ]);
    $databases = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Database Systems',
        'description' => 'Database review.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3b82f6',
    ]);
    $algorithms->members()->attach($student->id);
    $databases->members()->attach($student->id);

    StudySession::create([
        'group_id' => $algorithms->id,
        'created_by' => $student->id,
        'title' => 'Algorithms Drill',
        'session_date' => now()->addDay()->toDateString(),
        'start_time' => '10:00',
        'end_time' => '11:00',
        'location' => 'Library',
        'type' => 'in-person',
        'status' => 'confirmed',
        'max_attendees' => 12,
    ]);
    StudySession::create([
        'group_id' => $databases->id,
        'created_by' => $student->id,
        'title' => 'Database Cleanup',
        'session_date' => now()->addDays(2)->toDateString(),
        'start_time' => '13:00',
        'end_time' => '14:00',
        'location' => 'Online meeting',
        'meeting_url' => 'https://meet.example.com/database',
        'type' => 'online',
        'status' => 'confirmed',
        'max_attendees' => 12,
    ]);

    $filteredResponse = $this->actingAs($student)->get(route('studyhub.student.sessions', [
        'tab' => 'upcoming',
        'view' => 'list',
        'group_id' => $algorithms->id,
        'week_start' => now()->addWeek()->startOfWeek()->toDateString(),
    ]));

    $filteredResponse->assertOk();
    expect(collect($filteredResponse->viewData('upcomingSessions'))->pluck('title')->all())
        ->toContain('Algorithms Drill')
        ->not->toContain('Database Cleanup');
    expect($filteredResponse->viewData('sessionFilters'))->toMatchArray([
        'tab' => 'upcoming',
        'view' => 'list',
        'group_id' => (string) $algorithms->id,
        'week_start' => now()->addWeek()->startOfWeek()->toDateString(),
    ]);
    expect($filteredResponse->viewData('calendarPrevWeek'))->toBe(now()->startOfWeek()->toDateString());

    $createResponse = $this->actingAs($student)->post(route('studyhub.student.sessions.store'), [
        'title' => 'Quiz Prep Sprint',
        'group_id' => $algorithms->id,
        'date' => now()->addDays(3)->toDateString(),
        'start_time' => '15:00',
        'end_time' => '16:00',
        'location' => 'Room 204',
        'type' => 'in-person',
        'max_attendees' => 10,
        'notes' => 'Bring practice questions.',
    ]);

    $createResponse->assertRedirect(route('studyhub.student.sessions'));
    expect(StudySession::query()
        ->where('title', 'Quiz Prep Sprint')
        ->where('notes', 'Bring practice questions.')
        ->exists())->toBeTrue();
});

test('students can filter resources by library fields', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Resource Search Group',
        'description' => 'Searchable shared files.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($student->id);

    StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $student->id,
        'name' => 'Exam Trees Reviewer.pdf',
        'category' => 'Study Guide',
        'file_type' => 'pdf',
        'download_count' => 24,
        'rating_average' => 4.8,
        'rating_count' => 12,
        'size_bytes' => 1024,
        'uploaded_at' => now(),
    ]);

    StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $student->id,
        'name' => 'Lecture Outline.docx',
        'category' => 'Lecture Notes',
        'file_type' => 'docx',
        'size_bytes' => 1024,
        'uploaded_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($student)->get(route('studyhub.student.resources', [
        'q' => 'trees',
        'category' => 'Study Guide',
        'group_id' => $group->id,
        'availability' => 'unavailable',
    ]));

    $response->assertOk();

    $resourceNames = collect($response->viewData('resources')->items())->pluck('name');

    expect($resourceNames)->toContain('Exam Trees Reviewer.pdf');
    expect($resourceNames)->not->toContain('Lecture Outline.docx');
    expect($response->viewData('activeFilterCount'))->toBeGreaterThan(0);
});

test('students can sort resources by downloads and rating', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Resource Sorting Group',
        'description' => 'Sortable resource metadata.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($student->id);

    StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $student->id,
        'name' => 'Most Rated.pdf',
        'category' => 'Study Guide',
        'file_type' => 'pdf',
        'download_count' => 10,
        'rating_average' => 4.9,
        'rating_count' => 20,
        'size_bytes' => 1024,
        'uploaded_at' => now()->subDay(),
    ]);

    StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $student->id,
        'name' => 'Most Downloaded.pdf',
        'category' => 'Study Guide',
        'file_type' => 'pdf',
        'download_count' => 99,
        'rating_average' => 4.1,
        'rating_count' => 8,
        'size_bytes' => 1024,
        'uploaded_at' => now(),
    ]);

    $downloadResponse = $this->actingAs($student)->get(route('studyhub.student.resources', [
        'sort' => 'most_downloaded',
    ]));

    $ratingResponse = $this->actingAs($student)->get(route('studyhub.student.resources', [
        'sort' => 'highest_rated',
    ]));

    expect(collect($downloadResponse->viewData('resources')->items())->pluck('name')->first())->toBe('Most Downloaded.pdf');
    expect(collect($ratingResponse->viewData('resources')->items())->pluck('name')->first())->toBe('Most Rated.pdf');
});

test('students can rate and review visible resources', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'display_name' => 'Helpful Reviewer',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Reviewable Resource Group',
        'description' => 'Resources can collect feedback.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($student->id);

    $resource = StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $student->id,
        'name' => 'Review Me.pdf',
        'category' => 'Study Guide',
        'file_type' => 'pdf',
        'size_bytes' => 1024,
        'uploaded_at' => now(),
    ]);

    $response = $this->actingAs($student)->post(route('studyhub.student.resources.reviews.store', $resource), [
        'accuracy_rating' => 5,
        'clarity_rating' => 4,
        'usefulness_rating' => 3,
        'review_text' => 'Clear examples and useful exam notes.',
    ]);

    $response->assertRedirect(route('studyhub.student.resources'));

    $resource->refresh();

    expect(StudyResourceReview::where('study_resource_id', $resource->id)->count())->toBe(1);
    expect($resource->rating_average)->toBe('4.00');
    expect($resource->rating_count)->toBe(1);

    $this->actingAs($student)->post(route('studyhub.student.resources.reviews.store', $resource), [
        'accuracy_rating' => 5,
        'clarity_rating' => 5,
        'usefulness_rating' => 5,
        'review_text' => 'Updated after using it for finals.',
    ]);

    $resource->refresh();
    $review = StudyResourceReview::where('study_resource_id', $resource->id)->first();

    expect(StudyResourceReview::where('study_resource_id', $resource->id)->count())->toBe(1);
    expect($resource->rating_average)->toBe('5.00');
    expect($resource->rating_count)->toBe(1);
    expect($review->review_text)->toBe('Updated after using it for finals.');

    $resourcesResponse = $this->actingAs($student)->get(route('studyhub.student.resources'));
    $formattedResource = collect($resourcesResponse->viewData('resources')->items())->firstWhere('name', 'Review Me.pdf');

    expect($formattedResource['viewer_review']['accuracy_rating'])->toBe(5);
    expect($formattedResource['latest_review']['review_text'])->toBe('Updated after using it for finals.');
});

test('students cannot review private resources they cannot view', function () {
    $owner = User::factory()->create([
        'role' => 'student',
    ]);
    $viewer = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $owner->id,
        'name' => 'Private Review Block Group',
        'description' => 'Private reviews stay protected.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'private',
        'join_code' => 'REVIEWS',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($owner->id);

    $resource = StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $owner->id,
        'name' => 'Private Review.pdf',
        'category' => 'Study Guide',
        'file_type' => 'pdf',
        'size_bytes' => 1024,
        'uploaded_at' => now(),
    ]);

    $response = $this->actingAs($viewer)->post(route('studyhub.student.resources.reviews.store', $resource), [
        'accuracy_rating' => 5,
        'clarity_rating' => 5,
        'usefulness_rating' => 5,
    ]);

    $response->assertRedirect(route('studyhub.student.resources'));
    expect(StudyResourceReview::where('study_resource_id', $resource->id)->exists())->toBeFalse();
});

test('students can save resources and organize them into folders', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Library Save Group',
        'description' => 'Resources worth saving.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($student->id);

    $resource = StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $student->id,
        'name' => 'Folder Ready Notes.pdf',
        'category' => 'Study Guide',
        'file_type' => 'pdf',
        'size_bytes' => 1024,
        'uploaded_at' => now(),
    ]);

    $saveResponse = $this->actingAs($student)->post(route('studyhub.student.resources.save', $resource));

    $saveResponse->assertRedirect();
    expect(SavedResource::where('user_id', $student->id)->where('study_resource_id', $resource->id)->exists())->toBeTrue();

    $folderResponse = $this->actingAs($student)->post(route('studyhub.student.library.folders.store'), [
        'name' => 'Finals Review',
        'color' => '#22c55e',
    ]);

    $folderResponse->assertRedirect();
    $folder = ResourceFolder::where('user_id', $student->id)->where('name', 'Finals Review')->first();
    $saved = SavedResource::where('user_id', $student->id)->where('study_resource_id', $resource->id)->first();

    $moveResponse = $this->actingAs($student)->patch(route('studyhub.student.library.saved.update', $saved), [
        'resource_folder_id' => $folder->id,
    ]);

    $moveResponse->assertRedirect();
    expect($saved->refresh()->resource_folder_id)->toBe($folder->id);

    $libraryResponse = $this->actingAs($student)->get(route('studyhub.student.library', [
        'folder' => $folder->id,
    ]));

    $libraryResponse->assertOk();
    expect(collect($libraryResponse->viewData('savedResources')->items())->pluck('name'))->toContain('Folder Ready Notes.pdf');
});

test('students can use my library header filters', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Library Filter Group',
        'description' => 'Saved files with useful filters.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($student->id);

    $pdf = StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $student->id,
        'name' => 'Downloadable Formula.pdf',
        'category' => 'Study Guide',
        'path' => 'studyhub-resources/formula.pdf',
        'file_type' => 'pdf',
        'size_bytes' => 1024,
        'uploaded_at' => now(),
    ]);

    $docx = StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $student->id,
        'name' => 'Offline Outline.docx',
        'category' => 'Lecture Notes',
        'file_type' => 'docx',
        'size_bytes' => 1024,
        'uploaded_at' => now(),
    ]);

    SavedResource::create([
        'user_id' => $student->id,
        'study_resource_id' => $pdf->id,
        'saved_at' => now(),
    ]);

    SavedResource::create([
        'user_id' => $student->id,
        'study_resource_id' => $docx->id,
        'saved_at' => now()->subDay(),
    ]);

    ResourceView::create([
        'user_id' => $student->id,
        'study_resource_id' => $pdf->id,
        'viewed_at' => now(),
    ]);

    $fileTypeResponse = $this->actingAs($student)->get(route('studyhub.student.library', [
        'file_type' => 'pdf',
    ]));

    $downloadableResponse = $this->actingAs($student)->get(route('studyhub.student.library', [
        'availability' => 'downloadable',
    ]));

    $recentResponse = $this->actingAs($student)->get(route('studyhub.student.library', [
        'item' => 'recent',
    ]));

    expect(collect($fileTypeResponse->viewData('savedResources')->items())->pluck('name'))->toContain('Downloadable Formula.pdf')->not->toContain('Offline Outline.docx');
    expect(collect($downloadableResponse->viewData('savedResources')->items())->pluck('name'))->toContain('Downloadable Formula.pdf')->not->toContain('Offline Outline.docx');
    expect(collect($recentResponse->viewData('savedResources')->items())->pluck('name'))->toContain('Downloadable Formula.pdf')->not->toContain('Offline Outline.docx');
});

test('students cannot save private resources they cannot view', function () {
    $owner = User::factory()->create([
        'role' => 'student',
    ]);
    $viewer = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $owner->id,
        'name' => 'Hidden Library Group',
        'description' => 'Private resources stay private.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'private',
        'join_code' => 'HIDDEN',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($owner->id);

    $resource = StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $owner->id,
        'name' => 'Hidden Notes.pdf',
        'category' => 'Study Guide',
        'file_type' => 'pdf',
        'size_bytes' => 1024,
        'uploaded_at' => now(),
    ]);

    $response = $this->actingAs($viewer)->post(route('studyhub.student.resources.save', $resource));

    $response->assertRedirect();
    expect(SavedResource::where('user_id', $viewer->id)->where('study_resource_id', $resource->id)->exists())->toBeFalse();
});

test('resource views are tracked when students open resource files', function () {
    Storage::fake('local');

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Recently Viewed Group',
        'description' => 'Recent resources are tracked.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($student->id);

    $path = 'studyhub-resources/recent.pdf';
    Storage::disk('local')->put($path, 'recent resource');

    $resource = StudyResource::create([
        'group_id' => $group->id,
        'uploaded_by' => $student->id,
        'name' => 'Recently Viewed.pdf',
        'category' => 'Study Guide',
        'path' => $path,
        'file_type' => 'pdf',
        'size_bytes' => 1024,
        'uploaded_at' => now(),
    ]);

    $response = $this->actingAs($student)->get(route('studyhub.student.resources.view', $resource));

    $response->assertOk();
    expect(ResourceView::where('user_id', $student->id)->where('study_resource_id', $resource->id)->exists())->toBeTrue();

    $libraryResponse = $this->actingAs($student)->get(route('studyhub.student.library'));

    $libraryResponse->assertOk();
    expect(collect($libraryResponse->viewData('recentResources'))->pluck('name'))->toContain('Recently Viewed.pdf');
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
    expect($resource->file_type)->toBe('pdf');
    expect(SavedResource::where('user_id', $student->id)->where('study_resource_id', $resource->id)->exists())->toBeTrue();
    Storage::disk('local')->assertExists($resource->path);
    Storage::disk('public')->assertMissing($resource->path);
});

test('students can upload resources from my library and return to library', function () {
    Storage::fake('local');

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $group = StudyGroup::create([
        'owner_id' => $student->id,
        'name' => 'Library Upload Group',
        'description' => 'Uploads can start from My Library.',
        'category' => 'Computer Science',
        'meeting_style' => 'online',
        'visibility' => 'public',
        'color' => '#3282B8',
    ]);
    $group->members()->attach($student->id);

    $response = $this->actingAs($student)->post(route('studyhub.student.resources.store'), [
        'group_id' => $group->id,
        'category' => 'Study Guide',
        'resource_file' => UploadedFile::fake()->create('library-upload.pdf', 128, 'application/pdf'),
        'redirect_to' => route('studyhub.student.library'),
        'library_upload_intent' => '1',
    ]);

    $response->assertRedirect(route('studyhub.student.library'));

    $resource = StudyResource::where('name', 'library-upload.pdf')->first();

    expect($resource)->not->toBeNull();
    expect(SavedResource::where('user_id', $student->id)->where('study_resource_id', $resource->id)->exists())->toBeTrue();
    Storage::disk('local')->assertExists($resource->path);
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
    expect($resource->refresh()->download_count)->toBe(1);
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
