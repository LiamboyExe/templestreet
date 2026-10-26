<?php

namespace App\Http\Controllers;

use App\Enums\MemberStatus;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    /**
     * List branches with member statistics.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $branches = Branch::query()
            ->when(! $user->isMainAdmin(), fn (Builder $query) => $query->whereKey($user->branch_id))
            ->withCount([
                'members',
                'members as active_members_count' => fn (Builder $query) => $query->where('status', MemberStatus::Active),
                'members as inactive_members_count' => fn (Builder $query) => $query->where('status', MemberStatus::Inactive),
                'members as pending_members_count' => fn (Builder $query) => $query->where('status', MemberStatus::Pending),
                'admins as local_admins_count' => fn (Builder $query) => $query->where('role', UserRole::Local),
            ])
            ->with(['admins' => fn ($query) => $query->where('role', UserRole::Local)->select('id', 'name', 'username', 'branch_id')])
            ->orderBy('name')
            ->get();

        return Inertia::render('branches/Index', [
            'branches' => $branches,
            'isMainAdmin' => $user->isMainAdmin(),
        ]);
    }

    /**
     * Store a new branch together with its branch admin account.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Branch::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:150'],
            'manager' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'email', 'max:100'],
            'admin_username' => ['required', 'string', 'max:60', 'unique:users,username'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $branch = DB::transaction(function () use ($validated): Branch {
            $branch = Branch::create([
                'name' => $validated['name'],
                'location' => $validated['location'],
                'manager' => $validated['manager'],
                'phone' => $validated['phone'] ?? '',
                'email' => $validated['email'] ?? '',
            ]);

            User::create([
                'name' => $validated['manager'],
                'username' => $validated['admin_username'],
                'email' => $validated['admin_username'].'@templestreet.local',
                'password' => $validated['admin_password'],
                'role' => UserRole::Local,
                'branch_id' => $branch->id,
                'email_verified_at' => now(),
            ]);

            return $branch;
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Branch ":name" was created with its admin account.', ['name' => $branch->name]),
        ]);

        return back();
    }

    /**
     * Update the given branch and optionally reset its admin password.
     */
    public function update(Request $request, Branch $branch): RedirectResponse
    {
        Gate::authorize('update', $branch);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:150'],
            'manager' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'email', 'max:100'],
            'admin_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $branch->update([
            'name' => $validated['name'],
            'location' => $validated['location'],
            'manager' => $validated['manager'],
            'phone' => $validated['phone'] ?? '',
            'email' => $validated['email'] ?? '',
        ]);

        if (filled($validated['admin_password'] ?? null)) {
            $admin = $branch->admins()->where('role', UserRole::Local)->first();

            if ($admin === null) {
                throw ValidationException::withMessages([
                    'admin_password' => __('This branch has no admin account to reset.'),
                ]);
            }

            $admin->update(['password' => $validated['admin_password']]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Branch ":name" was updated.', ['name' => $branch->name]),
        ]);

        return back();
    }

    /**
     * Delete the given branch with its members, cards and admin accounts.
     */
    public function destroy(Branch $branch): RedirectResponse
    {
        Gate::authorize('delete', $branch);

        DB::transaction(function () use ($branch): void {
            $branch->admins()->where('role', UserRole::Local)->delete();
            $branch->delete();
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Branch deleted.'),
        ]);

        return back();
    }
}
