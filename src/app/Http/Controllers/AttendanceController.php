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

        return view('attendance.stamp', compact('today', 'attendance', 'isOnBreak'));
    }

    public function clockIn()
    {
        $user = Auth::user();
        $clockIn = Carbon::now();
        $today = Carbon::today();

        if(Attendance::where('user_id', $user->id)->where('date', $today)->exists()){
            return redirect()->route('attendance.show');
        }
        Attendance::create([
            'user_id' => $user->id,
            'date' => $clockIn->toDateString(),
            'clock_in' => $clockIn->toDateTimeString(),
        ]);

        return redirect()->route('attendance.show');
    }

    public function clockOut()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        // いらないかも
        if(!$attendance || !$attendance->clock_in){
            return redirect()->back();
        }

        $clockIn = $attendance->clock_in;
        $clockOut = now();
        $diffInMinutes = $clockIn->diffInMinutes($clockOut);
        $diffInSeconds = $clockIn->diffInSeconds($clockOut);

        if($diffInMinutes < 5){
            return redirect()->back()->with('error', '出勤から５分経過後に退勤できます');
        }

        $attendance->update([
            'clock_out' => $clockOut->toDateTimeString(),
            'total_clock' => $diffInSeconds,
        ]);

        return redirect()->route('attendance.show');
    }

    public function breakStart()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        $now = Carbon::now();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => $now->toDateTimeString(),
        ]);

        return redirect()->route('attendance.show');
    }

    public function breakEnd()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        $break = $attendance->breaks()->latest()->first();

        if ($break && !$break->break_end) {
            $breakStart = $break->break_start;
            $now = Carbon::now();

            if($now->diffInMinutes($breakStart) < 5){
                return redirect()->route('attendance.show')->with('error', '休憩開始から5分経過後に休憩終了できます');
            }

            $break->update([
                'break_end' => $now->toDateTimeString(),
            ]);
        }

        // 休憩の合計時間（秒単位）を算出
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