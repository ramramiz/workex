<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Holiday;
use App\Models\Setting;
use Carbon\Carbon;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::orderBy('date', 'asc')->get();
        $weekStartDay = Setting::get('week_start_day', 'mon');
        $weekOffDaysSetting = Setting::get('week_off_days', 'sun');
        $weekOffDays = explode(',', $weekOffDaysSetting);

        return view('settings.holidays.index', compact('holidays', 'weekStartDay', 'weekOffDays'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:national,optional,company',
        ]);

        $date = Carbon::parse($validated['date']);
        $markedCount = 0;

        if ($request->boolean('repeat_yearly_nth_day')) {
            $year = $date->year;
            $dayOfWeek = $date->dayOfWeek; // 0 (Sun) - 6 (Sat)
            $dayOfMonth = $date->day;
            $nth = (int) ceil($dayOfMonth / 7);

            for ($m = 1; $m <= 12; $m++) {
                $targetDate = self::getNthWeekdayOfMonth($year, $m, $dayOfWeek, $nth);
                if ($targetDate) {
                    Holiday::updateOrCreate(
                        ['date' => $targetDate->toDateString()],
                        [
                            'name' => $validated['name'],
                            'type' => $validated['type'],
                        ]
                    );
                    $markedCount++;
                }
            }

            \App\Models\ActivityLog::log('holiday_marked_recurring', "Marked recurring holiday '{$validated['name']}' for all {$nth} occurrence of weekday index {$dayOfWeek} in {$year}");
            $message = "Holiday marked for all {$markedCount} months of {$year}!";
        } else {
            // Verify single date uniqueness
            $request->validate(['date' => 'unique:holidays,date']);
            
            $holiday = Holiday::create($validated);
            \App\Models\ActivityLog::log('holiday_marked', "Marked holiday '{$holiday->name}' on {$holiday->date->format('Y-m-d')}");
            $message = 'Holiday marked successfully!';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('settings.holidays.index')->with('success', $message);
    }

    private static function getNthWeekdayOfMonth(int $year, int $month, int $dayOfWeek, int $nth): ?Carbon
    {
        $start = Carbon::create($year, $month, 1);
        $count = 0;
        for ($d = 0; $d < 31; $d++) {
            $date = $start->copy()->addDays($d);
            if ($date->month != $month) {
                break;
            }
            if ($date->dayOfWeek === $dayOfWeek) {
                $count++;
                if ($count === $nth) {
                    return $date;
                }
            }
        }
        return null;
    }

    public function destroy(Holiday $holiday)
    {
        $holidayName = $holiday->name;
        $holidayDate = $holiday->date->format('Y-m-d');
        
        $holiday->delete();

        \App\Models\ActivityLog::log('holiday_removed', "Removed holiday '{$holidayName}' from {$holidayDate}");

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Holiday removed successfully!'
            ]);
        }

        return redirect()->route('settings.holidays.index')->with('success', 'Holiday removed successfully!');
    }
}
