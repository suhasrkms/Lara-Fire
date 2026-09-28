<?php

namespace App\Http\Controllers\Auth;

use App\Auth\FirebaseUserProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\FirebaseException;

class EmailVerificationController extends Controller
{
    /**
     * Firebase verifies via its own link, so re-check the live record here.
     */
    public function notice(Request $request, FirebaseUserProvider $users): View|RedirectResponse
    {
        $user = $users->fresh($request->user()->getAuthIdentifier());

        if (! $user || $user->emailVerified || ! $user->hasPasswordProvider()) {
            return redirect()->route('home');
        }

        return view('auth.verify-email', ['user' => $user]);
    }

    public function send(Request $request, FirebaseAuth $auth): RedirectResponse
    {
        try {
            $auth->sendEmailVerificationLink((string) $request->user()->email);
        } catch (FirebaseException $e) {
            report($e);

            return back()->with('error', 'Could not send the verification email. Try again shortly.');
        }

        return back()->with('status', 'A new verification link has been sent to your inbox.');
    }
}
