<?php

namespace Tests\Unit;

use App\Models\Opportunity;
use App\Opportunities\DeadlineAttention;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class DeadlineAttentionTest extends TestCase
{
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
    }

    public function test_attention_is_derived_only_for_pre_application_statuses(): void
    {
        $now = CarbonImmutable::parse('2026-08-13 12:00:00', 'UTC');
        CarbonImmutable::setTestNow($now);

        $overdue = new Opportunity(['status' => 'SAVED', 'deadline_at' => $now->subDay()]);
        $dueSoon = new Opportunity(['status' => 'PREPARING', 'deadline_at' => $now->addDays(3)]);
        $upcoming = new Opportunity(['status' => 'SAVED', 'deadline_at' => $now->addDays(12)]);
        $applied = new Opportunity(['status' => 'APPLIED', 'deadline_at' => $now->subDay()]);

        $this->assertSame('OVERDUE', DeadlineAttention::for($overdue));
        $this->assertSame('DUE_SOON', DeadlineAttention::for($dueSoon));
        $this->assertSame('UPCOMING', DeadlineAttention::for($upcoming));
        $this->assertNull(DeadlineAttention::for($applied));
    }
}
