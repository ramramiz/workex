<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('status')->default('active'); // active, suspended
            $table->timestamps();
        });

        $tables = [
            'users',
            'departments',
            'clients',
            'lead_rooms',
            'projects',
            'expenses',
            'bugs',
            'meetings',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableCol) {
                $tableCol->unsignedBigInteger('company_id')->nullable()->after('id');
                $tableCol->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            });
        }

        DB::table('companies')->insert([
            'id' => 1,
            'name' => 'WorkeX Default',
            'email' => 'admin@workmonitor.com',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($tables as $table) {
            DB::table($table)->update(['company_id' => 1]);
        }
    }

    public function down(): void
    {
        $tables = [
            'users',
            'departments',
            'clients',
            'lead_rooms',
            'projects',
            'expenses',
            'bugs',
            'meetings',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableCol) {
                $tableCol->dropForeign(['company_id']);
                $tableCol->dropColumn('company_id');
            });
        }

        Schema::dropIfExists('companies');
    }
};
