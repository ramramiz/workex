<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('total_minutes')->default(0);
            $table->integer('productive_minutes')->default(0);
            $table->integer('break_minutes')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->text('device_info')->nullable();
            $table->text('work_done')->nullable();
            $table->string('status')->default('active'); // active, ended
            $table->unique(['user_id', 'date']);
            $table->timestamps();
        });

        Schema::create('work_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_breaks');
        Schema::dropIfExists('work_sessions');
    }
};
