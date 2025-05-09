<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\CorrectionRequest;


class AdminCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending'); // デフォルトは承認待ち
        // 承認待ちの申請を取得
        $pendingRequests = CorrectionRequest::with(['user', 'attendance'])
            ->when(in_array($status, ['pending', 'approved']), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view("admin.correction_requests.index", compact('pendingRequests'));
    }

    public function show($attendance_correct_request)
    {
        $request = CorrectionRequest::with(['attendance', 'user', 'requestAttendance', 'requestBreakTimes'])->findOrFail($attendance_correct_request);

        return view('admin.correction_requests.show', compact('request'));
    }

    public function approve($id)
    {
        DB::transaction(function () use ($id) {
            $request = CorrectionRequest::with(['requestAttendance', 'requestBreakTimes'])->findOrFail($id);
            $attendance = Attendance::findOrFail($request->attendance_id);

            // 出退勤更新
            $attendance->update([
                'clock_in' => $request->requestAttendance->corrected_clock_in,
                'clock_out' => $request->requestAttendance->corrected_clock_out,
            ]);

            // 休憩時間更新（すべて削除→追加）
            $attendance->breaks()->delete();

            foreach ($request->requestBreakTimes as $break) {
                $attendance->breaks()->create([
                    'break_start' => $break->corrected_break_start,
                    'break_end' => $break->corrected_break_end,
                ]);
            }

            $request->update(['status' => 'approved']);
        });

        return redirect()->route('admin.correction_requests.index')->with('success', '承認しました。');
    }

    public function reject($id)
    {
        $request = CorrectionRequest::findOrFail($id);
        $request->update(['status' => 'rejected']);

        return redirect()->route('admin.correction_requests.index');
    }
}
