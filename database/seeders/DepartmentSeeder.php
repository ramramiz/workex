<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Designation;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
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
            $dept = Department::updateOrCreate(
                ['name' => $deptData['name']],
                ['status' => 'active']
            );

            foreach ($deptData['designations'] as $desig) {
                Designation::updateOrCreate(
                    ['name' => $desig, 'department_id' => $dept->id],
                    ['status' => 'active']
                );
            }
        }
    }
}
