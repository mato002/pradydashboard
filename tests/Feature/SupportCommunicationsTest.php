<?php

namespace Tests\Feature;

use App\Domain\Support\SupportOperationsSummary;
use App\Models\Project;
use App\Models\StaffProfile;
use App\Models\SupportTicket;
use App\Models\SupportTicketComment;
use App\Models\Tenant;
use App\Models\TenantCommunication;
use App\Models\TenantNotice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportCommunicationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Tenant, 2: Project, 3: StaffProfile}
     */
    private function fixtures(): array
    {
        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'Support App', 'domain' => 'support.test']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Support Co',
            'status' => 'active',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
        ]);
        $staff = StaffProfile::query()->create([
            'full_name' => 'Alex Support',
            'employment_type' => 'full_time',
            'status' => 'active',
        ]);

        return [$user, $tenant, $project, $staff];
    }

    public function test_ticket_created_from_tenant_page(): void
    {
        [$user, $tenant] = $this->fixtures();

        $this->actingAs($user)
            ->post(route('tenants.support-tickets.store', $tenant), [
                'subject' => 'Cannot login',
                'description' => 'Users locked out after update',
                'category' => 'account_access',
                'priority' => 'high',
                'source' => 'phone',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('support_tickets', [
            'tenant_id' => $tenant->id,
            'subject' => 'Cannot login',
            'status' => 'open',
        ]);
    }

    public function test_ticket_comment_added(): void
    {
        [$user, $tenant] = $this->fixtures();
        $ticket = SupportTicket::query()->create([
            'tenant_id' => $tenant->id,
            'project_id' => $tenant->project_id,
            'subject' => 'API errors',
            'category' => 'bug',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'email',
            'opened_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('tenants.support-tickets.comments.store', [$tenant, $ticket]), [
                'message' => 'Investigating logs',
                'comment_type' => 'internal_note',
                'visibility' => 'internal',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('support_ticket_comments', [
            'support_ticket_id' => $ticket->id,
            'message' => 'Investigating logs',
        ]);
    }

    public function test_ticket_assigned_to_staff(): void
    {
        [$user, $tenant,, $staff] = $this->fixtures();
        $ticket = SupportTicket::query()->create([
            'tenant_id' => $tenant->id,
            'project_id' => $tenant->project_id,
            'subject' => 'Billing question',
            'category' => 'billing',
            'priority' => 'low',
            'status' => 'open',
            'source' => 'email',
            'opened_at' => now(),
        ]);

        $this->actingAs($user)
            ->put(route('tenants.support-tickets.update', [$tenant, $ticket]), [
                'subject' => 'Billing question',
                'category' => 'billing',
                'priority' => 'low',
                'source' => 'email',
                'status' => 'in_progress',
                'assigned_staff_id' => $staff->id,
            ])
            ->assertRedirect();

        $this->assertSame($staff->id, $ticket->fresh()->assigned_staff_id);
    }

    public function test_ticket_resolved(): void
    {
        [$user, $tenant] = $this->fixtures();
        $ticket = SupportTicket::query()->create([
            'tenant_id' => $tenant->id,
            'project_id' => $tenant->project_id,
            'subject' => 'Training request',
            'category' => 'training',
            'priority' => 'medium',
            'status' => 'in_progress',
            'source' => 'internal',
            'opened_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('tenants.support-tickets.resolve', [$tenant, $ticket]), [
                'resolution_notes' => 'Session completed',
            ])
            ->assertRedirect();

        $ticket->refresh();
        $this->assertSame('resolved', $ticket->status);
        $this->assertNotNull($ticket->resolved_at);
        $this->assertTrue(
            SupportTicketComment::query()
                ->where('support_ticket_id', $ticket->id)
                ->where('comment_type', 'resolution')
                ->exists()
        );
    }

    public function test_communication_logged_with_follow_up(): void
    {
        [$user, $tenant,, $staff] = $this->fixtures();

        $this->actingAs($user)
            ->post(route('tenants.communications.store', $tenant), [
                'channel' => 'phone',
                'direction' => 'inbound',
                'message' => 'Discussed renewal pricing',
                'communication_date' => now()->format('Y-m-d H:i:s'),
                'follow_up_required' => true,
                'follow_up_date' => now()->addDays(3)->toDateString(),
                'staff_profile_id' => $staff->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tenant_communications', [
            'tenant_id' => $tenant->id,
            'follow_up_required' => true,
            'status' => 'pending_follow_up',
        ]);
    }

    public function test_overdue_follow_up_appears_in_dashboard_summary(): void
    {
        [$user, $tenant] = $this->fixtures();

        TenantCommunication::query()->create([
            'tenant_id' => $tenant->id,
            'channel' => 'email',
            'direction' => 'outbound',
            'message' => 'Send contract copy',
            'communication_date' => now()->subDays(5),
            'follow_up_required' => true,
            'follow_up_date' => Carbon::yesterday()->toDateString(),
            'status' => 'pending_follow_up',
        ]);

        $summary = app(SupportOperationsSummary::class)->platform();
        $this->assertGreaterThanOrEqual(1, $summary['overdue_follow_ups']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('Support & communications'));
    }

    public function test_tenant_notice_created(): void
    {
        [$user, $tenant] = $this->fixtures();

        $this->actingAs($user)
            ->post(route('tenants.notices.store', $tenant), [
                'notice_type' => 'maintenance',
                'title' => 'Scheduled maintenance',
                'message' => 'Window Sunday 2am–4am EAT',
                'severity' => 'warning',
                'status' => 'draft',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tenant_notices', [
            'tenant_id' => $tenant->id,
            'title' => 'Scheduled maintenance',
            'status' => 'draft',
        ]);
    }
}
