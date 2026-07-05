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
        // Get all project IDs that have a related ProjectAmc entry where start_date is not null
        $amcProjectIds = \DB::table('project_amcs')
            ->whereNotNull('start_date')
            ->pluck('project_id');

        if ($amcProjectIds->isNotEmpty()) {
            \DB::table('projects')
                ->whereIn('id', $amcProjectIds)
                ->update(['status' => 'completed_started_amc']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status to completed for projects that have status completed_started_amc
        \DB::table('projects')
            ->where('status', 'completed_started_amc')
            ->update(['status' => 'completed']);
    }
};
