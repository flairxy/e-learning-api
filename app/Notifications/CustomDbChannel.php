<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class CustomDbChannel
{

    public function send($notifiable, Notification $notification)
    {
        $data = $notification->toDatabase($notifiable);

        return $notifiable->routeNotificationFor('database')->create([
            'id' => $notification->id,

            //customize here
            'sender' => $data['sender'], //<-- comes from toDatabase() Method below
            'type' => get_class($notification),
            'data' => $data,
            'read_at' => null,
        ]);
    }
}
