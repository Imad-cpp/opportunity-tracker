<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_opportunity_with_server_controlled_status(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')->postJson('/api/v1/opportunities', $this->payload([
            'title' => '  Backend Internship  ',
            'source_url' => ' https://example.test/opportunity ',
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.status', 'SAVED')
            ->assertJsonPath('data.title', 'Backend Internship')
            ->assertJsonPath('data.source_url', 'https://example.test/opportunity')
            ->assertJsonMissingPath('data.owner_id');

        $this->assertDatabaseHas('opportunities', [
            'owner_id' => $user->getKey(),
            'status' => 'SAVED',
            'title' => 'Backend Internship',
        ]);
    }

    public function test_create_rejects_protected_workflow_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->postJson('/api/v1/opportunities', $this->payload([
            'status' => 'ACCEPTED',
            'owner_id' => User::factory()->create()->getKey(),
        ]))->assertUnprocessable()
            ->assertJsonPath('error.code', 'VALIDATION_FAILED')
            ->assertJsonStructure(['error' => ['details' => ['status', 'owner_id']]]);
    }

    public function test_source_url_accepts_http_and_https_only(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->postJson('/api/v1/opportunities', $this->payload([
            'source_url' => 'ftp://example.test/file',
        ]))->assertUnprocessable()
            ->assertJsonPath('error.code', 'VALIDATION_FAILED');
    }

    public function test_default_list_contains_only_owned_active_records(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $active = $this->createOpportunity($owner, 'Active');
        $this->createOpportunity($owner, 'Archived', ['archived_at' => now()]);
        $this->createOpportunity($other, 'Foreign');

        $response = $this->actingAs($owner, 'web')->getJson('/api/v1/opportunities')->assertOk();

        $response->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', (string) $active->getKey());
    }

    public function test_foreign_resource_is_not_found_for_reads_and_mutations(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $foreign = $this->createOpportunity($other, 'Foreign');
        $base = '/api/v1/opportunities/'.$foreign->getKey();

        $this->actingAs($owner, 'web')->getJson($base)
            ->assertNotFound()->assertJsonPath('error.code', 'NOT_FOUND');
        $this->actingAs($owner, 'web')->patchJson($base, ['title' => 'Changed'])
            ->assertNotFound()->assertJsonPath('error.code', 'NOT_FOUND');
        $this->actingAs($owner, 'web')->postJson($base.'/archive')
            ->assertNotFound()->assertJsonPath('error.code', 'NOT_FOUND');
        $this->actingAs($owner, 'web')->postJson($base.'/restore')
            ->assertNotFound()->assertJsonPath('error.code', 'NOT_FOUND');
        $this->actingAs($owner, 'web')->deleteJson($base)
            ->assertNotFound()->assertJsonPath('error.code', 'NOT_FOUND');

        $this->assertDatabaseHas('opportunities', ['id' => $foreign->getKey(), 'title' => 'Foreign']);
    }

    public function test_owner_can_update_editable_fields_but_not_status(): void
    {
        $owner = User::factory()->create();
        $opportunity = $this->createOpportunity($owner, 'Original');

        $this->actingAs($owner, 'web')->patchJson('/api/v1/opportunities/'.$opportunity->getKey(), [
            'title' => ' Updated title ',
            'priority' => 'HIGH',
            'notes' => '',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Updated title')
            ->assertJsonPath('data.priority', 'HIGH')
            ->assertJsonPath('data.notes', null)
            ->assertJsonPath('data.status', 'SAVED');

        $this->actingAs($owner, 'web')->patchJson('/api/v1/opportunities/'.$opportunity->getKey(), [
            'status' => 'ACCEPTED',
        ])->assertUnprocessable();
    }

    public function test_archive_hides_record_and_restore_returns_it_to_default_list(): void
    {
        $owner = User::factory()->create();
        $opportunity = $this->createOpportunity($owner, 'Tracked');
        $base = '/api/v1/opportunities/'.$opportunity->getKey();

        $this->actingAs($owner, 'web')->postJson($base.'/archive')
            ->assertOk()->assertJsonPath('data.id', (string) $opportunity->getKey());
        $this->actingAs($owner, 'web')->getJson('/api/v1/opportunities')->assertJsonCount(0, 'data');
        $this->actingAs($owner, 'web')->getJson($base)->assertOk();

        $this->actingAs($owner, 'web')->postJson($base.'/restore')
            ->assertOk()->assertJsonPath('data.archived_at', null);
        $this->actingAs($owner, 'web')->getJson('/api/v1/opportunities')->assertJsonCount(1, 'data');
    }

    public function test_delete_physically_removes_owned_record(): void
    {
        $owner = User::factory()->create();
        $opportunity = $this->createOpportunity($owner, 'Delete me');

        $this->actingAs($owner, 'web')
            ->deleteJson('/api/v1/opportunities/'.$opportunity->getKey())
            ->assertNoContent();

        $this->assertDatabaseMissing('opportunities', ['id' => $opportunity->getKey()]);
    }

    public function test_opportunity_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/opportunities')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
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

    private function createOpportunity(User $owner, string $title, array $overrides = []): Opportunity
    {
        return $owner->opportunities()->create(array_replace([
            'type' => 'INTERNSHIP',
            'status' => 'SAVED',
            'priority' => 'MEDIUM',
            'title' => $title,
            'organization' => 'Synthetic Organization',
        ], $overrides));
    }
}
