<?php

namespace App\Http\Controllers;

use App\Enums\MembershipType;
use App\Enums\MemberStatus;
use App\Models\Branch;
use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    /**
     * List members with search and branch/status filters.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $branch = $request->integer('branch');

        $members = Member::query()
            ->with('branch')
            ->visibleTo($user)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $like = '%'.$search.'%';
                    $query->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->when(in_array($status, MemberStatus::values(), true), fn (Builder $query) => $query->where('status', $status))
            ->when($user->isMainAdmin() && $branch > 0, fn (Builder $query) => $query->where('branch_id', $branch))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('members/Index', [
            'members' => $members,
            'branches' => $user->isMainAdmin()
                ? Branch::query()->orderBy('name')->get(['id', 'name'])
                : Branch::query()->whereKey($user->branch_id)->get(['id', 'name']),
            'filters' => [
                'q' => $search,
                'status' => $status,
                'branch' => $branch,
            ],
            'isMainAdmin' => $user->isMainAdmin(),
            'membershipTypes' => MembershipType::values(),
            'statuses' => MemberStatus::values(),
        ]);
    }

    /**
     * Store a new member.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Member::class);

        $user = $request->user();

        if ($user->isBranchAdmin()) {
            $request->merge(['branch_id' => $user->branch_id]);
        }

        $validated = $request->validate($this->rules());

        $member = Member::create([
            ...$validated,
            'profile_slug' => $this->uniqueProfileSlug($validated['first_name'], $validated['last_name']),
            'approved' => $request->boolean('approved'),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __(':name was added.', ['name' => $member->full_name]),
        ]);

        return back();
    }

    /**
     * Update the given member.
     */
    public function update(Request $request, Member $member): RedirectResponse
    {
        Gate::authorize('update', $member);

        if ($request->user()->isBranchAdmin()) {
            $request->merge(['branch_id' => $request->user()->branch_id]);
        }

        $validated = $request->validate($this->rules());

        $member->update([
            ...$validated,
            'approved' => $request->boolean('approved'),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __(':name was updated.', ['name' => $member->full_name]),
        ]);

        return back();
    }

    /**
     * Delete the given member.
     */
    public function destroy(Member $member): RedirectResponse
    {
        Gate::authorize('delete', $member);

        $member->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Member deleted.'),
        ]);

        return back();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'string', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'membership_type' => ['required', Rule::enum(MembershipType::class)],
            'status' => ['required', Rule::enum(MemberStatus::class)],
            'join_date' => ['required', 'date'],
        ];
    }

    private function uniqueProfileSlug(string $firstName, string $lastName): string
    {
        $base = Str::slug($firstName.' '.$lastName) ?: 'member';

        do {
            $slug = $base.'-'.Str::lower(Str::random(6));
        } while (Member::query()->where('profile_slug', $slug)->exists());

        return $slug;
    }
}
