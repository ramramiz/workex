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
        Schema::table('project_amcs', function (Blueprint $table) {
            $table->string('alert_phone')->nullable()->after('remarks');
            $table->string('alert_email')->nullable()->after('alert_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_amcs', function (Blueprint $table) {
            $table->dropColumn(['alert_phone', 'alert_email']);
        });
    }
};
