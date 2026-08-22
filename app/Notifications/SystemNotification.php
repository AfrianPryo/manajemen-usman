<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    public string $title;
    public string $message;
    public string $badge;
    public bool $actionable;
    public ?string $url;
    public array $extraData;

    public function __construct(
        string $title,
        string $message,
        string $badge = 'Info',
        bool $actionable = false,
        ?string $url = null,
        array $extraData = []
    ) {
        $this->title = $title;
        $this->message = $message;
        $this->badge = $badge;
        $this->actionable = $actionable;
        $this->url = $url;
        $this->extraData = $extraData;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'title' => $this->title,
            'message' => $this->message,
            'badge' => $this->badge,
            'actionable' => $this->actionable,
            'url' => $this->url,
        ], $this->extraData);
    }
}