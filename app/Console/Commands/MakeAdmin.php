<?php

namespace App\Console\Commands;

use App\Auth\FirebaseUserProvider;
use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\UserNotFound;

class MakeAdmin extends Command
{
    protected $signature = 'larafire:make-admin
                            {email : Email of an existing Firebase user}
                            {--revoke : Remove the admin role instead}';

    protected $description = 'Grant (or revoke) the admin custom claim for a Firebase user';

    public function handle(FirebaseAuth $auth, FirebaseUserProvider $users): int
    {
        $email = (string) $this->argument('email');

        try {
            $user = $auth->getUserByEmail($email);
        } catch (UserNotFound) {
            $this->components->error("No Firebase user with email [{$email}]. Register first.");

            return self::FAILURE;
        }

        $claims = $user->customClaims;
        $claims['admin'] = ! $this->option('revoke');
        $auth->setCustomUserClaims($user->uid, $claims);
        $users->forget($user->uid);

        $this->components->info($claims['admin']
            ? "{$email} is now an admin. Sign out and back in if you're already logged in."
            : "Admin role removed from {$email}.");

        return self::SUCCESS;
    }
}
