<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('authenticated users are sent through the studyhub dashboard router', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('studyhub.dashboard'));
});

test('studyhub dashboard routes students to the student dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('studyhub.dashboard'));

    $response->assertRedirect(route('studyhub.student.dashboard'));
});
