<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Database\Seeders\DepartmentSeeder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $companies = DB::table('companies')->pluck('id');

        foreach ($companies as $companyId) {
            DepartmentSeeder::seedForCompany($companyId);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-reversible
    }
};
