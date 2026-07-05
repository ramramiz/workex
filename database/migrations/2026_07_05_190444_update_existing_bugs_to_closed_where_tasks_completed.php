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
        // Find all bugs whose associated task is completed
        $bugs = \App\Models\Bug::whereHas('task', function($q) {
            $q->where('status', 'completed');
        })->get();

        foreach ($bugs as $bug) {
            $bug->update(['status' => 'closed']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
