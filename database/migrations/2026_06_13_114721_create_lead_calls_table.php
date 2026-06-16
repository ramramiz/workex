<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('telecaller_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('call_date_time');
            $table->string('status'); // Connected, Not Connected, Busy, Switched Off
            $table->text('customer_response')->nullable();
            $table->string('next_action')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_calls');
    }
};
