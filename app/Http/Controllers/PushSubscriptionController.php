<?php

namespace App\Http\Controllers;

use App\Services\PushNotifications;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kreait\Firebase\Exception\FirebaseException;

class PushSubscriptionController extends Controller
{
    public function store(Request $request, PushNotifications $push): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:4096']]);

        try {
            $push->subscribe($data['token'], $request->user()->getAuthIdentifier());
        } catch (FirebaseException $e) {
            report($e);

            return response()->json(['message' => 'Could not register this device.'], 422);
        }

        return response()->json(['message' => 'Notifications enabled on this device.']);
    }

    public function destroy(Request $request, PushNotifications $push): Response|JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:4096']]);

        try {
            $push->unsubscribe($data['token']);
        } catch (FirebaseException $e) {
            report($e);
        }

        return response()->noContent();
    }

    /**
     * Service worker with the public Firebase web config injected.
     */
    public function serviceWorker(): Response
    {
        return response()
            ->view('firebase-messaging-sw', ['config' => array_filter((array) config('larafire.web'))])
            ->header('Content-Type', 'application/javascript')
            ->header('Service-Worker-Allowed', '/');
    }
}
