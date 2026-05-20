<?php

namespace Database\Seeders;

use App\Models\HrDepartment;
use Illuminate\Database\Seeder;

class HrDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Technology', 'code' => 'technology'],
            ['name' => 'Sales', 'code' => 'sales'],
            ['name' => 'Support', 'code' => 'support'],
            ['name' => 'Finance', 'code' => 'finance'],
            ['name' => 'Operations', 'code' => 'operations'],
            ['name' => 'Management', 'code' => 'management'],
            ['name' => 'Legal / Compliance', 'code' => 'legal_compliance'],
            ['name' => 'Other', 'code' => 'other'],
        ];

        foreach ($departments as $dept) {
            HrDepartment::query()->updateOrCreate(
                ['code' => $dept['code']],
                [
                    'name' => $dept['name'],
                    'status' => 'active',
                ]
            );
        }
    }
}
