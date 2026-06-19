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
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'sqlite') {
            Schema::table('lead_room_work_sessions', function (Blueprint $table) {
                $table->dropForeign(['lead_room_id']);
            });
        }

        Schema::table('lead_room_work_sessions', function (Blueprint $table) {
            $table->foreignId('lead_room_id')->nullable()->change();
        });

        if ($driver !== 'sqlite') {
            Schema::table('lead_room_work_sessions', function (Blueprint $table) {
                $table->foreign('lead_room_id')->references('id')->on('lead_rooms')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'sqlite') {
            Schema::table('lead_room_work_sessions', function (Blueprint $table) {
                $table->dropForeign(['lead_room_id']);
            });
        }

        Schema::table('lead_room_work_sessions', function (Blueprint $table) {
            $table->foreignId('lead_room_id')->nullable(false)->change();
        });

        if ($driver !== 'sqlite') {
            Schema::table('lead_room_work_sessions', function (Blueprint $table) {
                $table->foreign('lead_room_id')->references('id')->on('lead_rooms')->onDelete('cascade');
            });
        }
    }
};
