<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('name');
            $table->date('dob')->nullable()->after('gender');
            $table->string('qualification')->nullable()->after('dob');
            $table->string('state')->nullable()->after('email');
            $table->string('district')->nullable()->after('state');
            $table->string('home_town')->nullable()->after('district');
            $table->string('experience_years')->nullable()->after('home_town');
            $table->string('salary_expectation')->nullable()->after('experience_years');
            $table->string('ready_to_relocate')->nullable()->after('salary_expectation');
            $table->string('linkedin_id')->nullable()->after('ready_to_relocate');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'dob',
                'qualification',
                'state',
                'district',
                'home_town',
                'experience_years',
                'salary_expectation',
                'ready_to_relocate',
                'linkedin_id'
            ]);
        });
    }
};
