@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<div class="content">
    <h2 class="ttl">会員登録</h2>
    <form class="form-content" action="/register" method="post">
    @csrf
        <div class="form-content__group">
            <label class="group-label" for="name">ユーザー名</label>
            <input class="group-input" type="text" name="name" value="{{ old('name') }}">
        </div>
        @if ($errors->has('name'))
            <ul class="error-messages">
                @foreach ($errors->get('name') as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
        <div class="form-content__group">
            <label class="group-label" for="email">メールアドレス</label>
            <input class="group-input" type="text" name="email" value="{{ old('email') }}">
        </div>
        @if ($errors->has('email'))
            <ul class="error-messages">
                @foreach ($errors->get('email') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
        <div class="form-content__group">
            <label class="group-label" for="password">パスワード</label>
            <input class="group-input" type="password" name="password">
        </div>
        @if ($errors->has('password'))
            <ul class="error-messages">
                @foreach ($errors->get('password') as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
        <div class="form-content__group">
            <label class="group-label" for="password_confirmation">確認パスワード</label>
            <input class="group-input" type="password" name="password_confirmation">
        </div>
        <button class="form-content__button" type="submit">登録</button>
    </form>
    <h2 class="form-sub">アカウントをお持ちの方</h2>
    <a class="form-sub__button" href="/login">ログインはこちら</a>
</div>
@endsection