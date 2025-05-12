@extends('layouts.app', ['hideHeader' => true])

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
<div class="content">
    <h2 class="ttl">ログイン</h2>
    <form class="form-content" action="/login" method="post">
        @csrf
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
            <label class="group-label" for="username_or_email">パスワード</label>
            <input class="group-input" type="password" name="password">
        </div>
        @if ($errors->has('password'))
            <ul class="error-messages">
                @foreach ($errors->get('password') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
        <button class="form-content__button" type="submit">ログイン</button>
    </form>
    <h2 class="form-sub">アカウントをお持でない方</h2>
    <a class="form-sub__button" href="/register">登録はこちら</a>
</div>
@endsection