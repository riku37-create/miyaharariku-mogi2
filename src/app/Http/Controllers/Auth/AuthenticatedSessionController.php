<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            return $user->role === 'admin'
                ? redirect()->route('admin.attendance.list')
                : redirect()->route('user.attendance.clock');
        }

        return back()->withErrors([
            'email' => '認証に失敗しました。',
        ]);
    }
}
