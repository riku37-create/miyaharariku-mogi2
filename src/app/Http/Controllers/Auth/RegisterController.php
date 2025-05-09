<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;

class RegisterController extends Controller
{
    public function store(RegisterRequest $request, CreateNewUser $creator)
    {
        $user = $creator->create($request->all());

        Auth::login($user);

        return  redirect()->route('attendance.show');
    }
}
