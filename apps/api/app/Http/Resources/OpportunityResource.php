<?php

namespace App\Http\Resources;

use App\Models\Opportunity;
use App\Opportunities\DeadlineAttention;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Opportunity $opportunity */
        $opportunity = $this->resource;

        return [
            'id' => (string) $opportunity->id,
            'type' => $opportunity->type,
            'status' => $opportunity->status,
            'priority' => $opportunity->priority,
            'title' => $opportunity->title,
            'organization' => $opportunity->organization,
            'source_url' => $opportunity->source_url,
            'location' => $opportunity->location,
            'notes' => $opportunity->notes,
            'deadline_at' => $opportunity->deadline_at?->toISOString(),
            'deadline_precision' => $opportunity->deadline_precision,
            'deadline_timezone' => $opportunity->deadline_timezone,
            'deadline_attention' => DeadlineAttention::for($opportunity),
            'next_action' => $opportunity->next_action,
            'next_action_at' => $opportunity->next_action_at?->toISOString(),
            'archived_at' => $opportunity->archived_at?->toISOString(),
            'created_at' => $opportunity->created_at?->toISOString(),
            'updated_at' => $opportunity->updated_at?->toISOString(),
        ];
    }
}
