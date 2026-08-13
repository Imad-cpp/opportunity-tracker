<?php

namespace App\Opportunities;

use App\Models\Opportunity;
use Carbon\CarbonImmutable;

final class DeadlineAttention
{
    private const ACTIONABLE_STATUSES = ['SAVED', 'PREPARING'];

    public static function for(Opportunity $opportunity, ?CarbonImmutable $now = null): ?string
    {
        if ($opportunity->archived_at !== null
            || $opportunity->deadline_at === null
            || ! in_array($opportunity->status, self::ACTIONABLE_STATUSES, true)) {
            return null;
        }

        $now ??= CarbonImmutable::now('UTC');
        $deadline = CarbonImmutable::instance($opportunity->deadline_at)->utc();

        if ($deadline->lt($now)) {
            return 'OVERDUE';
        }

        if ($deadline->lte($now->addDays(7))) {
            return 'DUE_SOON';
        }

        return 'UPCOMING';
    }
}
