<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewStreamUpdate implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $documentId;
    public string $chunk;

    public function __construct(int $documentId, string $chunk)
    {
        $this->documentId = $documentId;
        $this->chunk = $chunk;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('document-review.' . $this->documentId);
    }

    public function broadcastAs(): string
    {
        return 'ReviewStreamUpdate';
    }
}