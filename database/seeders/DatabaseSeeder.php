<?php

namespace Database\Seeders;

use App\Enums\NfcCardStatus;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Member;
use App\Models\NfcCard;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with the Temple Street demo data.
     */
    public function run(): void
    {
        $branches = [
            'north' => Branch::query()->updateOrCreate(
                ['name' => 'North Star Branch'],
                [
                    'location' => '123 Aurora Ave, Northville',
                    'manager' => 'Margaret Santos',
                    'phone' => '(02) 8123-4567',
                    'email' => 'north@membersdemo.local',
                ],
            ),
            'south' => Branch::query()->updateOrCreate(
                ['name' => 'South Bay Branch'],
                [
                    'location' => '45 Coral St, Southport',
                    'manager' => 'Robert dela Cruz',
                    'phone' => '(032) 234-5678',
                    'email' => 'south@membersdemo.local',
                ],
            ),
        ];

        $this->seedUser('admin', 'Main Administrator', 'admin@templestreet.local', 'admin123', UserRole::Main, null);
        $this->seedUser('north_admin', 'North Branch Admin', 'north_admin@templestreet.local', 'north123', UserRole::Local, $branches['north']);
        $this->seedUser('south_admin', 'South Branch Admin', 'south_admin@templestreet.local', 'south123', UserRole::Local, $branches['south']);

        $members = $this->memberData();

        foreach ($members as $row) {
            $branch = $branches[$row['branch']];

            Member::query()->updateOrCreate(
                ['profile_slug' => $row['slug']],
                [
                    'branch_id' => $branch->id,
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                    'address' => $row['address'],
                    'membership_type' => $row['membership_type'],
                    'status' => $row['status'],
                    'join_date' => $row['join_date'],
                    'approved' => $row['approved'],
                ],
            );
        }

        $cards = [
            'alice-reyes-b1m1' => ['NFC-A1B2C3D4', NfcCardStatus::Active, '2023-01-20', 'Primary card'],
            'carlos-mendoza-b1m2' => ['NFC-E5F6A7B8', NfcCardStatus::Active, '2023-02-25', 'Primary card'],
            'diana-v-b1m3' => ['NFC-C9D0E1F2', NfcCardStatus::Active, '2023-03-15', 'Primary card'],
            'eduardo-lim-b1m4' => ['NFC-G3H4I5J6', NfcCardStatus::Inactive, '2023-04-10', 'Member deactivated'],
            'fatima-c-b1m5' => ['NFC-K7L8M9N0', NfcCardStatus::Active, '2023-05-22', 'Primary card'],
            'laura-c-b2m1' => ['NFC-O1P2Q3R4', NfcCardStatus::Active, '2023-02-01', 'Primary card'],
            'marco-b-b2m2' => ['NFC-S5T6U7V8', NfcCardStatus::Active, '2023-02-18', 'Primary card'],
            'nina-dr-b2m3' => ['NFC-W9X0Y1Z2', NfcCardStatus::Active, '2023-03-25', 'Primary card'],
            'oscar-r-b2m4' => ['NFC-A3B4C5D6', NfcCardStatus::Lost, '2023-04-20', 'Reported lost'],
            'patricia-g-b2m5' => ['NFC-E7F8G9H0', NfcCardStatus::Active, '2023-05-10', 'Primary card'],
        ];

        foreach ($cards as $slug => [$uid, $status, $issued, $notes]) {
            $member = Member::query()->where('profile_slug', $slug)->first();

            if ($member === null) {
                continue;
            }

            NfcCard::query()->updateOrCreate(
                ['member_id' => $member->id],
                [
                    'card_uid' => $uid,
                    'profile_url' => route('profile.show', $member),
                    'card_status' => $status,
                    'issued_date' => $issued,
                    'notes' => $notes,
                ],
            );
        }
    }

    private function seedUser(
        string $username,
        string $name,
        string $email,
        string $password,
        UserRole $role,
        ?Branch $branch,
    ): void {
        $user = User::query()->updateOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'branch_id' => $branch?->id,
                'email_verified_at' => now(),
            ],
        );

        $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function memberData(): array
    {
        return [
            ['branch' => 'north', 'first_name' => 'Alice', 'last_name' => 'Reyes', 'email' => 'alice.reyes@mail.demo', 'phone' => '09171234001', 'address' => '12 Maple St, Northville', 'membership_type' => 'Premium', 'status' => 'Active', 'join_date' => '2023-01-15', 'slug' => 'alice-reyes-b1m1', 'approved' => true],
            ['branch' => 'north', 'first_name' => 'Carlos', 'last_name' => 'Mendoza', 'email' => 'carlos.m@mail.demo', 'phone' => '09181234002', 'address' => '34 Oak Ave, Northville', 'membership_type' => 'Regular', 'status' => 'Active', 'join_date' => '2023-02-20', 'slug' => 'carlos-mendoza-b1m2', 'approved' => true],
            ['branch' => 'north', 'first_name' => 'Diana', 'last_name' => 'Villanueva', 'email' => 'diana.v@mail.demo', 'phone' => '09191234003', 'address' => '56 Pine Rd, Northville', 'membership_type' => 'Student', 'status' => 'Active', 'join_date' => '2023-03-10', 'slug' => 'diana-v-b1m3', 'approved' => true],
            ['branch' => 'north', 'first_name' => 'Eduardo', 'last_name' => 'Lim', 'email' => 'eduardo.lim@mail.demo', 'phone' => '09201234004', 'address' => '78 Elm Blvd, Northville', 'membership_type' => 'Regular', 'status' => 'Inactive', 'join_date' => '2023-04-05', 'slug' => 'eduardo-lim-b1m4', 'approved' => true],
            ['branch' => 'north', 'first_name' => 'Fatima', 'last_name' => 'Castro', 'email' => 'fatima.c@mail.demo', 'phone' => '09211234005', 'address' => '90 Birch Ln, Northville', 'membership_type' => 'Senior', 'status' => 'Active', 'join_date' => '2023-05-18', 'slug' => 'fatima-c-b1m5', 'approved' => true],
            ['branch' => 'north', 'first_name' => 'George', 'last_name' => 'Navarro', 'email' => 'george.n@mail.demo', 'phone' => '09221234006', 'address' => '101 Cedar Way, Northville', 'membership_type' => 'Premium', 'status' => 'Active', 'join_date' => '2023-06-22', 'slug' => 'george-n-b1m6', 'approved' => true],
            ['branch' => 'north', 'first_name' => 'Hannah', 'last_name' => 'Torres', 'email' => 'hannah.t@mail.demo', 'phone' => '09231234007', 'address' => '202 Walnut Dr, Northville', 'membership_type' => 'Student', 'status' => 'Pending', 'join_date' => '2023-07-30', 'slug' => 'hannah-t-b1m7', 'approved' => false],
            ['branch' => 'north', 'first_name' => 'Ivan', 'last_name' => 'Flores', 'email' => 'ivan.f@mail.demo', 'phone' => '09241234008', 'address' => '303 Ash St, Northville', 'membership_type' => 'Regular', 'status' => 'Active', 'join_date' => '2023-08-14', 'slug' => 'ivan-f-b1m8', 'approved' => true],
            ['branch' => 'north', 'first_name' => 'Julia', 'last_name' => 'Santos', 'email' => 'julia.s@mail.demo', 'phone' => '09251234009', 'address' => '404 Poplar Ave, Northville', 'membership_type' => 'Premium', 'status' => 'Active', 'join_date' => '2023-09-09', 'slug' => 'julia-s-b1m9', 'approved' => true],
            ['branch' => 'north', 'first_name' => 'Kevin', 'last_name' => 'Aquino', 'email' => 'kevin.a@mail.demo', 'phone' => '09261234010', 'address' => '505 Spruce Blvd, Northville', 'membership_type' => 'Regular', 'status' => 'Active', 'join_date' => '2023-10-01', 'slug' => 'kevin-a-b1m10', 'approved' => true],
            ['branch' => 'south', 'first_name' => 'Laura', 'last_name' => 'Cruz', 'email' => 'laura.c@mail.demo', 'phone' => '09271234011', 'address' => '11 Seaside Rd, Southport', 'membership_type' => 'Regular', 'status' => 'Active', 'join_date' => '2023-01-28', 'slug' => 'laura-c-b2m1', 'approved' => true],
            ['branch' => 'south', 'first_name' => 'Marco', 'last_name' => 'Bautista', 'email' => 'marco.b@mail.demo', 'phone' => '09281234012', 'address' => '22 Harbor St, Southport', 'membership_type' => 'Senior', 'status' => 'Active', 'join_date' => '2023-02-14', 'slug' => 'marco-b-b2m2', 'approved' => true],
            ['branch' => 'south', 'first_name' => 'Nina', 'last_name' => 'Dela Rosa', 'email' => 'nina.dr@mail.demo', 'phone' => '09291234013', 'address' => '33 Bay Ave, Southport', 'membership_type' => 'Premium', 'status' => 'Active', 'join_date' => '2023-03-22', 'slug' => 'nina-dr-b2m3', 'approved' => true],
            ['branch' => 'south', 'first_name' => 'Oscar', 'last_name' => 'Ramos', 'email' => 'oscar.r@mail.demo', 'phone' => '09301234014', 'address' => '44 Wave Blvd, Southport', 'membership_type' => 'Regular', 'status' => 'Inactive', 'join_date' => '2023-04-18', 'slug' => 'oscar-r-b2m4', 'approved' => true],
            ['branch' => 'south', 'first_name' => 'Patricia', 'last_name' => 'Gomez', 'email' => 'patricia.g@mail.demo', 'phone' => '09311234015', 'address' => '55 Tide Ln, Southport', 'membership_type' => 'Student', 'status' => 'Active', 'join_date' => '2023-05-05', 'slug' => 'patricia-g-b2m5', 'approved' => true],
            ['branch' => 'south', 'first_name' => 'Quincy', 'last_name' => 'Macaraeg', 'email' => 'quincy.m@mail.demo', 'phone' => '09321234016', 'address' => '66 Coral Ct, Southport', 'membership_type' => 'Regular', 'status' => 'Active', 'join_date' => '2023-06-11', 'slug' => 'quincy-m-b2m6', 'approved' => true],
            ['branch' => 'south', 'first_name' => 'Rachel', 'last_name' => 'Fernandez', 'email' => 'rachel.f@mail.demo', 'phone' => '09331234017', 'address' => '77 Shell Way, Southport', 'membership_type' => 'Premium', 'status' => 'Pending', 'join_date' => '2023-07-23', 'slug' => 'rachel-f-b2m7', 'approved' => false],
            ['branch' => 'south', 'first_name' => 'Samuel', 'last_name' => 'Dizon', 'email' => 'samuel.d@mail.demo', 'phone' => '09341234018', 'address' => '88 Reef Rd, Southport', 'membership_type' => 'Regular', 'status' => 'Active', 'join_date' => '2023-08-30', 'slug' => 'samuel-d-b2m8', 'approved' => true],
            ['branch' => 'south', 'first_name' => 'Teresa', 'last_name' => 'Aguilar', 'email' => 'teresa.a@mail.demo', 'phone' => '09351234019', 'address' => '99 Lagoon Dr, Southport', 'membership_type' => 'Senior', 'status' => 'Active', 'join_date' => '2023-09-15', 'slug' => 'teresa-a-b2m9', 'approved' => true],
            ['branch' => 'south', 'first_name' => 'Ulysses', 'last_name' => 'Pascual', 'email' => 'ulysses.p@mail.demo', 'phone' => '09361234020', 'address' => '100 Dune Blvd, Southport', 'membership_type' => 'Student', 'status' => 'Active', 'join_date' => '2023-10-20', 'slug' => 'ulysses-p-b2m10', 'approved' => true],
        ];
    }
}
