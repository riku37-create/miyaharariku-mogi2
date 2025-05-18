<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequest;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;
use App\Models\RequestAttendance;
use App\Models\RequestBreakTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = User::find(Auth::id());

        $monthInput = $request->input('month', Carbon::now()->format('Y-m'));
        $month = Carbon::parse($monthInput);

        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date')
            ->get();
        return view('attendance.history.index', compact('attendances', 'month'));
    }

    public function detail($id)
    {
        $attendance = Attendance::with(['user','breaks'])->findOrFail($id);

        $correctionRequest = CorrectionRequest::where('attendance_id', $attendance->id)
        ->where('user_id', auth()->id())
        ->where('status', 'pending')
        ->with(['requestAttendance', 'requestBreakTimes'])
        ->first();

        $hasPendingRequest = $correctionRequest !== null;

        return view('attendance.history.show', compact('attendance', 'correctionRequest', 'hasPendingRequest'));
    }

    public function storeCorrectionRequest(AttendanceRequest $request, Attendance $attendance)
    {
        DB::transaction(function () use ($request, $attendance) {
            $correctionRequest = CorrectionRequest::create([
                'user_id' => auth()->id(),
                'attendance_id' => $attendance->id,
                'status' => 'pending',
                'reason' => $request->reason,
            ]);

            $clockIn = Carbon::parse($attendance->date)->setTimeFromTimeString($request->input('clock_in'));
            $clockOut = Carbon::parse($attendance->date)->setTimeFromTimeString($request->input('clock_out'));

            RequestAttendance::create([
                'attendance_id' => $attendance->id,
                'correction_request_id' => $correctionRequest->id,
                'original_clock_in' => $attendance->clock_in,
                'original_clock_out' => $attendance->clock_out,
                'corrected_clock_in' => $clockIn,
                'corrected_clock_out' => $clockOut,
            ]);

            foreach ($request->input('breaks', []) as $breakInput) {
                if (empty($breakInput['start']) && empty($breakInput['end'])) {
                    continue;
                }

                // 片方だけ空ならバリデーションエラー
                if (empty($breakInput['start']) || empty($breakInput['end'])) {
                    throw new \Exception('休憩時間の開始・終了の両方を入力してください');
                }

                $correctedStart = Carbon::parse($attendance->date)->setTimeFromTimeString($breakInput['start']);
                $correctedEnd = Carbon::parse($attendance->date)->setTimeFromTimeString($breakInput['end']);

                if (!empty($breakInput['id'])) {
                    $originalBreak = BreakTime::find($breakInput['id']);

                    RequestBreakTime::create([
                        'break_time_id' => $originalBreak->id,
                        'correction_request_id' => $correctionRequest->id,
                        'original_break_start' => $originalBreak->break_start,
                        'original_break_end' => $originalBreak->break_end,
                        'corrected_break_start' => $correctedStart,
                        'corrected_break_end' => $correctedEnd,
                    ]);
                } else {
                    // 新規追加休憩
                    RequestBreakTime::create([
                        'break_time_id' => null,
                        'correction_request_id' => $correctionRequest->id,
                        'original_break_start' => null,
                        'original_break_end' => null,
                        'corrected_break_start' => $correctedStart,
                        'corrected_break_end' => $correctedEnd,
                    ]);
                }
            }
        });

        return redirect()->route('staff.attendances.index');
    }
}
