<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'owner_id',
    'type',
    'status',
    'priority',
    'title',
    'organization',
    'source_url',
    'location',
    'notes',
    'deadline_at',
    'deadline_precision',
    'deadline_timezone',
    'next_action',
    'next_action_at',
    'archived_at',
])]
class Opportunity extends Model
{
    use HasUuids;

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @param Builder<Opportunity> $query */
    public function scopeOwnedBy(Builder $query, User $owner): Builder
    {
        return $query->where('owner_id', $owner->getKey());
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'deadline_at' => 'datetime',
            'next_action_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }
}
