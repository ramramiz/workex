<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('location')->nullable()->after('client_phone');
            $table->string('business_type')->nullable()->after('location');
            $table->string('service_required')->nullable()->after('source');
            $table->date('preferred_date')->nullable()->after('follow_up_date');
            $table->text('company_details')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'location',
                'business_type',
                'service_required',
                'preferred_date',
                'company_details',
            ]);
        });
    }
};
