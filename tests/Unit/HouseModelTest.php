<?php

namespace Tests\Unit;

use App\Models\House;
use PHPUnit\Framework\TestCase;

class HouseModelTest extends TestCase
{
    // ==========================================
    // A. UNIT TESTS FOR HOUSE MODEL
    // ==========================================

    public function test_is_billable_returns_true_when_registered_and_active(): void
    {
        $house = new House([
            'is_registered' => true,
            'is_active' => true,
        ]);

        $this->assertTrue($house->is_billable);
    }

    public function test_is_billable_returns_false_when_not_registered(): void
    {
        $house = new House([
            'is_registered' => false,
            'is_active' => true,
        ]);

        $this->assertFalse($house->is_billable);
    }

    public function test_is_billable_returns_false_when_not_active(): void
    {
        $house = new House([
            'is_registered' => true,
            'is_active' => false,
        ]);

        $this->assertFalse($house->is_billable);
    }

    public function test_full_address_accessor(): void
    {
        $house = new House([
            'house_no' => '123',
            'street_name' => 'Jalan Tropika',
        ]);

        $this->assertEquals('123, Jalan Tropika', $house->full_address);
    }
}

