@extends('layouts.app', ['hideHeader' => true])

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/admin_login.css') }}">
@endsection

@section('content')
<div class="content">
    <h2 class="ttl">管理者ログイン</h2>
    <form class="form-content" action="/login" method="post">
        @csrf
        <div class="form-content__group">
            <label class="group-label" for="email">メールアドレス</label>
            <input class="group-input" type="text" name="email" value="{{ old('email') }}">
        </div>
        @if ($errors->has('email'))
        <div class="form__error">
            <ul class="error-messages">
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
            <ul class="error-messages">
                @foreach ($errors->get('password') as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <button class="form-content__button" type="submit">管理者ログインする</button>
    </form>
</div>
@endsection