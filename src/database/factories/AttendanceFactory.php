<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $date = Carbon::today()->subDays(rand(0, 30));
        $clockIn = $date->copy()->addHours(rand(8, 10));
        $clockOut = $clockIn->copy()->addHours(rand(7, 9));
        $totalClock = $clockIn->diffInSeconds($clockOut);

        return [
            'date' => $date->toDateString(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'total_clock' => $totalClock,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($attendance) {
            // total_break を計算しなおして update
            $totalBreak = $attendance->breaks->sum(function ($b) {
                return Carbon::parse($b->break_start)->diffInSeconds(Carbon::parse($b->break_end));
            });

            $attendance->update(['total_break' => $totalBreak]);
        });
    }

    public function withBreaks($count = 2)
    {
        return $this->has(
            BreakTime::factory()->count($count),
            'breaks' // リレーション名
        );
    }
}
