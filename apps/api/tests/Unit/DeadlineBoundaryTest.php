<?php

namespace Tests\Unit;

use App\Opportunities\DeadlineInput;
use PHPUnit\Framework\TestCase;

class DeadlineBoundaryTest extends TestCase
{
    public function test_local_date_boundaries_are_converted_to_utc(): void
    {
        $from = DeadlineInput::dateBoundary('2026-08-20', 'Asia/Tokyo', false);
        $to = DeadlineInput::dateBoundary('2026-08-20', 'Asia/Tokyo', true);

        $this->assertSame('2026-08-19T15:00:00+00:00', $from->toIso8601String());
        $this->assertSame('2026-08-20T14:59:59+00:00', $to->toIso8601String());
    }
}
