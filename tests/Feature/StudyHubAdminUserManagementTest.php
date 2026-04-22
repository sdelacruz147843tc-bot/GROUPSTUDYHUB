<?php

use App\Models\User;

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
});

test('admins can delete other users from admin user management', function () {
    $admin = User::factory()->admin()->create();
    $targetUser = User::factory()->create([
        'display_name' => 'Delete Me',
    ]);

    $response = $this->actingAs($admin)->post(route('studyhub.admin.users.delete', $targetUser));

    $response->assertRedirect(route('studyhub.admin.users'));
    $response->assertSessionHas('status', 'Delete Me was deleted successfully.');
    expect(User::find($targetUser->id))->toBeNull();
});

test('admins can not delete their own account from admin user management', function () {
    $admin = User::factory()->admin()->create([
        'display_name' => 'Self Admin',
    ]);

    $response = $this->actingAs($admin)->post(route('studyhub.admin.users.delete', $admin));

    $response->assertRedirect(route('studyhub.admin.users'));
    $response->assertSessionHas('status', 'You cannot delete your own admin account from this page.');
    expect(User::find($admin->id))->not->toBeNull();
});
