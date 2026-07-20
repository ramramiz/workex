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
        Schema::create('employee_onboardings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable(); // linked after HR approval
            $table->string('token')->unique();
            $table->string('status')->default('pending'); // pending, submitted, completed

            // Core placeholder fields filled by HR on link generation
            $table->string('name');
            $table->string('email');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('designation_id')->nullable();
            $table->unsignedBigInteger('team_leader_id')->nullable(); // Reporting Manager
            $table->unsignedBigInteger('role_id'); // Selected User Role ID
            $table->decimal('salary', 10, 2)->default(0);
            $table->date('joining_date')->nullable();
            $table->string('sector')->nullable(); // Sector details

            // Section 1: Personal Info
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('nationality')->nullable();
            $table->string('aadhaar_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('driving_license_number')->nullable();

            // Section 2: Contact Details
            $table->string('phone')->nullable(); // mobile
            $table->string('alternate_mobile')->nullable();
            $table->string('personal_email')->nullable();
            $table->text('current_address')->nullable();
            $table->string('current_pin_code')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('permanent_pin_code')->nullable();
            $table->boolean('same_as_current')->default(false);

            // Section 3: Emergency Contact Details
            $table->string('emergency_contact_person')->nullable();
            $table->string('emergency_relationship')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->string('emergency_alternate_phone')->nullable();
            $table->text('emergency_address')->nullable();


            // Section 5: Educational Qualifications
            $table->json('education_qualifications')->nullable(); // SSLC, Plus Two, Diploma, Degree, PG, Other

            // Section 6: Professional Details
            $table->string('total_experience')->nullable();
            $table->string('prev_employer')->nullable();
            $table->string('prev_designation')->nullable();
            $table->string('prev_duration')->nullable();
            $table->text('prev_reason_for_leaving')->nullable();
            $table->json('skills')->nullable();
            $table->string('skills_other')->nullable();

            // Section 7: Bank Details
            $table->string('bank_account_holder')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc')->nullable();
            $table->string('bank_upi')->nullable();

            // Section 8: PF / ESI Details
            $table->string('uan_number')->nullable();
            $table->string('pf_number')->nullable();
            $table->string('esi_number')->nullable();

            // Section 10: Official Access Requirements (HR Completed)
            $table->json('company_access_requirements')->nullable();
            $table->string('company_access_other')->nullable();

            // Section 11: IT Asset Requirement (HR Completed)
            $table->json('assets_issued')->nullable();
            $table->text('assets_remarks')->nullable();

            // Section 12: Medical Information (Optional)
            $table->text('medical_condition')->nullable();
            $table->text('medical_allergies')->nullable();
            $table->text('medical_medication')->nullable();

            // Section 13: Declaration By Employee
            $table->boolean('declaration_accepted')->default(false);
            $table->boolean('code_of_conduct_accepted')->default(false);

            // HR Use Only Fields
            $table->string('approved_by')->nullable();
            $table->string('hr_signature')->nullable();
            $table->string('management_signature')->nullable();
            $table->string('employment_type')->nullable(); // permanent, probation, contract, internship
            $table->string('official_email')->nullable();
            $table->string('employee_code')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_onboardings');
    }
};
