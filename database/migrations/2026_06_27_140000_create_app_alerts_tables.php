<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('heading');
            $table->text('title');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('app_alert_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_alert_id')->constrained('app_alerts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_alert_users');
        Schema::dropIfExists('app_alerts');
    }
};
