<?php

namespace App\Console\Commands;

use App\Services\NoteRepository;
use Illuminate\Console\Command;

class About extends Command
{
    protected $signature = 'larafire:about';

    protected $description = 'Show Lara Fire setup status and next steps';

    public function handle(): int
    {
        $credentials = (string) config('firebase.projects.app.credentials');
        $path = $credentials && ! str_starts_with($credentials, '{') && ! str_starts_with($credentials, '/') && ! str_contains($credentials, ':\\')
            ? base_path($credentials)
            : $credentials;

        $this->newLine();
        $this->line('  <fg=yellow;options=bold>🔥 Lara Fire</> — Laravel '.app()->version().' + Firebase');
        $this->newLine();

        $this->components->twoColumnDetail('Service account', $credentials && (str_starts_with($credentials, '{') || is_file($path)) ? '<fg=green>found</>' : '<fg=red>missing</> ('.($credentials ?: 'FIREBASE_CREDENTIALS').')');
        $this->components->twoColumnDetail('Web config (social login / FCM)', filled(config('larafire.web.apiKey')) ? '<fg=green>set</>' : '<fg=yellow>not set</>');
        $this->components->twoColumnDetail('FCM VAPID key', filled(config('larafire.vapid_key')) ? '<fg=green>set</>' : '<fg=yellow>not set</>');
        $this->components->twoColumnDetail('Firestore (REST)', NoteRepository::available() ? '<fg=green>ready</>' : '<fg=yellow>needs service account</>');

        $this->newLine();
        $this->line('  Next steps:');
        $this->line('   1. Put your service account JSON at <fg=cyan>storage/app/firebase/service-account.json</>');
        $this->line('   2. Fill the FIREBASE_WEB_* values in <fg=cyan>.env</>');
        $this->line('   3. <fg=cyan>npm install && npm run build</> then <fg=cyan>php artisan serve</>');
        $this->line('   4. Register, then <fg=cyan>php artisan larafire:make-admin you@example.com</>');
        $this->newLine();
        $this->line('  Docs & updates: <fg=cyan>'.config('larafire.repository_url').'</>  ·  Videos: <fg=cyan>'.config('larafire.youtube_url').'</>');
        $this->newLine();

        return self::SUCCESS;
    }
}
