<?php

namespace Database\Factories;

use App\Enums\NfcCardStatus;
use App\Models\Member;
use App\Models\NfcCard;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NfcCard>
 */
class NfcCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'card_uid' => 'NFC-'.Str::upper(Str::random(8)),
            'profile_url' => fake()->url(),
            'card_status' => NfcCardStatus::Active,
            'issued_date' => fake()->dateTimeBetween('-2 years')->format('Y-m-d'),
            'notes' => fake()->optional()->sentence(3),
        ];
    }
}
