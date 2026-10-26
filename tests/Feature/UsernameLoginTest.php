<?php

use App\Models\User;

test('users can authenticate with their username', function () {
    $user = User::factory()->create([
        'username' => 'operator-01',
    ]);

    $this->post(route('login.store'), [
        'email' => 'operator-01',
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('users can still authenticate with their email address', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});
