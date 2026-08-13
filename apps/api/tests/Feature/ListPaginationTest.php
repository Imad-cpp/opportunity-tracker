<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_uses_fixed_twenty_item_pages(): void
    {
        $owner = User::factory()->create();

        foreach (range(1, 21) as $index) {
            $owner->opportunities()->create([
                'type' => 'INTERNSHIP',
                'status' => 'SAVED',
                'priority' => 'MEDIUM',
                'title' => 'Opportunity '.$index,
                'organization' => 'Synthetic Organization',
            ]);
        }

        $this->actingAs($owner, 'web')
            ->getJson('/api/v1/opportunities?page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 20);
    }
}
