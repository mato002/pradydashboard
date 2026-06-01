<?php

namespace Tests\Unit;

use App\Support\PaymentsGateway\CanonicalCallbackUrls;
use Tests\TestCase;

class CanonicalCallbackUrlsTest extends TestCase
{
    public function test_generates_canonical_urls_from_payments_gateway_url_config(): void
    {
        config(['payment_gateway.base_url' => 'https://gateway.example.test']);

        $urls = CanonicalCallbackUrls::all();

        $this->assertSame('https://gateway.example.test/pay/c2b/validate', $urls['validation_url']);
        $this->assertSame('https://gateway.example.test/pay/c2b/confirm', $urls['confirmation_url']);
        $this->assertSame('https://gateway.example.test/pay/stk', $urls['stk_callback_url']);
        $this->assertSame('https://gateway.example.test/pay/b2c/result', $urls['b2c_result_url']);
        $this->assertSame('https://gateway.example.test/pay/b2c/timeout', $urls['b2c_timeout_url']);
    }

    public function test_does_not_hardcode_payments_domain_when_config_changes(): void
    {
        config(['payment_gateway.base_url' => 'https://staging-payments.example.test']);

        foreach (CanonicalCallbackUrls::all() as $url) {
            $this->assertStringStartsWith('https://staging-payments.example.test/pay/', $url);
            $this->assertStringNotContainsString('payments.pradytecai.com', $url);
        }
    }

    public function test_prefills_blank_callback_fields_with_canonical_urls(): void
    {
        config(['payment_gateway.base_url' => 'https://gateway.example.test']);

        $prefilled = CanonicalCallbackUrls::prefillDefaults([
            'validation_url' => 'https://custom.example.test/validate',
        ]);

        $this->assertSame('https://custom.example.test/validate', $prefilled['validation_url']);
        $this->assertSame('https://gateway.example.test/pay/c2b/confirm', $prefilled['confirmation_url']);
        $this->assertSame('https://gateway.example.test/pay/stk', $prefilled['stk_callback_url']);
    }

    public function test_detects_legacy_internal_callback_urls(): void
    {
        $this->assertTrue(CanonicalCallbackUrls::isLegacyUrl(
            'https://payments.pradytecai.com/api/v1/callbacks/mpesa/stk'
        ));
        $this->assertFalse(CanonicalCallbackUrls::isLegacyUrl(
            'https://payments.pradytecai.com/pay/stk'
        ));
    }

    public function test_classifies_mismatched_and_canonical_urls(): void
    {
        config(['payment_gateway.base_url' => 'https://gateway.example.test']);

        $this->assertSame(
            CanonicalCallbackUrls::STATUS_CANONICAL,
            CanonicalCallbackUrls::classify('stk_callback_url', 'https://gateway.example.test/pay/stk')
        );
        $this->assertSame(
            CanonicalCallbackUrls::STATUS_LEGACY_INTERNAL,
            CanonicalCallbackUrls::classify('stk_callback_url', 'https://gateway.example.test/api/v1/callbacks/mpesa/stk')
        );
        $this->assertSame(
            CanonicalCallbackUrls::STATUS_MISMATCHED,
            CanonicalCallbackUrls::classify('stk_callback_url', 'https://other.example.test/pay/stk')
        );
        $this->assertSame(
            CanonicalCallbackUrls::STATUS_MISSING,
            CanonicalCallbackUrls::classify('stk_callback_url', null)
        );
    }

    public function test_assess_account_marks_legacy_urls_as_needing_update(): void
    {
        config(['payment_gateway.base_url' => 'https://gateway.example.test']);

        $assessment = CanonicalCallbackUrls::assessAccount([
            'supports_c2b' => true,
            'validation_url' => 'https://gateway.example.test/api/v1/callbacks/c2b/validate',
            'confirmation_url' => 'https://gateway.example.test/pay/c2b/confirm',
        ]);

        $this->assertTrue($assessment['needs_url_update']);
        $this->assertSame(CanonicalCallbackUrls::STATUS_LEGACY_INTERNAL, $assessment['overall_status']);
    }
}
