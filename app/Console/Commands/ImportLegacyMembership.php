<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('membership:import-legacy {--source=membership_demo : Source MySQL database name} {--fresh : Delete existing membership data in the target database first}')]
#[Description('Import branches, admins, members and NFC cards from the legacy membership_demo MySQL database')]
class ImportLegacyMembership extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $target = (string) config('database.connections.mysql.database');
        $source = (string) $this->option('source');

        if ($target === $source) {
            $this->error('The target database and source database must be different.');

            return self::FAILURE;
        }

        $sourceConfig = config('database.connections.mysql');
        $sourceConfig['database'] = $source;
        config(['database.connections.legacy_membership' => $sourceConfig]);

        try {
            DB::connection('legacy_membership')->getPdo();
        } catch (Throwable $exception) {
            $this->error("Could not connect to [{$source}]: {$exception->getMessage()}");

            return self::FAILURE;
        }

        $branches = DB::connection('legacy_membership')->table('branches')->orderBy('id')->get();
        $admins = DB::connection('legacy_membership')->table('admins')->orderBy('id')->get();
        $members = DB::connection('legacy_membership')->table('members')->orderBy('id')->get();
        $cards = DB::connection('legacy_membership')->table('nfc_cards')->orderBy('id')->get();

        if ($branches->isEmpty() && $admins->isEmpty() && $members->isEmpty()) {
            $this->error("No legacy membership data found in [{$source}].");

            return self::FAILURE;
        }

        DB::transaction(function () use ($branches, $admins, $members, $cards): void {
            if ($this->option('fresh')) {
                DB::table('nfc_cards')->delete();
                DB::table('members')->delete();
                DB::table('users')->delete();
                DB::table('branches')->delete();
            }

            $now = now();

            foreach ($branches as $branch) {
                DB::table('branches')->updateOrInsert(
                    ['id' => $branch->id],
                    [
                        'name' => $branch->name,
                        'location' => $branch->location,
                        'manager' => $branch->manager,
                        'phone' => $branch->phone ?? '',
                        'email' => $branch->email ?? '',
                        'created_at' => $branch->created_at ? Carbon::parse($branch->created_at) : $now,
                        'updated_at' => $now,
                    ],
                );
            }

            foreach ($admins as $admin) {
                DB::table('users')->updateOrInsert(
                    ['username' => $admin->username],
                    [
                        'name' => $admin->full_name ?: $admin->username,
                        'email' => $admin->username.'@templestreet.local',
                        'password' => $admin->password,
                        'role' => $admin->role,
                        'branch_id' => $admin->branch_id,
                        'email_verified_at' => $now,
                        'remember_token' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            }

            $slugs = [];

            foreach ($members as $member) {
                $slugs[$member->id] = $member->profile_slug;

                DB::table('members')->updateOrInsert(
                    ['id' => $member->id],
                    [
                        'branch_id' => $member->branch_id,
                        'first_name' => $member->first_name,
                        'last_name' => $member->last_name,
                        'email' => $member->email,
                        'phone' => $member->phone,
                        'address' => $member->address,
                        'membership_type' => $member->membership_type,
                        'status' => $member->status,
                        'join_date' => $member->join_date,
                        'profile_slug' => $member->profile_slug,
                        'approved' => (bool) $member->approved,
                        'created_at' => $member->created_at ? Carbon::parse($member->created_at) : $now,
                        'updated_at' => $now,
                    ],
                );
            }

            $baseUrl = rtrim((string) config('app.url'), '/');

            foreach ($cards as $card) {
                DB::table('nfc_cards')->updateOrInsert(
                    ['id' => $card->id],
                    [
                        'member_id' => $card->member_id,
                        'card_uid' => $card->card_uid,
                        'profile_url' => isset($slugs[$card->member_id])
                            ? $baseUrl.'/p/'.$slugs[$card->member_id]
                            : $card->profile_url,
                        'card_status' => $card->card_status,
                        'issued_date' => $card->issued_date,
                        'notes' => $card->notes,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            }
        });

        DB::purge('legacy_membership');

        $this->info(sprintf(
            'Imported %d branches, %d admins, %d members and %d NFC cards from [%s] into [%s].',
            $branches->count(),
            $admins->count(),
            $members->count(),
            $cards->count(),
            $source,
            $target,
        ));

        return self::SUCCESS;
    }
}
