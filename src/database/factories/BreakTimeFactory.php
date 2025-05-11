<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        // ここではまだ attendance_id がないので仮の時間を返す
        return [
            'break_start' => now(),
            'break_end' => now()->addMinutes(30),
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function ($breakTime) {
            if ($breakTime->attendance) {
                $clockIn = Carbon::parse($breakTime->attendance->clock_in);
                $clockOut = Carbon::parse($breakTime->attendance->clock_out);

                // clock_in から clock_out の間の適当な時間に設定
                $breakStart = $clockIn->copy()->addMinutes(rand(60, 180));
                $breakEnd = (clone $breakStart)->addMinutes(rand(15, 45));

                // 退勤時間を超えないように調整
                if ($breakEnd->greaterThan($clockOut)) {
                    $breakEnd = $clockOut->copy()->subMinutes(rand(5, 10));
                }

                $breakTime->break_start = $breakStart;
                $breakTime->break_end = $breakEnd;
            }
        });
    }
}
