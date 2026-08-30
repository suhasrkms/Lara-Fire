<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PushNotifications;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Kreait\Firebase\Exception\FirebaseException;

class NotificationController extends Controller
{
    public function create(): View
    {
        return view('admin.notifications', [
            'configured' => filled(config('larafire.vapid_key')),
        ]);
    }

    public function store(Request $request, PushNotifications $push): RedirectResponse
    {
        $data = $request->validate([
            'audience' => ['required', 'in:all,user'],
            'uid' => ['required_if:audience,user', 'nullable', 'string', 'max:128'],
            'title' => ['required', 'string', 'max:100'],
            'body' => ['required', 'string', 'max:500'],
            'link' => ['nullable', 'url:https', 'max:255'],
        ]);

        try {
            $data['audience'] === 'all'
                ? $push->toEveryone($data['title'], $data['body'], $data['link'] ?? null)
                : $push->toUser($data['uid'], $data['title'], $data['body'], $data['link'] ?? null);
        } catch (FirebaseException $e) {
            report($e);

            return back()->withInput()->with('error', 'FCM rejected the message: '.$e->getMessage());
        }

        return back()->with('status', 'Notification sent.');
    }
}
