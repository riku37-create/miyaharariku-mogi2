<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AdminAttendanceRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Attendance;

class AdminAttendanceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $dateInput = $request->input('date', Carbon::today()->toDateString());
        $date = Carbon::parse($dateInput);

        $attendances = Attendance::with('user')
            ->where('date', $date)
            ->get();

        return view('admin.attendances.index', [
            'attendances' => $attendances,
            'date' => $date,
        ]);
    }

    public function detail($id)
    {
        $attendance = Attendance::with(['user','breaks'])->findOrFail($id);

        return view('admin.attendances.show', compact('attendance'));
    }

    public function update(AdminAttendanceRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        DB::transaction(function () use ($request, $attendance) {
            // 出勤・退勤時間更新
            $attendance->clock_in = Carbon::parse($attendance->date)->setTimeFromTimeString($request->input('clock_in'));
            $attendance->clock_out = Carbon::parse($attendance->date)->setTimeFromTimeString($request->input('clock_out'));
            $attendance->save();

            // 休憩時間更新（全部削除→再登録）
            $attendance->breaks()->delete();

            foreach ($request->input('breaks', []) as $breakInput) {
                $breakStart = Carbon::parse($attendance->date)->setTimeFromTimeString($breakInput['start']);
                $breakEnd = Carbon::parse($attendance->date)->setTimeFromTimeString($breakInput['end']);

                $attendance->breaks()->create([
                    'break_start' => $breakStart,
                    'break_end' => $breakEnd,
                ]);
            }

            $attendance->load('breaks');

            // 勤務時間（出勤から退勤までの秒数）
            $diffInSeconds = $attendance->clock_in->diffInSeconds($attendance->clock_out);

            // 休憩時間（breaksテーブルの合計秒数）
            $totalBreakSeconds = $attendance->breaks->sum(function ($b) {
                if (!$b->break_start || !$b->break_end) {
                    return 0;
                }
                return Carbon::parse($b->break_start)->diffInSeconds(Carbon::parse($b->break_end));
            });

            // attendancesテーブルに更新
            $attendance->update([
                'total_clock' => $diffInSeconds,
                'total_break' => $totalBreakSeconds,
            ]);
        });

        return redirect()->route('admin.attendances.index');
    }

}
