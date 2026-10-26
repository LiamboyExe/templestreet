<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    /**
     * Show the signed-in administrator's account details.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Account', [
            'user' => $request->user()->loadMissing('branch'),
        ]);
    }
}
