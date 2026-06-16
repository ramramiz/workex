<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_follow_ups', function (Blueprint $table) {
            $table->time('follow_up_time')->nullable()->after('next_follow_up');
            $table->string('status')->default('pending')->after('follow_up_time'); // pending, completed, missed
            $table->boolean('reminder_sent')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('lead_follow_ups', function (Blueprint $table) {
            $table->dropColumn(['follow_up_time', 'status', 'reminder_sent']);
        });
    }
};
