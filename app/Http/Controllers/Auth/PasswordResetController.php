<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\FirebaseException;

class PasswordResetController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request, FirebaseAuth $auth): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        try {
            $auth->sendPasswordResetLink($data['email']);
        } catch (FirebaseException) {
            // Don't reveal whether the address exists.
        }

        return back()->with('status', 'If that email is registered, a reset link is on its way.');
    }
}
