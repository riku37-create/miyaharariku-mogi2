<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = \App\Models\Attendance::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $date = now()->startOfMonth()->addDays(rand(0, now()->daysInMonth - 1));
        $clockIn = $date->copy()->setTime(rand(8, 9), rand(0, 59));
        $clockOut = $clockIn->copy()->addHours(rand(7, 9));
        $totalClock = $clockIn->diffInSeconds($clockOut);

        return [
            'date' => $date->toDateString(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'total_clock' => $totalClock,
        ];
    }

    public function noBreaks()
    {
        return $this->afterCreating(function ($attendance) {
            // 何も生成しない
        });
    }

    //休憩も生成
    public function configure()
    {
        return $this->afterCreating(function ($attendance) {
            $clockIn = Carbon::parse($attendance->clock_in);
            $clockOut = Carbon::parse($attendance->clock_out);

            $breaks = [];
            $usedRanges = [];

            $breakCount = rand(1, 3); // 1〜3件の休憩を生成

            for ($i = 0; $i < $breakCount; $i++) {
                $maxAttempts = 10;
                $attempts = 0;

                do {
                    $breakStart = $clockIn->copy()->addMinutes(rand(60, 180));
                    $breakEnd = (clone $breakStart)->addMinutes(rand(15, 45));

                    // 終了時間が退勤を超えないように制限
                    if ($breakEnd->greaterThan($clockOut)) {
                        $breakEnd = $clockOut->copy()->subMinutes(rand(5, 10));
                        if ($breakEnd->lessThan($breakStart)) {
                            continue; // 無効な範囲なのでやり直し
                        }
                    }

                    // 重複チェック
                    $overlap = false;
                    foreach ($usedRanges as [$start, $end]) {
                        if (
                            ($breakStart->between($start, $end)) ||
                            ($breakEnd->between($start, $end)) ||
                            ($start->between($breakStart, $breakEnd)) // 相互チェック
                        ) {
                            $overlap = true;
                            break;
                        }
                    }

                    $attempts++;

                } while ($overlap && $attempts < $maxAttempts);

                if (!$overlap) {
                    $usedRanges[] = [$breakStart, $breakEnd];
                    $breaks[] = [
                        'attendance_id' => $attendance->id,
                        'break_start' => $breakStart,
                        'break_end' => $breakEnd,
                    ];
                }
            }

            foreach ($breaks as $break) {
                \App\Models\BreakTime::create($break);
            }

            // total_break 再計算
            $totalBreak = collect($breaks)->sum(function ($b) {
                return Carbon::parse($b['break_start'])->diffInSeconds(Carbon::parse($b['break_end']));
            });

            $attendance->update(['total_break' => $totalBreak]);
        });
    }
}