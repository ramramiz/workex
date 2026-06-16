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
        Schema::table('users', function (Blueprint $table) {
            $table->string('mailbox_smtp_host')->nullable();
            $table->string('mailbox_smtp_port')->default('465');
            $table->string('mailbox_smtp_encryption')->default('ssl');
            $table->string('mailbox_smtp_username')->nullable();
            $table->text('mailbox_smtp_password')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mailbox_smtp_host',
                'mailbox_smtp_port',
                'mailbox_smtp_encryption',
                'mailbox_smtp_username',
                'mailbox_smtp_password',
            ]);
        });
    }
};
