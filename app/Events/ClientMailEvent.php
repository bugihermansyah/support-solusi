<?php

namespace App\Events;

use App\Models\Reporting;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientMailEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reporting;
    public $toEmails;
    public $ccEmails;
    public $locationName;
    public $outstandingNumber;
    public $outstandingTitle;
    public $outstandingReporter;
    public $supportNames;

    public function __construct(Reporting $reporting, array $toEmails, array $ccEmails, string $locationName, string $outstandingNumber, string $outstandingTitle, string $outstandingReporter, array $supportNames)
    {
        $this->reporting = $reporting;
        $this->toEmails = $toEmails;
        $this->ccEmails = $ccEmails;
        $this->locationName = $locationName;
        $this->outstandingNumber = $outstandingNumber;
        $this->outstandingTitle = $outstandingTitle;
        $this->outstandingReporter = $outstandingReporter;
        $this->supportNames = $supportNames;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
