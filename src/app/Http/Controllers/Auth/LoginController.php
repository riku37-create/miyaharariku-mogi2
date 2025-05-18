<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    public function create(Request $request)
    {
        if ($request->is('admin/login')) {
            return view('auth.admin_login');
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $request->authenticate();

        // セキュリティ対策
        $request->session()->regenerate();

        $user = Auth::user();

        if ($request->routeIs('admin.login.post') && $user->role !== 'admin') {
            Auth::logout();
            return back()->withErrors(['email' => '管理者アカウントでログインしてください']);
        }
        
        if ($request->routeIs('login.post') && $user->role === 'admin') {
            Auth::logout();
            return back()->withErrors(['email' => '一般ユーザーとしてログインしてください']);
        }

        return $user->role === 'admin'
            ? redirect()->route('admin.attendances.index')
            : redirect()->route('attendance.show');
        }
}
