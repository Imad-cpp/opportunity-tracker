<?php

namespace App\Http\Resources;

use App\Models\OpportunityEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var OpportunityEvent $event */
        $event = $this->resource;

        return [
            'id' => (string) $event->id,
            'type' => $event->type,
            'from_status' => $event->from_status,
            'to_status' => $event->to_status,
            'changed_fields' => $event->changed_fields,
            'created_at' => $event->created_at?->toISOString(),
        ];
    }
}
