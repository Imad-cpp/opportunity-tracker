<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'type' => $this->type,
            'from_status' => $this->from_status,
            'to_status' => $this->to_status,
            'changed_fields' => $this->changed_fields,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
