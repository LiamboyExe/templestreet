<?php

use App\Models\Member;

test('an approved member profile is publicly visible', function () {
    $member = Member::factory()->create([
        'first_name' => 'Alice',
        'approved' => true,
    ]);

    $this->get(route('profile.show', $member))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Profile/Show')
            ->where('member.first_name', 'Alice'));
});

test('an unapproved member profile is not visible', function () {
    $member = Member::factory()->unapproved()->create();

    $this->get(route('profile.show', $member))->assertNotFound();
});

test('an unknown profile slug is not found', function () {
    $this->get(route('profile.show', 'does-not-exist'))->assertNotFound();
});
