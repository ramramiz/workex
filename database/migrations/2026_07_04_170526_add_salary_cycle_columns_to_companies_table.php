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
            $table->string('salary_cycle')->default('monthly')->after('gst');
            $table->unsignedTinyInteger('salary_payment_date')->default(5)->after('salary_cycle');
            $table->unsignedTinyInteger('salary_payment_date_1')->default(15)->after('salary_payment_date');
            $table->unsignedTinyInteger('salary_payment_date_2')->default(30)->after('salary_payment_date_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'salary_cycle',
                'salary_payment_date',
                'salary_payment_date_1',
                'salary_payment_date_2',
            ]);
        });
    }
};
