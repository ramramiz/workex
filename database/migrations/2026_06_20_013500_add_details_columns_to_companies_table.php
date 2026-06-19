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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('auth_person_name')->nullable()->after('email');
            $table->string('auth_person_email')->nullable()->after('auth_person_name');
            $table->string('phone')->nullable()->after('auth_person_email');
            $table->text('address')->nullable()->after('phone');
            $table->string('gst')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'auth_person_name',
                'auth_person_email',
                'phone',
                'address',
                'gst'
            ]);
        });
    }
};
