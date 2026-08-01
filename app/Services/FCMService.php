<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FCMService
{
    /**
     * Send Push Notification to a Student/Staff device via FCM
     */
    public static function sendNotification($fcmToken, $title, $body, $data = [])
    {
        if (!$fcmToken) {
            return false;
        }

        // Send request via Firebase Cloud Messaging API
        $response = Http::withHeaders([
            'Authorization' => 'key=' . env('FCM_SERVER_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $fcmToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => $data,
        ]);

        return $response->successful();
    }
}