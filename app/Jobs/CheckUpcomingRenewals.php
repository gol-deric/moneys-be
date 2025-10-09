<?php

namespace App\Jobs;

use App\Models\Subscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckUpcomingRenewals implements ShouldQueue
{
    use Queueable;

    protected int $daysAhead;

    /**
     * Create a new job instance.
     */
    public function __construct(int $daysAhead)
    {
        $this->daysAhead = $daysAhead;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subscriptions = Subscription::dueInDays($this->daysAhead)->get();

        foreach ($subscriptions as $subscription) {
            SendRenewalNotification::dispatch($subscription, $this->daysAhead);
        }
    }
}
