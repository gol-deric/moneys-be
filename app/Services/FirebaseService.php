<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = config('firebase.credentials');

        if (!$credentialsPath || !file_exists($credentialsPath)) {
            throw new \Exception('Firebase credentials file not found at: ' . $credentialsPath);
        }

        $factory = (new Factory)->withServiceAccount($credentialsPath);
        $this->messaging = $factory->createMessaging();
    }

    /**
     * Send a notification to a device.
     *
     * @param string $token The FCM device token
     * @param string $title The notification title
     * @param string $body The notification body
     * @param array $data Additional data to send with the notification
     * @return void
     */
    public function sendNotification(string $token, string $title, string $body, array $data = []): void
    {
        try {
            $notification = FirebaseNotification::create($title, $body);

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);
        } catch (\Exception $e) {
            \Log::error('Firebase notification failed: ' . $e->getMessage(), [
                'token' => $token,
                'title' => $title,
                'body' => $body,
            ]);
        }
    }
}
