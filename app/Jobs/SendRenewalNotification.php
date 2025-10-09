<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Subscription;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendRenewalNotification implements ShouldQueue
{
    use Queueable;

    protected Subscription $subscription;
    protected int $daysAhead;

    /**
     * Create a new job instance.
     */
    public function __construct(Subscription $subscription, int $daysAhead)
    {
        $this->subscription = $subscription;
        $this->daysAhead = $daysAhead;
    }

    /**
     * Execute the job.
     */
    public function handle(FirebaseService $firebaseService): void
    {
        $user = $this->subscription->user;

        // Check if user has notifications enabled
        if (!$user->notifications_enabled) {
            return;
        }

        // Create notification title and message
        $title = $this->getNotificationTitle();
        $message = $this->getNotificationMessage();

        // Send Firebase push notification if FCM token exists
        if ($user->fcm_token) {
            $firebaseService->sendNotification(
                $user->fcm_token,
                $title,
                $message,
                [
                    'subscription_id' => $this->subscription->id,
                    'type' => 'renewal',
                ]
            );
        }

        // Save notification to database
        Notification::create([
            'user_id' => $user->id,
            'subscription_id' => $this->subscription->id,
            'title' => $title,
            'message' => $message,
            'notification_type' => 'renewal',
        ]);
    }

    /**
     * Get the notification title based on days ahead.
     */
    protected function getNotificationTitle(): string
    {
        return match ($this->daysAhead) {
            0 => 'Renewal Due Today',
            1 => 'Renewal Due Tomorrow',
            default => "Renewal Due in {$this->daysAhead} Days",
        };
    }

    /**
     * Get the notification message.
     */
    protected function getNotificationMessage(): string
    {
        $name = $this->subscription->name;
        $price = $this->subscription->price;
        $currency = $this->subscription->currency_code;

        return match ($this->daysAhead) {
            0 => "{$name} renewal of {$currency} {$price} is due today.",
            1 => "{$name} renewal of {$currency} {$price} is due tomorrow.",
            default => "{$name} renewal of {$currency} {$price} is due in {$this->daysAhead} days.",
        };
    }
}
