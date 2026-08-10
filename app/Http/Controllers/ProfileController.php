<?php

namespace App\Http\Controllers;

use App\Auth\FirebaseUserProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\FirebaseException;

/**
 * Every action targets the signed-in user only — never a uid from the URL.
 */
class ProfileController extends Controller
{
    public function __construct(
        protected FirebaseAuth $auth,
        protected FirebaseUserProvider $users,
    ) {}

    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'displayName' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $properties = ['displayName' => $data['displayName']];

        if ($data['email'] !== $user->email) {
            $properties['email'] = $data['email'];
            $properties['emailVerified'] = false;
        }

        try {
            $this->auth->updateUser($user->uid, $properties);

            if (isset($properties['email'])) {
                $this->auth->sendEmailVerificationLink($data['email']);
            }
        } catch (EmailExists) {
            throw ValidationException::withMessages(['email' => 'That email is already in use.']);
        } catch (FirebaseException $e) {
            report($e);

            return back()->with('error', 'Profile update failed.');
        }

        $this->users->forget($user->uid);

        return back()->with('status', isset($properties['email'])
            ? 'Profile updated. Please verify your new email address.'
            : 'Profile updated.');
    }

    public function password(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = ['password' => ['required', 'confirmed', Password::min(8)]];
        if ($user->hasPasswordProvider()) {
            $rules['current_password'] = ['required', 'string'];
        }

        $data = $request->validateWithBag('password', $rules);

        if ($user->hasPasswordProvider()) {
            try {
                $this->auth->signInWithEmailAndPassword((string) $user->email, $data['current_password']);
            } catch (FirebaseException) {
                throw ValidationException::withMessages(['current_password' => 'Current password is incorrect.'])
                    ->errorBag('password');
            }
        }

        try {
            $this->auth->changeUserPassword($user->uid, $data['password']);
        } catch (FirebaseException $e) {
            report($e);

            return back()->with('error', 'Password update failed.');
        }

        $this->users->forget($user->uid);

        return back()->with('status', 'Password updated.');
    }

    /**
     * Disables (not deletes) the signed-in user's own account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $uid = $request->user()->uid;

        try {
            $this->auth->disableUser($uid);
            $this->auth->revokeRefreshTokens($uid);
        } catch (FirebaseException $e) {
            report($e);

            return back()->with('error', 'Could not disable your account. Please try again.');
        }

        $this->users->forget($uid);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome')->with('status', 'Your account has been disabled.');
    }
}
