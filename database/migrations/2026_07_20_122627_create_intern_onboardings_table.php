<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('intern_onboardings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('intern_id');
            $table->string('token')->unique();
            $table->string('status')->default('pending'); // pending, submitted, completed

            // Section 1: Personal Info
            $table->string('preferred_name')->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('aadhaar_number')->nullable();
            $table->string('alternate_mobile')->nullable();
            $table->text('current_address')->nullable();
            $table->string('pin_code')->nullable();

            // Section 2: Educational Info
            $table->string('college_name')->nullable();
            $table->string('university_board')->nullable();
            $table->string('course')->nullable();
            $table->string('branch_specialization')->nullable();
            $table->string('current_semester_year')->nullable();
            $table->string('college_roll_number')->nullable();
            $table->string('expected_completion_year')->nullable();

            // Section 3: Parent/Guardian Details
            $table->string('parent_name')->nullable();
            $table->string('parent_relationship')->nullable();
            $table->string('parent_phone')->nullable();
            $table->string('parent_occupation')->nullable();
            $table->text('parent_address')->nullable();

            // Section 4: Emergency Contact Details
            $table->string('emergency_contact_person')->nullable();
            $table->string('emergency_relationship')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->string('emergency_alternate_phone')->nullable();

            // Section 5: Internship Details (Confirm/select details)
            $table->string('internship_type')->nullable();
            $table->string('internship_type_other')->nullable();
            $table->string('internship_mode')->nullable();
            $table->string('internship_duration')->nullable();
            $table->string('internship_duration_other')->nullable();
            $table->json('areas_of_interest')->nullable();
            $table->string('areas_of_interest_other')->nullable();

            // Section 6: Technical Skills
            $table->json('programming_languages')->nullable();
            $table->string('programming_languages_other')->nullable();
            $table->json('design_tools')->nullable();
            $table->string('design_tools_other')->nullable();
            $table->text('completed_projects')->nullable();

            // Section 10: Learning Objectives
            $table->text('learning_expectations')->nullable();
            $table->text('career_goal')->nullable();

            // Section 11: Intern Declaration
            $table->boolean('declaration_accepted')->default(false);
            $table->string('signature_name')->nullable();
            $table->date('signature_date')->nullable();

            // Section 8: Access Requirements (HR Completed)
            $table->json('company_access_requirements')->nullable();
            $table->string('company_access_other')->nullable();

            // Section 9: Asset Requirement (HR Completed)
            $table->json('assets_issued')->nullable();
            $table->text('assets_remarks')->nullable();

            // Office Use Only
            $table->string('office_use_domain')->nullable();
            $table->string('office_use_mentor_assigned')->nullable();
            $table->boolean('office_use_certificate_eligible')->nullable();
            $table->string('office_use_hr_signature')->nullable();
            $table->string('office_use_mentor_signature')->nullable();
            $table->string('office_use_management_approval')->nullable();

            $table->timestamps();

            $table->foreign('intern_id')->references('id')->on('interns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intern_onboardings');
    }
};
