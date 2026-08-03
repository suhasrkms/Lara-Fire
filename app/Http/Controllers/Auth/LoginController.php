<?php

namespace App\Http\Controllers\Auth;

use App\Auth\FirebaseUserProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Throwable;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login', [
            'socialProviders' => config('larafire.social_providers', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    /**
     * Social sign-in: the browser signs in with the Firebase JS SDK and posts the ID token here.
     */
    public function firebase(Request $request, FirebaseAuth $auth, FirebaseUserProvider $users): RedirectResponse
    {
        $request->validate(['id_token' => ['required', 'string']]);

        try {
            $token = $auth->verifyIdToken($request->string('id_token')->toString());
        } catch (Throwable) {
            return redirect()->route('login')->with('error', 'Social sign-in failed. Please try again.');
        }

        $firebaseClaim = (array) $token->claims()->get('firebase', []);
        if (($firebaseClaim['sign_in_provider'] ?? null) === 'anonymous') {
            return redirect()->route('login')->with('error', 'Anonymous accounts cannot sign in here.');
        }

        $authTime = (int) $token->claims()->get('auth_time', 0);
        if ($authTime < now()->subMinutes(5)->getTimestamp()) {
            return redirect()->route('login')->with('error', 'Sign-in expired. Please try again.');
        }

        $user = $users->fresh((string) $token->claims()->get('sub'));

        if (! $user) {
            return redirect()->route('login')->with('error', 'This account is disabled.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome');
    }
}
