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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('domain_provider')->nullable()->after('url');
            $table->date('domain_valid_till')->nullable()->after('domain_provider');
            $table->unsignedBigInteger('hosting_provider_id')->nullable()->after('domain_valid_till');
            $table->date('hosting_valid_till')->nullable()->after('hosting_provider_id');

            $table->foreign('hosting_provider_id')->references('id')->on('hosting_providers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['hosting_provider_id']);
            $table->dropColumn(['domain_provider', 'domain_valid_till', 'hosting_provider_id', 'hosting_valid_till']);
        });
    }
};
