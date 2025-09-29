@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/verify.css') }}">
@endsection

@section('content')
    <x-auth_header></x-auth_header>
    <div class="verify-notice">
        <div class="verify-notice__parts">
            <p>登録していただいたメールアドレスに認証メールを送付しました。<br/>メール認証を完了してください。</p>
            {{-- <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="verify-button">
                    認証はこちらから
                </button>
            </form> --}}

            {{-- Mailhogを開く（ローカル開発用） --}}
            <div class="verify-button__parts">
                <button onclick="window.open('http://localhost:8025', '_blank')" type="submit" class="verify-button">
                    認証はこちらから
                </button>
            </div>

            {{-- 再送のリンクで正しいのはこっち↓ --}}
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="resend__link" onclick="window.open('http://localhost:8025', '_blank')">
                    認証メールを再送する
                </button>
            </form>

            {{-- <div class="resend__link">
                <a class="button-submit" href="">認証メールを再送する</a>
            </div> --}}
        </div>
    </div>
@endsection
