<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::loginView(function () {
            // 現在のリクエストパスでビューを分岐
            if (Request::is('admin/login')) {
                return view('auth.admin_login');
            }

            return view('auth.login');
        });

        $this->app->singleton('path.redirect', function () {
            return function () {
                $user = auth()->user();

                return $user->role === 'admin'
                    ? route('admin.attendances.index') // 管理者
                    : route('attendance.show'); // 一般ユーザー
            };
        });
    }
}
