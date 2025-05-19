<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;


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
}
