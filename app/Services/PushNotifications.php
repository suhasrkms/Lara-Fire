<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;

/**
 * Topic-based FCM: no token storage needed.
 * Every device joins the broadcast topic plus a per-user topic.
 */
class PushNotifications
{
    public function __construct(protected Messaging $messaging) {}

    public function broadcastTopic(): string
    {
        return (string) config('larafire.fcm.broadcast_topic', 'larafire-all');
    }

    public function userTopic(string $uid): string
    {
        // Topic names allow [a-zA-Z0-9-_.~%]
        return config('larafire.fcm.user_topic_prefix', 'larafire-user-').preg_replace('/[^a-zA-Z0-9\-_.~%]/', '', $uid);
    }

    public function subscribe(string $token, string $uid): void
    {
        $this->messaging->subscribeToTopics([$this->broadcastTopic(), $this->userTopic($uid)], $token);
    }

    public function unsubscribe(string $token): void
    {
        $this->messaging->unsubscribeFromAllTopics($token);
    }

    public function toEveryone(string $title, string $body, ?string $link = null): void
    {
        $this->send($this->message($title, $body, $link)->withTopic($this->broadcastTopic()));
    }

    public function toUser(string $uid, string $title, string $body, ?string $link = null): void
    {
        $this->send($this->message($title, $body, $link)->withTopic($this->userTopic($uid)));
    }

    protected function message(string $title, string $body, ?string $link): CloudMessage
    {
        $message = CloudMessage::new()->withNotification(['title' => $title, 'body' => $body]);

        if ($link) {
            $message = $message
                ->withData(['link' => $link])
                ->withWebPushConfig(['fcm_options' => ['link' => $link]]);
        }

        return $message;
    }

    protected function send(CloudMessage $message): void
    {
        $this->messaging->send($message);
    }
}
