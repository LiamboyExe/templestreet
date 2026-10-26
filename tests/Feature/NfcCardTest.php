<?php

use App\Models\Branch;
use App\Models\Member;
use App\Models\NfcCard;
use App\Models\User;

beforeEach(function () {
    $this->north = Branch::factory()->create();
    $this->south = Branch::factory()->create();
    $this->mainAdmin = User::factory()->mainAdmin()->create();
    $this->northAdmin = User::factory()->branchAdmin($this->north)->create();
});

test('a card can be assigned to a member', function () {
    $member = Member::factory()->for($this->north)->create();

    $this->actingAs($this->northAdmin)
        ->post(route('nfc-cards.store'), [
            'member_id' => $member->id,
            'card_uid' => 'NFC-ABC12345',
            'card_status' => 'Active',
            'issued_date' => '2026-01-15',
            'notes' => 'Primary card',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('nfc_cards', [
        'member_id' => $member->id,
        'card_uid' => 'NFC-ABC12345',
        'card_status' => 'Active',
    ]);
});

test('branch admin cannot assign a card to a member from another branch', function () {
    $member = Member::factory()->for($this->south)->create();

    $this->actingAs($this->northAdmin)
        ->post(route('nfc-cards.store'), [
            'member_id' => $member->id,
            'card_status' => 'Active',
            'issued_date' => '2026-01-15',
        ])
        ->assertNotFound();

    $this->assertDatabaseMissing('nfc_cards', ['member_id' => $member->id]);
});

test('a card status can be updated', function () {
    $member = Member::factory()->for($this->north)->create();
    $card = NfcCard::factory()->for($member)->create(['card_status' => 'Active']);

    $this->actingAs($this->northAdmin)
        ->patch(route('nfc-cards.update', $card), [
            'card_uid' => $card->card_uid,
            'profile_url' => $card->profile_url,
            'card_status' => 'Lost',
            'issued_date' => '2026-01-15',
            'notes' => 'Reported lost',
        ])
        ->assertRedirect();

    expect($card->fresh()->card_status->value)->toBe('Lost');
});

test('branch admin only sees cards from their own branch', function () {
    $northMember = Member::factory()->for($this->north)->create();
    $southMember = Member::factory()->for($this->south)->create();
    NfcCard::factory()->for($northMember)->create();
    NfcCard::factory()->for($southMember)->create();

    $this->actingAs($this->northAdmin)
        ->get(route('nfc-cards.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('nfc/Index')
            ->has('cards', 1));
});
