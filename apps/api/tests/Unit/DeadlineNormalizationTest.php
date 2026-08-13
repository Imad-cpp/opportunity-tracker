<?php

namespace Tests\Unit;

use App\Models\User;
use App\Opportunities\DeadlineInput;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class DeadlineNormalizationTest extends TestCase
{
    public function test_exact_local_deadline_is_normalized_to_utc(): void
    {
        $user = new User(['timezone' => 'UTC']);
        $attributes = DeadlineInput::attributes([
            'deadline_at' => '2026-09-01T09:30',
            'deadline_precision' => 'DATETIME',
            'deadline_timezone' => 'America/New_York',
        ], $user);

        $expected = CarbonImmutable::parse('2026-09-01 13:30:00', 'UTC');

        $this->assertSame($expected->timestamp, $attributes['deadline_at']->timestamp);
        $this->assertSame('DATETIME', $attributes['deadline_precision']);
        $this->assertSame('America/New_York', $attributes['deadline_timezone']);
    }
}
