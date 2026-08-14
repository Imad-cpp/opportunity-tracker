<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_an_authenticated_session(): void
    {
        $this->getJson('/api/v1/dashboard/summary')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_dashboard_returns_only_the_authenticated_owners_summary(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $owned = $owner->opportunities()->create([
            'type' => 'INTERNSHIP',
            'status' => 'SAVED',
            'priority' => 'HIGH',
            'title' => 'Owned opportunity',
            'organization' => 'Synthetic Research Lab',
            'deadline_at' => now()->addDay(),
            'deadline_precision' => 'EXACT',
            'deadline_timezone' => 'UTC',
        ]);

        $other->opportunities()->create([
            'type' => 'JOB',
            'status' => 'SAVED',
            'priority' => 'HIGH',
            'title' => 'Foreign opportunity',
            'organization' => 'Other Owner',
            'deadline_at' => now()->addDay(),
        ]);

        $owned->events()->create([
            'actor_id' => $owner->id,
            'type' => 'CREATED',
        ]);

        $response = $this->actingAs($owner, 'web')
            ->getJson('/api/v1/dashboard/summary')
            ->assertOk()
            ->assertJsonPath('data.status_counts.SAVED', 1)
            ->assertJsonCount(1, 'data.due_soon')
            ->assertJsonPath('data.due_soon.0.id', (string) $owned->id)
            ->assertJsonPath('data.due_soon.0.title', 'Owned opportunity')
            ->assertJsonCount(1, 'data.recent_activity')
            ->assertJsonPath('data.recent_activity.0.opportunity.id', (string) $owned->id);

        $response->assertJsonMissing(['title' => 'Foreign opportunity']);
        $response->assertJsonMissingPath('data.due_soon.0.owner_id');
        $response->assertJsonMissingPath('data.due_soon.0.notes');
        $response->assertJsonMissingPath('data.recent_activity.0.actor_id');
    }
}
