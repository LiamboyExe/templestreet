<?php

namespace App\Http\Controllers;

use App\Enums\MemberStatus;
use App\Enums\NfcCardStatus;
use App\Models\Branch;
use App\Models\Member;
use App\Models\NfcCard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the membership summary dashboard.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $members = Member::query()->visibleTo($user);

        $totalMembers = (clone $members)->count();
        $activeMembers = (clone $members)->where('status', MemberStatus::Active)->count();

        $cards = NfcCard::query()->whereHas('member', fn (Builder $query) => $query->visibleTo($user));
        $totalCards = (clone $cards)->count();
        $activeCards = (clone $cards)->where('card_status', NfcCardStatus::Active)->count();

        $recentMembers = (clone $members)
            ->with('branch')
            ->latest('join_date')
            ->limit(5)
            ->get();

        $branchStats = $user->isMainAdmin()
            ? Branch::query()
                ->withCount([
                    'members',
                    'members as active_members_count' => fn (Builder $query) => $query->where('status', MemberStatus::Active),
                ])
                ->orderBy('name')
                ->get()
            : collect();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalMembers' => $totalMembers,
                'activeMembers' => $activeMembers,
                'totalBranches' => $user->isMainAdmin() ? Branch::count() : ($user->branch_id ? 1 : 0),
                'totalCards' => $totalCards,
                'activeCards' => $activeCards,
            ],
            'recentMembers' => $recentMembers,
            'branchStats' => $branchStats,
            'isMainAdmin' => $user->isMainAdmin(),
        ]);
    }
}
