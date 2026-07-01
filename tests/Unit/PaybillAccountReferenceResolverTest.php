<?php

namespace Tests\Unit;

use App\Support\Billing\PaybillAccountReferenceResolver;
use PHPUnit\Framework\TestCase;

class PaybillAccountReferenceResolverTest extends TestCase
{
    public function test_derives_compact_uppercase_reference_from_client_name(): void
    {
        $this->assertSame('PAGECAPITAL', PaybillAccountReferenceResolver::fromClientName('PAGE CAPITAL LTD'));
        $this->assertSame('CLIENTNAME', PaybillAccountReferenceResolver::fromClientName('Client name'));
        $this->assertSame('JOSHATKIPRONO', PaybillAccountReferenceResolver::fromClientName('Josh At Kiprono Ltd'));
    }

    public function test_returns_empty_string_when_client_name_missing(): void
    {
        $this->assertSame('', PaybillAccountReferenceResolver::fromClientName(null));
        $this->assertSame('', PaybillAccountReferenceResolver::fromClientName('   '));
    }
}
