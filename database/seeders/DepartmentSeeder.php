<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        self::seedForCompany(1);
    }

    public static function seedForCompany(int $companyId): void
    {
        $departments = [
            ['name' => 'Administration', 'designations' => ['Admin', 'Office Assistant', 'Operations Manager']],
            ['name' => 'Management', 'designations' => ['CEO', 'CTO', 'Project Manager', 'Product Manager']],
            ['name' => 'Development', 'designations' => ['Senior Developer', 'Junior Developer', 'Full Stack Developer', 'Backend Developer', 'Frontend Developer', 'Mobile Developer']],
            ['name' => 'Design', 'designations' => ['UI/UX Designer', 'Graphic Designer', 'Web Designer']],
            ['name' => 'QA & Testing', 'designations' => ['QA Engineer', 'Test Lead', 'Manual Tester', 'Automation Tester']],
            ['name' => 'DevOps', 'designations' => ['DevOps Engineer', 'System Admin', 'Cloud Engineer']],
            ['name' => 'Sales & Marketing', 'designations' => ['Sales Manager', 'Business Development Executive', 'Digital Marketing Executive']],
            ['name' => 'Human Resources', 'designations' => ['HR Manager', 'HR Executive', 'Recruiter']],
            ['name' => 'Accounts', 'designations' => ['Accounts Manager', 'Accountant', 'Finance Executive']],
        ];

        foreach ($departments as $deptData) {
            $deptId = DB::table('departments')
                ->where('company_id', $companyId)
                ->where('name', $deptData['name'])
                ->value('id');

            if (!$deptId) {
                $deptId = DB::table('departments')->insertGetId([
                    'company_id' => $companyId,
                    'name' => $deptData['name'],
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($deptData['designations'] as $desig) {
                $exists = DB::table('designations')
                    ->where('department_id', $deptId)
                    ->where('name', $desig)
                    ->exists();

                if (!$exists) {
                    DB::table('designations')->insert([
                        'department_id' => $deptId,
                        'name' => $desig,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
