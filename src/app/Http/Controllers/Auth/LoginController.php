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
        // リクエストURLに応じてビューを切り替える
        if ($request->is('admin/login')) {
            return view('auth.admin_login');
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            return $user->role === 'admin'
                ? redirect()->route('admin.attendances.index')
                : redirect()->route('attendance.show');
        }

        return back()->withErrors([
            'email' => '認証に失敗しました。',
        ]);
    }
}
