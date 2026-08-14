<?php

namespace App\Http\Controllers\Admin;

use App\Auth\FirebaseUser;
use App\Auth\FirebaseUserProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\FirebaseException;

class UserController extends Controller
{
    public function __construct(
        protected FirebaseAuth $auth,
        protected FirebaseUserProvider $users,
    ) {}

    public function index(Request $request): View
    {
        $all = [];
        foreach ($this->auth->listUsers(1000, 500) as $record) {
            $all[] = FirebaseUser::fromRecord($record);
        }

        usort($all, fn (FirebaseUser $a, FirebaseUser $b) => strcmp((string) $b->lastLoginAt, (string) $a->lastLoginAt));

        $search = trim((string) $request->query('q'));
        $filtered = $search === '' ? $all : array_values(array_filter($all, fn (FirebaseUser $u) => str_contains(
            mb_strtolower($u->email.' '.$u->displayName.' '.$u->uid),
            mb_strtolower($search),
        )));

        $since = Carbon::now()->subDays(30);

        $providers = [];
        foreach ($all as $u) {
            foreach ($u->providers ?: ['none'] as $p) {
                $providers[$p] = ($providers[$p] ?? 0) + 1;
            }
        }
        arsort($providers);

        return view('admin.users', [
            'users' => $filtered,
            'search' => $search,
            'stats' => [
                'total' => count($all),
                'verified' => count(array_filter($all, fn ($u) => $u->emailVerified)),
                'disabled' => count(array_filter($all, fn ($u) => $u->disabled)),
                'admins' => count(array_filter($all, fn ($u) => $u->isAdmin())),
                'new30' => count(array_filter($all, fn ($u) => $u->createdAt && Carbon::parse($u->createdAt)->gte($since))),
            ],
            'providers' => $providers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'displayName' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', Password::min(8)],
        ]);

        try {
            $this->auth->createUser($data + ['emailVerified' => false, 'disabled' => false]);
        } catch (EmailExists) {
            throw ValidationException::withMessages(['email' => 'That email is already registered.']);
        } catch (FirebaseException $e) {
            report($e);

            return back()->withInput($request->except('password'))->with('error', 'Could not create user.');
        }

        return back()->with('status', "User {$data['email']} created.");
    }

    public function update(Request $request, string $uid): RedirectResponse
    {
        $data = $request->validate([
            'displayName' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        return $this->act($uid, function (FirebaseUser $target) use ($uid, $data) {
            $props = ['displayName' => $data['displayName']];
            if ($data['email'] !== $target->email) {
                $props += ['email' => $data['email'], 'emailVerified' => false];
            }
            $this->auth->updateUser($uid, $props);

            return 'User updated.';
        });
    }

    public function toggleDisabled(Request $request, string $uid): RedirectResponse
    {
        $this->guardSelf($request, $uid);

        return $this->act($uid, function (FirebaseUser $target) use ($uid) {
            if ($target->disabled) {
                $this->auth->enableUser($uid);

                return 'User enabled.';
            }

            $this->auth->disableUser($uid);
            $this->auth->revokeRefreshTokens($uid);

            return 'User disabled.';
        });
    }

    public function toggleAdmin(Request $request, string $uid): RedirectResponse
    {
        $this->guardSelf($request, $uid);

        return $this->act($uid, function (FirebaseUser $target) use ($uid) {
            $claims = $target->customClaims;
            $claims['admin'] = ! $target->isAdmin();
            $this->auth->setCustomUserClaims($uid, $claims);

            return $claims['admin'] ? 'Admin role granted.' : 'Admin role revoked.';
        });
    }

    public function sendPasswordReset(string $uid): RedirectResponse
    {
        return $this->act($uid, function (FirebaseUser $target) {
            $this->auth->sendPasswordResetLink((string) $target->email);

            return "Password reset email sent to {$target->email}.";
        });
    }

    protected function guardSelf(Request $request, string $uid): void
    {
        abort_if($request->user()->uid === $uid, 422, 'You cannot change your own status or role.');
    }

    /**
     * @param  callable(FirebaseUser): string  $action
     */
    protected function act(string $uid, callable $action): RedirectResponse
    {
        try {
            $target = FirebaseUser::fromRecord($this->auth->getUser($uid));
            $message = $action($target);
        } catch (UserNotFound) {
            return back()->with('error', 'User not found.');
        } catch (EmailExists) {
            return back()->with('error', 'That email is already in use.');
        } catch (FirebaseException $e) {
            report($e);

            return back()->with('error', 'Firebase rejected the request.');
        }

        $this->users->forget($uid);

        return back()->with('status', $message);
    }
}
