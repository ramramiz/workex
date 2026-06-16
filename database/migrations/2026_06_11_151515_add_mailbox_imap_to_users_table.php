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
            $table->boolean('mailbox_imap_enabled')->default(false);
            $table->string('mailbox_imap_host')->nullable();
            $table->string('mailbox_imap_port')->default('993');
            $table->string('mailbox_imap_encryption')->default('ssl');
            $table->string('mailbox_imap_username')->nullable();
            $table->text('mailbox_imap_password')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mailbox_imap_enabled',
                'mailbox_imap_host',
                'mailbox_imap_port',
                'mailbox_imap_encryption',
                'mailbox_imap_username',
                'mailbox_imap_password',
            ]);
        });
    }
};
