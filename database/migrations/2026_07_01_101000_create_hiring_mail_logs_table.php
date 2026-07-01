<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hiring_mail_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('job_application_id')->nullable();
            $table->string('candidate_name');
            $table->string('candidate_email');
            $table->string('vacancy_title');
            $table->string('subject');
            $table->date('interview_date');
            $table->string('interview_time');
            $table->text('interview_venue');
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('job_application_id')->references('id')->on('job_applications')->onDelete('set null');
            $table->foreign('sent_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hiring_mail_logs');
    }
};
