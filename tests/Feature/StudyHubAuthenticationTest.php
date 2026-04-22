<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('studyhub login screen can be rendered', function () {
    $response = $this->get(route('studyhub.login'));

    $response->assertOk();
});

test('student accounts are redirected to the student dashboard after login', function () {
    $user = User::factory()->create([
        'role' => 'student',
    ]);

    $response = $this->post(route('studyhub.authenticate'), [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'student',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('studyhub.student.dashboard', absolute: false));
});

test('admin accounts are redirected to the admin dashboard after login', function () {
    $user = User::factory()->admin()->create();

    $response = $this->post(route('studyhub.authenticate'), [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'admin',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('studyhub.admin.dashboard', absolute: false));
});

test('users can not log into the wrong dashboard role', function () {
    $user = User::factory()->create([
        'role' => 'student',
    ]);

    $response = $this->from(route('studyhub.login'))->post(route('studyhub.authenticate'), [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'admin',
    ]);

    $response->assertRedirect(route('studyhub.login'));
    $response->assertSessionHasErrors('role');
    $this->assertGuest();
});

test('students are redirected away from admin routes', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $response = $this->actingAs($student)->get(route('studyhub.admin.dashboard'));

    $response->assertRedirect(route('studyhub.student.dashboard'));
});
