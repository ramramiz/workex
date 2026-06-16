<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->text('completed_work');
            $table->text('pending_work')->nullable();
            $table->text('issues_faced')->nullable();
            $table->text('tomorrow_plan')->nullable();
            $table->string('git_commit_link')->nullable();
            $table->json('attachments')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, rework
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reviewer_comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unique(['user_id', 'date']);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->timestamp('login_time')->nullable();
            $table->timestamp('logout_time')->nullable();
            $table->integer('total_minutes')->default(0);
            $table->string('type')->default('office'); // office, work_from_home, half_day, absent, holiday
            $table->string('status')->default('present'); // present, absent, late, half_day, on_leave
            $table->integer('late_minutes')->default(0);
            $table->boolean('early_logout')->default(false);
            $table->text('notes')->nullable();
            $table->unique(['user_id', 'date']);
            $table->timestamps();
        });

        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('leave_type'); // casual, sick, emergency, work_from_home, half_day
            $table->date('from_date');
            $table->date('to_date');
            $table->decimal('total_days', 4, 1)->default(1);
            $table->text('reason');
            $table->string('status')->default('pending'); // pending, team_leader_approved, approved, rejected
            $table->foreignId('team_leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('team_leader_status')->nullable(); // approved, rejected
            $table->text('team_leader_comment')->nullable();
            $table->timestamp('team_leader_at')->nullable();
            $table->foreignId('hr_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('hr_status')->nullable(); // approved, rejected
            $table->text('hr_comment')->nullable();
            $table->timestamp('hr_at')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaves');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('daily_reports');
    }
};
