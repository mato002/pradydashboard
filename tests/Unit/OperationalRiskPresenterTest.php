<?php

namespace Tests\Unit;

use App\Support\Admin\OperationalRiskPresenter;
use App\Support\OperationalRiskCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalRiskPresenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_builds_summary_and_grouped_sections(): void
    {
        $risks = collect([
            $this->sampleRisk('invoice_overdue:1', OperationalRiskCategory::BILLING, 'critical'),
            $this->sampleRisk('invoice_overdue:2', OperationalRiskCategory::BILLING, 'critical'),
            $this->sampleRisk('telemetry_stale:1', OperationalRiskCategory::INFRASTRUCTURE, 'warning'),
            $this->sampleRisk('subscription_renewal:1', OperationalRiskCategory::RENEWAL, 'warning'),
        ]);

        $center = OperationalRiskPresenter::build($risks);

        $this->assertSame(4, $center['total']);
        $this->assertSame(2, $center['summary']['critical']['count']);
        $this->assertSame(2, $center['summary']['billing']['count']);
        $this->assertSame(1, $center['summary']['infrastructure']['count']);

        $billing = collect($center['sections'])->firstWhere('id', 'billing');
        $this->assertNotNull($billing);
        $this->assertSame(2, $billing['count']);
        $this->assertCount(1, $billing['items']);
        $this->assertSame('bundle', $billing['items'][0]['type']);
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleRisk(string $key, string $category, string $severity): array
    {
        return [
            'key' => $key,
            'category' => $category,
            'severity' => $severity,
            'title' => 'Test '.$key,
            'description' => 'Sample description',
            'recommended_action' => 'Do something',
            'due_at' => null,
            'tenant_id' => 1,
            'project_id' => null,
            'server_id' => null,
            'staff_profile_id' => null,
            'url' => '/example',
            'acknowledged' => false,
        ];
    }
}
