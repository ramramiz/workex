<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternOnboarding extends Model
{
    protected $table = 'intern_onboardings';

    protected $fillable = [
        'intern_id',
        'token',
        'status',
        'sector',
        
        // Section 1
        'preferred_name',
        'gender',
        'dob',
        'blood_group',
        'aadhaar_number',
        'alternate_mobile',
        'current_address',
        'pin_code',

        // Section 2
        'college_name',
        'university_board',
        'course',
        'branch_specialization',
        'current_semester_year',
        'college_roll_number',
        'expected_completion_year',

        // Section 3
        'parent_name',
        'parent_relationship',
        'parent_phone',
        'parent_occupation',
        'parent_address',

        // Section 4
        'emergency_contact_person',
        'emergency_relationship',
        'emergency_phone',
        'emergency_alternate_phone',

        // Section 5
        'internship_type',
        'internship_type_other',
        'internship_mode',
        'internship_duration',
        'internship_duration_other',
        'areas_of_interest',
        'areas_of_interest_other',

        // Section 6
        'programming_languages',
        'programming_languages_other',
        'design_tools',
        'design_tools_other',
        'completed_projects',

        // Section 10
        'learning_expectations',
        'career_goal',

        // Section 11
        'declaration_accepted',
        'signature_name',
        'signature_date',

        // Section 8
        'company_access_requirements',
        'company_access_other',

        // Section 9
        'assets_issued',
        'assets_remarks',

        // Office Use
        'office_use_domain',
        'office_use_mentor_assigned',
        'office_use_certificate_eligible',
        'office_use_hr_signature',
        'office_use_mentor_signature',
        'office_use_management_approval',
    ];

    protected $casts = [
        'dob' => 'date',
        'signature_date' => 'date',
        'declaration_accepted' => 'boolean',
        'areas_of_interest' => 'array',
        'programming_languages' => 'array',
        'design_tools' => 'array',
        'company_access_requirements' => 'array',
        'assets_issued' => 'array',
        'office_use_certificate_eligible' => 'boolean',
    ];

    public function intern()
    {
        return $this->belongsTo(Intern::class);
    }
}
