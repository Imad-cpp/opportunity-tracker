<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\OpportunityEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_records_created_event_and_next_action(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner, 'web')->postJson('/api/v1/opportunities', $this->payload([
            'next_action' => 'Prepare application draft',
            'next_action_at' => '2026-09-01T09:00:00+00:00',
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.status', 'SAVED')
            ->assertJsonPath('data.next_action', 'Prepare application draft');

        $opportunityId = $response->json('data.id');
        $event = OpportunityEvent::query()
            ->where('opportunity_id', $opportunityId)
            ->sole();

        $this->assertSame('CREATED', $event->type);
        $this->assertSame($owner->getKey(), $event->actor_id);
        $this->assertNull($event->from_status);
        $this->assertNull($event->to_status);
        $this->assertNull($event->changed_fields);
    }

    public function test_update_records_only_changed_field_names_and_skips_noop_updates(): void
    {
        $owner = User::factory()->create();
        $opportunity = $this->createOpportunity($owner, 'Original');
        $base = '/api/v1/opportunities/'.$opportunity->getKey();

        $this->actingAs($owner, 'web')->patchJson($base, [
            'title' => 'Updated title',
            'notes' => 'Synthetic updated note.',
            'next_action' => 'Submit application',
            'next_action_at' => '2026-09-02T12:00:00+00:00',
        ])->assertOk()
            ->assertJsonPath('data.next_action', 'Submit application');

        $event = OpportunityEvent::query()
            ->where('opportunity_id', $opportunity->getKey())
            ->where('type', 'UPDATED')
            ->sole();

        $this->assertSame(
            ['next_action', 'next_action_at', 'notes', 'title'],
            $event->changed_fields,
        );
        $this->assertNotContains('Synthetic updated note.', $event->changed_fields);

        $this->actingAs($owner, 'web')->patchJson($base, [
            'title' => 'Updated title',
        ])->assertOk();

        $this->assertSame(
            1,
            OpportunityEvent::query()
                ->where('opportunity_id', $opportunity->getKey())
                ->where('type', 'UPDATED')
                ->count(),
        );
    }

    public function test_status_changes_record_from_and_to_and_same_status_is_noop(): void
    {
        $owner = User::factory()->create();
        $opportunity = $this->createOpportunity($owner, 'Status flow');
        $endpoint = '/api/v1/opportunities/'.$opportunity->getKey().'/status';

        $this->actingAs($owner, 'web')->postJson($endpoint, [
            'status' => 'APPLIED',
        ])->assertOk()
            ->assertJsonPath('data.status', 'APPLIED');

        $event = OpportunityEvent::query()
            ->where('opportunity_id', $opportunity->getKey())
            ->where('type', 'STATUS_CHANGED')
            ->sole();

        $this->assertSame('SAVED', $event->from_status);
        $this->assertSame('APPLIED', $event->to_status);

        $this->actingAs($owner, 'web')->postJson($endpoint, [
            'status' => 'APPLIED',
        ])->assertOk();

        $this->assertSame(
            1,
            OpportunityEvent::query()
                ->where('opportunity_id', $opportunity->getKey())
                ->where('type', 'STATUS_CHANGED')
                ->count(),
        );

        $this->actingAs($owner, 'web')->postJson($endpoint, [
            'status' => 'UNKNOWN',
        ])->assertUnprocessable()
            ->assertJsonPath('error.code', 'VALIDATION_FAILED');
    }

    public function test_archive_and_restore_events_are_idempotent(): void
    {
        $owner = User::factory()->create();
        $opportunity = $this->createOpportunity($owner, 'Archive flow');
        $base = '/api/v1/opportunities/'.$opportunity->getKey();

        $this->actingAs($owner, 'web')->postJson($base.'/archive')->assertOk();
        $this->actingAs($owner, 'web')->postJson($base.'/archive')->assertOk();
        $this->actingAs($owner, 'web')->postJson($base.'/restore')->assertOk();
        $this->actingAs($owner, 'web')->postJson($base.'/restore')->assertOk();

        $this->assertSame(
            ['ARCHIVED', 'RESTORED'],
            OpportunityEvent::query()
                ->where('opportunity_id', $opportunity->getKey())
                ->orderBy('created_at')
                ->pluck('type')
                ->all(),
        );
    }

    public function test_event_history_is_owner_scoped_and_delete_cascades_history(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($owner, 'web')->postJson(
            '/api/v1/opportunities',
            $this->payload(),
        )->assertCreated();

        $opportunityId = $response->json('data.id');
        $eventsEndpoint = '/api/v1/opportunities/'.$opportunityId.'/events';

        $this->actingAs($owner, 'web')->getJson($eventsEndpoint)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'CREATED');

        $this->actingAs($other, 'web')->getJson($eventsEndpoint)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'NOT_FOUND');

        $this->actingAs($owner, 'web')
            ->deleteJson('/api/v1/opportunities/'.$opportunityId)
            ->assertNoContent();

        $this->assertDatabaseMissing('opportunity_events', [
            'opportunity_id' => $opportunityId,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'type' => 'INTERNSHIP',
            'priority' => 'MEDIUM',
            'title' => 'Synthetic opportunity',
            'organization' => 'Synthetic Organization',
            'source_url' => 'https://example.test/opportunity',
            'location' => 'Remote',
            'notes' => 'Synthetic notes only.',
        ], $overrides);
    }

    private function createOpportunity(User $owner, string $title): Opportunity
    {
        return $owner->opportunities()->create([
            'type' => 'INTERNSHIP',
            'status' => 'SAVED',
            'priority' => 'MEDIUM',
            'title' => $title,
            'organization' => 'Synthetic Organization',
        ]);
    }
}
