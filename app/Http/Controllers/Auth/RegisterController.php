<?php

namespace App\Http\Controllers\Auth;

use App\Auth\FirebaseUserProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\FirebaseException;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'socialProviders' => config('larafire.social_providers', []),
        ]);
    }

    public function store(Request $request, FirebaseAuth $auth, FirebaseUserProvider $users): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        try {
            $record = $auth->createUser([
                'email' => $data['email'],
                'password' => $data['password'],
                'displayName' => $data['name'],
                'emailVerified' => false,
                'disabled' => false,
            ]);
        } catch (EmailExists) {
            throw ValidationException::withMessages(['email' => 'An account with this email already exists.']);
        } catch (FirebaseException $e) {
            report($e);

            return back()->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Could not create your account. Please try again.');
        }

        try {
            $auth->sendEmailVerificationLink($data['email']);
        } catch (FirebaseException $e) {
            report($e);
        }

        $user = $users->fresh($record->uid);

        if (! $user) {
            return redirect()->route('login')->with('status', 'Account created. Please sign in.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')
            ->with('status', 'Account created. We sent a verification link to your inbox.');
    }
}
