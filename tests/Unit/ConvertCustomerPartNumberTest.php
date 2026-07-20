<?php

namespace Tests\Unit;

use App\Support\ConvertsCustomerPartNumber;
use PHPUnit\Framework\TestCase;

class ConvertCustomerPartNumberTest extends TestCase
{
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new class {
            use ConvertsCustomerPartNumber;

            public function convert(string $part, $customerId): string
            {
                return $this->convertCustomerPartNumber($part, $customerId);
            }
        };
    }

    public function test_tmmin_12_digit_non_zero_suffix(): void
    {
        $this->assertSame('12345-67890-AB', $this->sut->convert('1234567890AB', 1));
    }

    public function test_tmmin_12_digit_zero_suffix(): void
    {
        $this->assertSame('12345-67890', $this->sut->convert('123456789000', 1));
    }

    public function test_mmki_10_digit_unchanged_for_customer_6_23_24(): void
    {
        foreach ([6, 23, 24] as $customerId) {
            $this->assertSame('1234567890', $this->sut->convert('1234567890', $customerId));
        }
    }

    public function test_suzuki_10_digit_gets_dash(): void
    {
        $this->assertSame('12345-67890', $this->sut->convert('1234567890', 14));
        $this->assertSame('12345-67890', $this->sut->convert('1234567890', 22));
    }

    public function test_tbina_10_digit_gets_dash(): void
    {
        $this->assertSame('12345-67890', $this->sut->convert('1234567890', 99));
    }

    public function test_suzuki_13_digit(): void
    {
        $this->assertSame('12345-67890-ABC', $this->sut->convert('1234567890ABC', 14));
    }

    public function test_other_length_returned_unchanged(): void
    {
        $this->assertSame('12345678901', $this->sut->convert('12345678901', 1)); // 11 chars
    }
}
