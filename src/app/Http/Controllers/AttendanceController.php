<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 勤怠登録画面(一般ユーザー)の表示
    public function show()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $nowTime = Carbon::now()->format('H:i');
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        $latestBreak = null;
        $isOnBreak = false;

        if ($attendance) {
            $latestBreak = $attendance->breaks()->latest()->first();
            // 休憩中かどうかを判定（break_endがnull＝まだ休憩から戻ってない）
            if ($latestBreak && is_null($latestBreak->break_end)) {
                $isOnBreak = true;
            }
        }

        return view('attendance.stamp', compact('today', 'nowTime', 'attendance', 'isOnBreak'));
    }

    public function clockIn()
    {
        $user = Auth::user();
        $clockIn = Carbon::now();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $clockIn->toDateString(),
            'clock_in' => $clockIn->toDateTimeString(),
        ]);

        return redirect()->route('attendance.show');
    }

    public function clockOut()
    {
        //日付このままだとまたいだ場合むり
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', Carbon::today()->toDateString())
            ->firstOrFail();

        $clockIn = $attendance->clock_in;
        $clockOut = Carbon::now(); // 現在時刻を退勤時間とする
        $diffInSeconds = $clockIn->diffInSeconds($clockOut);

        $attendance->update([
            'clock_out' => $clockOut->toDateTimeString(),
            'total_clock' => $diffInSeconds,
        ]);

        return redirect()->route('attendance.show');
    }

    public function breakStart()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', Carbon::today()->toDateString())
            ->firstOrFail();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->toDateTimeString(),
        ]);

        return redirect()->route('attendance.show');
    }

    public function breakEnd()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', Carbon::today()->toDateString())
            ->firstOrFail();
        $break = $attendance->breaks()->latest()->first();

        if ($break && !$break->break_end) {
            $break->update([
                'break_end' => Carbon::now()->toDateTimeString(),
            ]);
        }

        // 休憩の合計時間（分）を算出
        $totalBreakMinutes = $attendance->breaks->sum(function ($b) {
            if (!$b->break_start || !$b->break_end) {
                return 0;
            }
            return Carbon::parse($b->break_start)->diffInSeconds(Carbon::parse($b->break_end));
        });

        // attendancesテーブルに保存
        $attendance->update([
            'total_break' => $totalBreakMinutes,
        ]);

        return redirect()->route('attendance.show');
    }
}
