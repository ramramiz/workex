<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Setting::updateOrCreate(
            ['key' => 'company_auth_person_name'],
            [
                'value' => 'Authorized Person Name',
                'group' => 'company',
                'label' => 'Authorized Person Name',
                'type' => 'text',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'company_auth_person_email'],
            [
                'value' => 'auth@company.com',
                'group' => 'company',
                'label' => 'Authorized Person Email',
                'type' => 'text',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::whereIn('key', ['company_auth_person_name', 'company_auth_person_email'])->delete();
    }
};
