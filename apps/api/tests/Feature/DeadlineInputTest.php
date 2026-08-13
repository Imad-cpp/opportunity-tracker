<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeadlineInputTest extends TestCase
{
    use RefreshDatabase;

    public function test_date_only_deadline_uses_account_timezone_end_of_day(): void
    {
        $owner = User::factory()->create(['timezone' => 'Asia/Tokyo']);
        $response = $this->actingAs($owner, 'web')->postJson('/api/v1/opportunities', [
            'type' => 'INTERNSHIP',
            'priority' => 'MEDIUM',
            'title' => 'Synthetic opportunity',
            'organization' => 'Synthetic Organization',
            'deadline_at' => '2026-09-01',
            'deadline_precision' => 'DATE',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.deadline_precision', 'DATE')
            ->assertJsonPath('data.deadline_timezone', 'Asia/Tokyo');

        $opportunity = Opportunity::query()->findOrFail($response->json('data.id'));
        $expected = CarbonImmutable::parse('2026-09-01 23:59:59', 'Asia/Tokyo')->utc();
        $this->assertSame($expected->timestamp, $opportunity->deadline_at->timestamp);
    }

    public function test_date_only_deadline_rejects_client_timezone(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner, 'web')->postJson('/api/v1/opportunities', [
            'type' => 'INTERNSHIP',
            'priority' => 'MEDIUM',
            'title' => 'Synthetic opportunity',
            'organization' => 'Synthetic Organization',
            'deadline_at' => '2026-09-01',
            'deadline_precision' => 'DATE',
            'deadline_timezone' => 'UTC',
        ])->assertUnprocessable();
    }
}
