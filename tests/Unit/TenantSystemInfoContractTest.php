<?php

namespace Tests\Unit;

use App\Domain\Tenancy\Support\IntegrationPayloadSummarizer;
use App\Domain\Tenancy\Support\TenantSystemInfoContract;
use App\Domain\Tenancy\Support\TenantSystemInfoValidator;
use Tests\TestCase;

class TenantSystemInfoContractTest extends TestCase
{
    private TenantSystemInfoValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new TenantSystemInfoValidator(new IntegrationPayloadSummarizer);
    }

    public function test_valid_when_full_contract_payload(): void
    {
        $result = $this->validator->validate(TenantSystemInfoContract::samplePayload());

        $this->assertSame('valid', $result['status']);
        $this->assertSame([], $result['missing_required']);
        $this->assertSame([], $result['missing_recommended']);
    }

    public function test_partial_when_core_present_but_recommended_missing(): void
    {
        $result = $this->validator->validate([
            'status' => 'ok',
            'version' => '1.0.0',
            'project' => 'Prady MFI',
        ]);

        $this->assertSame('partial', $result['status']);
        $this->assertContains('tenant_code', $result['missing_recommended']);
    }

    public function test_invalid_when_version_missing(): void
    {
        $result = $this->validator->validate([
            'status' => 'ok',
            'project' => 'Prady MFI',
        ]);

        $this->assertSame('invalid', $result['status']);
        $this->assertContains('version', $result['missing_required']);
    }

    public function test_invalid_when_sensitive_keys_present(): void
    {
        $payload = TenantSystemInfoContract::samplePayload();
        $payload['api_secret'] = 'leaked';

        $result = $this->validator->validate($payload);

        $this->assertSame('invalid', $result['status']);
        $this->assertNotEmpty($result['issues']);
    }
}
