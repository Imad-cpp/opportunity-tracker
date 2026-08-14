<?php

namespace App\Opportunities;

use App\Models\Opportunity;
use App\Models\OpportunityEvent;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

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
            ->orderBy('id')
            ->limit(6)
            ->get();

        $overdue = (clone $active)
            ->whereIn('status', self::DEADLINE_STATUSES)
            ->where('deadline_at', '<', $now)
            ->orderBy('deadline_at')
            ->orderBy('id')
            ->limit(6)
            ->get();

        $nextActions = (clone $active)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->whereNotNull('next_action')
            ->whereNotNull('next_action_at')
            ->where('next_action_at', '<=', $horizon)
            ->orderBy('next_action_at')
            ->orderBy('id')
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
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return [
            'generated_at' => $now->toISOString(),
            'horizon_days' => 7,
            'status_counts' => $statusCounts,
            'due_soon' => self::opportunities($dueSoon, $now),
            'overdue' => self::opportunities($overdue, $now),
            'next_actions' => self::opportunities($nextActions, $now),
            'recent_activity' => $recentActivity
                ->map(static fn (OpportunityEvent $event): array => self::activity($event))
                ->values()
                ->all(),
        ];
    }

    /** @param Collection<int, Opportunity> $opportunities */
    private static function opportunities(Collection $opportunities, CarbonImmutable $now): array
    {
        return $opportunities
            ->map(static fn (Opportunity $opportunity): array => [
                'id' => (string) $opportunity->id,
                'type' => $opportunity->type,
                'status' => $opportunity->status,
                'priority' => $opportunity->priority,
                'title' => $opportunity->title,
                'organization' => $opportunity->organization,
                'deadline_at' => $opportunity->deadline_at?->toISOString(),
                'deadline_precision' => $opportunity->deadline_precision,
                'deadline_timezone' => $opportunity->deadline_timezone,
                'deadline_attention' => DeadlineAttention::for($opportunity, $now),
                'next_action' => $opportunity->next_action,
                'next_action_at' => $opportunity->next_action_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    private static function activity(OpportunityEvent $event): array
    {
        return [
            'id' => (string) $event->id,
            'type' => $event->type,
            'from_status' => $event->from_status,
            'to_status' => $event->to_status,
            'changed_fields' => $event->changed_fields,
            'created_at' => $event->created_at?->toISOString(),
            'opportunity' => [
                'id' => (string) $event->opportunity->id,
                'title' => $event->opportunity->title,
                'organization' => $event->opportunity->organization,
                'status' => $event->opportunity->status,
            ],
        ];
    }
}
