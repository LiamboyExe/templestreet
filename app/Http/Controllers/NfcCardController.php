<?php

namespace App\Http\Controllers;

use App\Enums\NfcCardStatus;
use App\Models\Member;
use App\Models\NfcCard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class NfcCardController extends Controller
{
    /**
     * List NFC cards and members that do not have one yet.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $cards = NfcCard::query()
            ->whereHas('member', fn (Builder $query) => $query->visibleTo($user))
            ->with(['member.branch'])
            ->latest('issued_date')
            ->get()
            ->each(function (NfcCard $card): void {
                $card->setAttribute('profile_url', route('profile.show', $card->member));
            });

        $membersWithoutCards = Member::query()
            ->visibleTo($user)
            ->doesntHave('nfcCard')
            ->with('branch')
            ->orderBy('first_name')
            ->get()
            ->each(function (Member $member): void {
                $member->setAttribute('profile_url', route('profile.show', $member));
            });

        return Inertia::render('nfc/Index', [
            'cards' => $cards,
            'membersWithoutCards' => $membersWithoutCards,
            'statuses' => NfcCardStatus::values(),
        ]);
    }

    /**
     * Assign a card to a member, or update it when the member already has one.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', NfcCard::class);

        $validated = $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'card_uid' => ['nullable', 'string', 'max:40'],
            'profile_url' => ['nullable', 'string', 'max:255'],
            'card_status' => ['required', Rule::enum(NfcCardStatus::class)],
            'issued_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $member = Member::query()
            ->visibleTo($request->user())
            ->findOrFail($validated['member_id']);

        $card = NfcCard::query()->firstOrNew(['member_id' => $member->id]);

        if ($card->exists) {
            Gate::authorize('update', $card);
        }

        $card->fill([
            'card_uid' => filled($validated['card_uid'] ?? null)
                ? $validated['card_uid']
                : 'NFC-'.Str::upper(Str::random(8)),
            'profile_url' => filled($validated['profile_url'] ?? null)
                ? $validated['profile_url']
                : route('profile.show', $member),
            'card_status' => $validated['card_status'],
            'issued_date' => $validated['issued_date'],
            'notes' => $validated['notes'] ?? null,
        ])->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $card->wasRecentlyCreated
                ? __('Card assigned to :name.', ['name' => $member->full_name])
                : __('Card updated for :name.', ['name' => $member->full_name]),
        ]);

        return back();
    }

    /**
     * Update the given card.
     */
    public function update(Request $request, NfcCard $nfcCard): RedirectResponse
    {
        Gate::authorize('update', $nfcCard);

        $validated = $request->validate([
            'card_uid' => ['required', 'string', 'max:40'],
            'profile_url' => ['required', 'string', 'max:255'],
            'card_status' => ['required', Rule::enum(NfcCardStatus::class)],
            'issued_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $nfcCard->update($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Card updated.'),
        ]);

        return back();
    }

    /**
     * Remove the given card.
     */
    public function destroy(NfcCard $nfcCard): RedirectResponse
    {
        Gate::authorize('delete', $nfcCard);

        $nfcCard->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Card removed.'),
        ]);

        return back();
    }
}
