<?php

namespace Tests\Unit;

use App\Support\Phone\EastAfricaPhone;
use Tests\TestCase;

class EastAfricaPhoneTest extends TestCase
{
    public function test_compose_kenya_mobile(): void
    {
        $this->assertSame('+254712345678', EastAfricaPhone::compose('254', '712345678'));
    }

    public function test_compose_strips_leading_zero(): void
    {
        $this->assertSame('+254712345678', EastAfricaPhone::compose('254', '0712345678'));
    }

    public function test_rejects_too_many_digits_for_kenya(): void
    {
        $this->assertNull(EastAfricaPhone::compose('254', '71234567890123456789'));
        $this->assertFalse(EastAfricaPhone::isValid('254', '71234567890123456789'));
    }

    public function test_parse_e164_number(): void
    {
        $parsed = EastAfricaPhone::parse('+255712345678');

        $this->assertSame('255', $parsed['dial_code']);
        $this->assertSame('712345678', $parsed['local']);
    }

    public function test_dial_for_iso(): void
    {
        $this->assertSame('256', EastAfricaPhone::dialForIso('UG'));
        $this->assertSame('254', EastAfricaPhone::dialForIso(null));
    }
}
