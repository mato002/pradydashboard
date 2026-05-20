<?php

namespace Tests\Feature;

use App\Domain\Hr\HrOverview;
use App\Models\HrDepartment;
use App\Models\Project;
use App\Models\Server;
use App\Models\StaffAssignment;
use App\Models\StaffDocument;
use App\Models\StaffProfile;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\HrDepartmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HrOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HrDepartmentSeeder::class);
    }

    public function test_department_can_be_created(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('hr.departments.store'), [
                'name' => 'Custom Ops',
                'code' => 'custom_ops',
                'status' => 'active',
            ])
            ->assertRedirect(route('hr.departments.index'));

        $this->assertDatabaseHas('hr_departments', [
            'name' => 'Custom Ops',
            'code' => 'custom_ops',
        ]);
    }

    public function test_staff_profile_can_be_created(): void
    {
        $user = User::factory()->create();
        $dept = HrDepartment::query()->where('code', 'technology')->first();

        $this->actingAs($user)
            ->post(route('hr.staff.store'), [
                'full_name' => 'Jane Doe',
                'hr_department_id' => $dept->id,
                'employment_type' => 'full_time',
                'status' => 'active',
                'email' => 'jane@prady.test',
            ])
            ->assertRedirect();

        $staff = StaffProfile::query()->where('full_name', 'Jane Doe')->first();
        $this->assertNotNull($staff);
        $this->assertNotEmpty($staff->staff_number);
    }

    public function test_staff_can_be_assigned_to_project_tenant_and_server(): void
    {
        $user = User::factory()->create();
        $staff = StaffProfile::query()->create([
            'full_name' => 'John Ops',
            'employment_type' => 'full_time',
            'status' => 'active',
        ]);
        $project = Project::query()->create(['name' => 'MFI App', 'domain' => 'mfi.test']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Mattare MFI',
            'status' => 'active',
        ]);
        $server = Server::query()->create(['name' => 'Hostinger VPS', 'status' => 'online']);

        $this->actingAs($user)->post(route('hr.staff.assignments.store', $staff), [
            'assignable_type' => Project::class,
            'assignable_id' => $project->id,
            'role_on_assignment' => 'Project Lead',
            'status' => 'active',
        ])->assertRedirect();

        $this->actingAs($user)->post(route('hr.staff.assignments.store', $staff), [
            'assignable_type' => Tenant::class,
            'assignable_id' => $tenant->id,
            'role_on_assignment' => 'Account Manager',
            'status' => 'active',
        ])->assertRedirect();

        $this->actingAs($user)->post(route('hr.staff.assignments.store', $staff), [
            'assignable_type' => Server::class,
            'assignable_id' => $server->id,
            'role_on_assignment' => 'Server Owner',
            'status' => 'active',
        ])->assertRedirect();

        $this->assertSame(3, StaffAssignment::query()->where('staff_profile_id', $staff->id)->count());
    }

    public function test_staff_document_upload_and_download(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $staff = StaffProfile::query()->create([
            'full_name' => 'Doc User',
            'employment_type' => 'contract',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('hr.staff.documents.store', $staff), [
                'title' => 'Employment Contract',
                'document_type' => 'contract',
                'file' => UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('hr.staff.show', ['staff' => $staff, 'tab' => 'documents']));

        $doc = StaffDocument::query()->where('staff_profile_id', $staff->id)->first();
        $this->assertNotNull($doc);
        Storage::disk('local')->assertExists($doc->file_path);

        $this->actingAs($user)
            ->get(route('hr.staff.documents.download', [$staff, $doc]))
            ->assertOk();
    }

    public function test_hr_dashboard_counts_use_real_data(): void
    {
        $dept = HrDepartment::query()->where('code', 'sales')->first();
        $active = StaffProfile::query()->create([
            'full_name' => 'Active One',
            'hr_department_id' => $dept->id,
            'employment_type' => 'full_time',
            'status' => 'active',
        ]);
        StaffProfile::query()->create([
            'full_name' => 'Exited One',
            'employment_type' => 'full_time',
            'status' => 'exited',
            'end_date' => now()->subMonth(),
        ]);

        $project = Project::query()->create(['name' => 'P', 'domain' => 'p.test']);
        StaffAssignment::query()->create([
            'staff_profile_id' => $active->id,
            'assignable_type' => Project::class,
            'assignable_id' => $project->id,
            'status' => 'active',
        ]);

        $metrics = app(HrOverview::class)->metrics();

        $this->assertSame(1, $metrics['active_staff']);
        $this->assertSame(1, $metrics['exited_staff']);
        $this->assertSame(1, $metrics['open_assignments']);
        $this->assertSame(0, $metrics['staff_without_assignments']);
        $this->assertGreaterThanOrEqual(8, $metrics['by_department']->count());
    }

    public function test_hr_index_page_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('hr.index'))
            ->assertOk()
            ->assertSee(__('Active staff'));
    }
}
