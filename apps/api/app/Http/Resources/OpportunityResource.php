<?php

namespace App\Http\Resources;

use App\Opportunities\DeadlineAttention;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'priority' => $this->priority,
            'title' => $this->title,
            'organization' => $this->organization,
            'source_url' => $this->source_url,
            'location' => $this->location,
            'notes' => $this->notes,
            'deadline_at' => $this->deadline_at?->toISOString(),
            'deadline_precision' => $this->deadline_precision,
            'deadline_timezone' => $this->deadline_timezone,
            'deadline_attention' => DeadlineAttention::for($this->resource),
            'next_action' => $this->next_action,
            'next_action_at' => $this->next_action_at?->toISOString(),
            'archived_at' => $this->archived_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
