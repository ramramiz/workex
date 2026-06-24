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
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('team_leader_approved')->default(false)->after('completed_link');
            $table->foreignId('team_leader_approved_by')->nullable()->after('team_leader_approved')->constrained('users')->nullOnDelete();
            $table->timestamp('team_leader_approved_at')->nullable()->after('team_leader_approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['team_leader_approved_by']);
            $table->dropColumn(['team_leader_approved', 'team_leader_approved_by', 'team_leader_approved_at']);
        });
    }
};
