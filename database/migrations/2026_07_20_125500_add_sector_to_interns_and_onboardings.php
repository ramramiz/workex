<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('interns', function (Blueprint $table) {
            $table->string('sector')->nullable()->after('designation_id');
        });

        Schema::table('intern_onboardings', function (Blueprint $table) {
            $table->string('sector')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('interns', function (Blueprint $table) {
            $table->dropColumn('sector');
        });

        Schema::table('intern_onboardings', function (Blueprint $table) {
            $table->dropColumn('sector');
        });
    }
};
