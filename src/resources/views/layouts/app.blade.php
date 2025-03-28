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
            <div class="header-inner__title">
                <a class="title-a" href="">
                    <img class="title-image" src="{{ asset('images/logo.svg') }}">
                </a>
            </div>
            <nav class="header-inner__nav">
                <ul class="nav-list">
                    <li class="nav-item"><a class="nav-item__a" href="">勤怠一覧</a></li>
                    <li class="nav-item"><a class="nav-item__a" href="">スタッフ一覧</a></li>
                    <li class="nav-item"><a class="nav-item__a" href="">申請一覧</a></li>
                    <li class="nav-item">
                        @if (Auth::check())
                        <form action="/logout" method="post">
                            @csrf
                            <button class="nav-item__button">ログアウト</button>
                        </form>
                        @else
                            <a class="nav-item__a" href="/login">ログイン</a>
                        @endif
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    @yield('content')
</body>