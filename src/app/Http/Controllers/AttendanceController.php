<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;
use App\Models\CorrectionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 勤怠登録画面(一般ユーザー)の表示
    public function show()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $nowTime = Carbon::now()->toTimeString();

        // 今日の勤怠データ取得 or 新規作成用 null
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

        return view('user.attendance_clock', compact('today', 'nowTime', 'attendance', 'isOnBreak'));
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->toDateString(),
            'clock_in' => $now->toTimeString(),
        ]);

        return redirect()->route('attendance.show');
    }

    public function clockOut(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', Carbon::today()->toDateString())
            ->firstOrFail();

        $clockIn = Carbon::parse($attendance->clock_in);
        $clockOut = Carbon::now(); // 現在時刻を退勤時間とする

        $diffInSeconds = $clockIn->diffInSeconds($clockOut);

        $attendance->update([
            'clock_out' => $clockOut->toTimeString(),
            'total_clock' => $diffInSeconds,
        ]);

        return redirect()->route('attendance.show');
    }

    public function breakStart(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', Carbon::today()->toDateString())
            ->firstOrFail();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->toTimeString(),
        ]);

        return redirect()->route('attendance.show');
    }

    public function breakEnd(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', Carbon::today()->toDateString())
            ->firstOrFail();

        $break = $attendance->breaks()->latest()->first();
        if ($break && !$break->break_end) {
            $break->update([
                'break_end' => Carbon::now()->toTimeString(),
            ]);
        }

        // 休憩の合計時間（分）を算出
        $totalBreakMinutes = $attendance->breaks->sum(function ($b) {
            if (!$b->break_start || !$b->break_end) {
                return 0;
            }
            return \Carbon\Carbon::parse($b->break_start)->diffInSeconds(\Carbon\Carbon::parse($b->break_end));
        });

        // attendancesテーブルに保存
        $attendance->update([
            'total_break' => $totalBreakMinutes,
        ]);

        return redirect()->route('attendance.show');
    }


    public function index()
    {
        $user = User::find(Auth::id());
        $attendances = $user->attendances()->with('breaks')->orderByDesc('date')->get();
        return view('user.index', compact('attendances'));
    }

    public function detail($id)
    {
        $attendance = Attendance::with(['user','breaks'])->findOrFail($id);

        $hasPendingRequest = $attendance->correctionRequests()
        ->where('status', 'pending')
        ->exists();

        return view('user.detail', compact('attendance', 'hasPendingRequest'));
    }

    public function requestCorrection(Request $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        // 1. 修正申請（ヘッダー）を作成
        $correctionRequest = CorrectionRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $id,
            'status' => 'pending', // 承認待ち
            'reason' => $request->input('reason'),
        ]);

        // 2. 出退勤の修正を登録（空でなければ）
        if ($request->filled('clock_in') || $request->filled('clock_out')) {
            CorrectionDetail::create([
                'correction_request_id' => $correctionRequest->id,
                'correctable_id' => $attendance->id,
                'correctable_type' => Attendance::class,
                'corrected_start' => $request->filled('clock_in') ? Carbon::parse($request->input('clock_in')) : null,
                'corrected_end' => $request->filled('clock_out') ? Carbon::parse($request->input('clock_out')) : null,
            ]);
        }

        // 3. 休憩の修正を登録（繰り返し処理）
        foreach ($request->input('breaks', []) as $breakInput) {
            if (!empty($breakInput['start']) || !empty($breakInput['end'])) {
                $breakTime = BreakTime::find($breakInput['id']);
                if ($breakTime) {
                    CorrectionDetail::create([
                        'correction_request_id' => $correctionRequest->id,
                        'correctable_id' => $breakTime->id,
                        'correctable_type' => BreakTime::class,
                        'corrected_start' => !empty($breakInput['start']) ? Carbon::parse($breakInput['start']) : null,
                        'corrected_end' => !empty($breakInput['end']) ? Carbon::parse($breakInput['end']) : null,
                    ]);
                }
            }
        }

        $hasPendingRequest = $attendance->correctionRequests()
        ->where('status', 'pending')
        ->exists();

        return view('user.detail', compact('attendance', 'hasPendingRequest'));
    }
}
