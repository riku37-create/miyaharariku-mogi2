<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header-inner">
            <div class="header-inner__ttl">
                <img class="ttl-img" src="{{ asset('logo.svg') }}">
            </div>
            @auth
                @if (!isset($hideHeader) && auth()->user()->isAdmin())
                    @include('layouts.header.admin')
                @elseif (!isset($hideHeader))
                    @include('layouts.header.user')
                @endif
            @endauth
        </div>
    </header>
    <div class="content-main">
        @yield('content')
    </div>
</body>