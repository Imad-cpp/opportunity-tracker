<?php

namespace Tests\Feature;

use App\Models\User;
use App\Opportunities\DashboardSummary;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_is_owner_scoped_and_prioritizes_actionable_work(): void
    {
        $now = CarbonImmutable::parse('2026-08-14T10:00:00Z');
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $dueSoon = $owner->opportunities()->create([
            'type' => 'INTERNSHIP',
            'status' => 'SAVED',
            'priority' => 'HIGH',
            'title' => 'Due soon internship',
            'organization' => 'Synthetic Research Lab',
            'deadline_at' => $now->addDays(2),
            'deadline_precision' => 'EXACT',
            'deadline_timezone' => 'UTC',
        ]);

        $overdue = $owner->opportunities()->create([
            'type' => 'SCHOLARSHIP',
            'status' => 'PREPARING',
            'priority' => 'HIGH',
            'title' => 'Overdue scholarship',
            'organization' => 'Synthetic Foundation',
            'deadline_at' => $now->subDay(),
            'deadline_precision' => 'EXACT',
            'deadline_timezone' => 'UTC',
        ]);

        $owner->opportunities()->create([
            'type' => 'JOB',
            'status' => 'APPLIED',
            'priority' => 'MEDIUM',
            'title' => 'Applied role',
            'organization' => 'Synthetic Studio',
        ]);

        $nextAction = $owner->opportunities()->create([
            'type' => 'JOB',
            'status' => 'OFFERED',
            'priority' => 'HIGH',
            'title' => 'Offer follow up',
            'organization' => 'Synthetic Systems',
            'next_action' => 'Review offer terms',
            'next_action_at' => $now->addDay(),
        ]);

        $owner->opportunities()->create([
            'type' => 'PROGRAM',
            'status' => 'ACCEPTED',
            'priority' => 'LOW',
            'title' => 'Closed program',
            'organization' => 'Synthetic University',
            'next_action' => 'Should not appear',
            'next_action_at' => $now,
        ]);

        $owner->opportunities()->create([
            'type' => 'OTHER',
            'status' => 'SAVED',
            'priority' => 'LOW',
            'title' => 'Archived item',
            'organization' => 'Synthetic Archive',
            'deadline_at' => $now->addDay(),
            'archived_at' => $now,
        ]);

        $other->opportunities()->create([
            'type' => 'JOB',
            'status' => 'SAVED',
            'priority' => 'HIGH',
            'title' => 'Foreign item',
            'organization' => 'Other Owner',
            'deadline_at' => $now->addDay(),
        ]);

        $summary = DashboardSummary::for($owner, $now);

        $this->assertSame([
            'SAVED' => 1,
            'PREPARING' => 1,
            'APPLIED' => 1,
            'INTERVIEWING' => 0,
            'OFFERED' => 1,
        ], $summary['status_counts']);
        $this->assertSame([(string) $dueSoon->id], array_column($summary['due_soon'], 'id'));
        $this->assertSame('DUE_SOON', $summary['due_soon'][0]['deadline_attention']);
        $this->assertSame([(string) $overdue->id], array_column($summary['overdue'], 'id'));
        $this->assertSame('OVERDUE', $summary['overdue'][0]['deadline_attention']);
        $this->assertSame([(string) $nextAction->id], array_column($summary['next_actions'], 'id'));
        $this->assertSame('Review offer terms', $summary['next_actions'][0]['next_action']);
    }

    public function test_recent_activity_is_owner_scoped_and_exposes_only_summary_context(): void
    {
        $now = CarbonImmutable::parse('2026-08-14T10:00:00Z');
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $owned = $owner->opportunities()->create([
            'type' => 'INTERNSHIP',
            'status' => 'APPLIED',
            'priority' => 'MEDIUM',
            'title' => 'Owned internship',
            'organization' => 'Synthetic Lab',
        ]);
        $foreign = $other->opportunities()->create([
            'type' => 'JOB',
            'status' => 'APPLIED',
            'priority' => 'MEDIUM',
            'title' => 'Foreign role',
            'organization' => 'Other Owner',
        ]);

        $owned->events()->create([
            'actor_id' => $owner->id,
            'type' => 'STATUS_CHANGED',
            'from_status' => 'PREPARING',
            'to_status' => 'APPLIED',
            'created_at' => $now->subMinute(),
        ]);
        $foreign->events()->create([
            'actor_id' => $other->id,
            'type' => 'STATUS_CHANGED',
            'from_status' => 'PREPARING',
            'to_status' => 'APPLIED',
            'created_at' => $now,
        ]);

        $summary = DashboardSummary::for($owner, $now);

        $this->assertCount(1, $summary['recent_activity']);
        $this->assertSame('Owned internship', $summary['recent_activity'][0]['opportunity']['title']);
        $this->assertSame('STATUS_CHANGED', $summary['recent_activity'][0]['type']);
        $this->assertArrayNotHasKey('actor_id', $summary['recent_activity'][0]);
        $this->assertArrayNotHasKey('notes', $summary['recent_activity'][0]['opportunity']);
    }
}
