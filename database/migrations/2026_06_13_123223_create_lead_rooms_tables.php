<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('lead_room_user', function (Blueprint $table) {
            $table->foreignId('lead_room_id')->constrained('lead_rooms')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->primary(['lead_room_id', 'user_id']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('lead_room_id')->nullable()->after('client_id')->constrained('lead_rooms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['lead_room_id']);
            $table->dropColumn('lead_room_id');
        });

        Schema::dropIfExists('lead_room_user');
        Schema::dropIfExists('lead_rooms');
    }
};
