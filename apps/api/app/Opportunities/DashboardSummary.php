<?php

namespace App\Opportunities;

use App\Models\Opportunity;
use App\Models\OpportunityEvent;
use App\Models\User;
use Carbon\CarbonImmutable;

final class DashboardSummary
{
    private const ACTIVE_STATUSES = ['SAVED', 'PREPARING', 'APPLIED', 'INTERVIEWING', 'OFFERED'];

    private const DEADLINE_STATUSES = ['SAVED', 'PREPARING'];

    public static function for(User $user, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now('UTC');
        $horizon = $now->addDays(7);
        $active = Opportunity::query()->ownedBy($user)->whereNull('archived_at');

        $dueSoon = (clone $active)
            ->whereIn('status', self::DEADLINE_STATUSES)
            ->whereBetween('deadline_at', [$now, $horizon])
            ->orderBy('deadline_at')
            ->limit(6)
            ->get();

        $overdue = (clone $active)
            ->whereIn('status', self::DEADLINE_STATUSES)
            ->where('deadline_at', '<', $now)
            ->orderBy('deadline_at')
            ->limit(6)
            ->get();

        $nextActions = (clone $active)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->whereNotNull('next_action')
            ->whereNotNull('next_action_at')
            ->where('next_action_at', '<=', $horizon)
            ->orderBy('next_action_at')
            ->limit(6)
            ->get();

        $counts = (clone $active)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->selectRaw('status, COUNT(*) AS aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $statusCounts = [];
        foreach (self::ACTIVE_STATUSES as $status) {
            $statusCounts[$status] = (int) ($counts[$status] ?? 0);
        }

        $recentActivity = OpportunityEvent::query()
            ->whereHas('opportunity', fn ($query) => $query->ownedBy($user))
            ->with('opportunity:id,owner_id,title,organization,status')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return [
            'generated_at' => $now->toISOString(),
            'horizon_days' => 7,
            'status_counts' => $statusCounts,
            'due_soon' => $dueSoon,
            'overdue' => $overdue,
            'next_actions' => $nextActions,
            'recent_activity' => $recentActivity,
        ];
    }
}
