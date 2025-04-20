<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        $attendances = Attendance::with('user')
            ->where('date', $date)
            ->get();

        return view('admin.attendances.index', [
            'attendances' => $attendances,
            'date' => $date,
        ]);
    }
}
