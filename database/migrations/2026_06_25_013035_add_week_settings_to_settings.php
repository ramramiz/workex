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
            ['key' => 'week_start_day'],
            [
                'value' => 'mon',
                'group' => 'work',
                'label' => 'Week Starting Day',
                'type' => 'text',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'week_off_days'],
            [
                'value' => 'sun',
                'group' => 'work',
                'label' => 'Weekly Off Days',
                'type' => 'text',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::whereIn('key', ['week_start_day', 'week_off_days'])->delete();
    }
};
