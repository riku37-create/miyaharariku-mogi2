<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;

class RegisteredUserController extends Controller
{
    public function store(RegisterRequest $request, CreateNewUser $creator)
    {
        // バリデーション & ユーザー作成
        $user = $creator->create($request->all());

        // 自動ログイン
        Auth::login($user);

        // ロールによってリダイレクト
        return $user->role === 'admin'
            ? redirect()->route('admin.attendances.index')
            : redirect()->route('attendance.show');
    }
}
