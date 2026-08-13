<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnershipScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_opportunity_scope_never_returns_another_owners_record(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $owned = $this->opportunity($owner, 'Owned opportunity');
        $foreign = $this->opportunity($other, 'Foreign opportunity');

        $ids = Opportunity::query()
            ->ownedBy($owner)
            ->pluck('id')
            ->all();

        $this->assertSame([(string) $owned->getKey()], $ids);
        $this->assertNotContains((string) $foreign->getKey(), $ids);
        $this->assertSame((string) $owner->getKey(), (string) $owned->owner->getKey());
    }

    private function opportunity(User $owner, string $title): Opportunity
    {
        return Opportunity::query()->create([
            'owner_id' => $owner->getKey(),
            'type' => 'INTERNSHIP',
            'status' => 'SAVED',
            'priority' => 'MEDIUM',
            'title' => $title,
            'organization' => 'Synthetic Organization',
        ]);
    }
}
