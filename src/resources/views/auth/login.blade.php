@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="login-form">
    <h1 class="form-title">ログイン</h1>
    <form class="form-content" action="/login" method="post">
        @csrf
        <div class="form-content__group">
            <label class="group-label" for="email">メールアドレス</label>
            <input class="group-input" type="text" name="email" value="{{ old('email') }}">
        </div>
        @if ($errors->has('email'))
        <div class="form__error">
            <ul>
                @foreach ($errors->get('email') as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <div class="form-content__group">
            <label class="group-label" for="username_or_email">パスワード</label>
            <input class="group-input" type="password" name="password">
        </div>
        @if ($errors->has('password'))
        <div class="form__error">
            <ul>
                @foreach ($errors->get('password') as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <button class="form-content__button" type="submit">ログイン</button>
    </form>
    <h2 class="form-sub">アカウントをお持でない方</h2>
    <a class="form-sub__button" href="/register">登録はこちら</a>
</div>
@endsection