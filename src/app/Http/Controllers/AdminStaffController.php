<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\User;
use App\Models\Attendance;


class AdminStaffController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')->get();

        return view('admin.staff.index', compact('users'));
    }

    public function detail(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 指定月（フォーマット: YYYY-MM）を受け取る。なければ今月。
        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date')
            ->get();

        return view('admin.staff.show', compact('user', 'attendances', 'month'));
    }

    public function exportCsv($id, Request $request): StreamedResponse
    {
        $month = $request->input('month') ?? now()->format('Y-m');

        $user = User::findOrFail($id);
        $attendances = Attendance::with('user')
            ->where('user_id', $id)
            ->whereBetween('date', [
                Carbon::parse($month)->startOfMonth(),
                Carbon::parse($month)->endOfMonth()
            ])->get();

        $filename = "{$user->name}_{$month}_attendances.csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        ];

        $columns = ['日付', '出勤', '退勤', '休憩時間', '実働時間', '総合時間'];

        $callback = function () use ($attendances, $columns) {
            $handle = fopen('php://output', 'w');
            // ヘッダー
            fputcsv($handle, $columns);

            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    $attendance->date->format('Y/m/d'),
                    optional($attendance->clock_in)->format('H:i') ?? '-',
                    optional($attendance->clock_out)->format('H:i') ?? '-',
                    $attendance->formatted_total_break,
                    $attendance->formatted_total_clock,
                    $attendance->formatted_total_raw,
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }
}
