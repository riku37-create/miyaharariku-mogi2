@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('no-header', true)

@section('content')
<div class="mail">
    <h1 class="mail-ttl">メール認証が必要です</h1>
    <p class="mail-txt">登録したメールアドレスに認証用のリンクを送信しました。</p>
    <p class="mail-txt">メールを確認し、リンクをクリックして認証を完了してください。</p>
    <a href="https://mailtrap.io/home">認証はこちらから</a>
    @if (session('status') == 'verification-link-sent')
    <div class="alert alert-success">
        新しい認証リンクが送信されました。メールを確認してください。
    </div>
    @endif
    <form class="mail-form" method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="form-btn">認証メールを再送信</button>
    </form>
</div>
@endsection