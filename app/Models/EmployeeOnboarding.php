<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeOnboarding extends Model
{
    protected $table = 'employee_onboardings';

    protected $fillable = [
        'employee_id',
        'token',
        'status',
        
        // HR generated fields
        'name',
        'email',
        'department_id',
        'designation_id',
        'team_leader_id',
        'role_id',
        'salary',
        'joining_date',
        'sector',

        // Section 1
        'gender',
        'dob',
        'blood_group',
        'marital_status',
        'nationality',
        'aadhaar_number',
        'pan_number',
        'passport_number',
        'driving_license_number',

        // Section 2
        'phone',
        'alternate_mobile',
        'personal_email',
        'current_address',
        'current_pin_code',
        'permanent_address',
        'permanent_pin_code',
        'same_as_current',

        // Section 3
        'emergency_contact_person',
        'emergency_relationship',
        'emergency_phone',
        'emergency_alternate_phone',
        'emergency_address',

        // Section 5
        'education_qualifications',

        // Section 6
        'total_experience',
        'prev_employer',
        'prev_designation',
        'prev_duration',
        'prev_reason_for_leaving',
        'skills',
        'skills_other',

        // Section 7
        'bank_account_holder',
        'bank_name',
        'bank_branch',
        'bank_account_number',
        'bank_ifsc',
        'bank_upi',

        // Section 8
        'uan_number',
        'pf_number',
        'esi_number',

        // Section 10
        'company_access_requirements',
        'company_access_other',

        // Section 11
        'assets_issued',
        'assets_remarks',

        // Section 12
        'medical_condition',
        'medical_allergies',
        'medical_medication',

        // Section 13
        'declaration_accepted',
        'code_of_conduct_accepted',

        // HR Use only
        'approved_by',
        'hr_signature',
        'management_signature',
        'employment_type',
        'official_email',
        'employee_code',
    ];

    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
        'declaration_accepted' => 'boolean',
        'code_of_conduct_accepted' => 'boolean',
        'same_as_current' => 'boolean',
        'education_qualifications' => 'array',
        'skills' => 'array',
        'company_access_requirements' => 'array',
        'assets_issued' => 'array',
        'salary' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function teamLeader()
    {
        return $this->belongsTo(User::class, 'team_leader_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
