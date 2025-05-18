<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    public function store(RegisterRequest $request, CreateNewUser $creator)
    {
        $user = $creator->create($request->all());

        Auth::login($user);

        event(new Registered($user));

        return  redirect()->route('verification.notice');
    }
}
