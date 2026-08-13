<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchivedFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_archived_filter_returns_owned_archived_items(): void
    {
        $owner = User::factory()->create();
        $archived = $owner->opportunities()->create([
            'type' => 'INTERNSHIP',
            'status' => 'SAVED',
            'priority' => 'MEDIUM',
            'title' => 'Archived item',
            'organization' => 'Synthetic Organization',
            'archived_at' => now(),
        ]);

        $this->actingAs($owner, 'web')
            ->getJson('/api/v1/opportunities?archived=true')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', (string) $archived->getKey());
    }
}
