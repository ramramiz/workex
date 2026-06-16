<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'tasks',
            'leads',
            'invoices',
            'quotations',
            'support_tickets',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableCol) {
                $tableCol->unsignedBigInteger('company_id')->nullable()->after('id');
                $tableCol->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            });

            // Backfill existing records to default company id = 1
            DB::table($table)->update(['company_id' => 1]);
        }
    }

    public function down(): void
    {
        $tables = [
            'tasks',
            'leads',
            'invoices',
            'quotations',
            'support_tickets',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableCol) {
                $tableCol->dropForeign(['company_id']);
                $tableCol->dropColumn('company_id');
            });
        }
    }
};
