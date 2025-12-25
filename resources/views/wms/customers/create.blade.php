@extends('wms.layouts.app')

@section('title', '出荷先を作成する')
@section('header_title', '取引先')

@section('content')
<div class="page-head">
    <div class="page-title">出荷先を作成する</div>
    <div class="actions">
        <a class="btn" href="{{ route('customers.index') }}">キャンセル</a>
        <button class="btn btn--primary" form="customer-form" type="submit">保存する</button>
    </div>
</div>

<div class="card">
    <h2 class="card__title">出荷先情報</h2>

    <form id="customer-form" method="POST" action="{{ route('customers.store') }}">
        @csrf

        <div class="form-grid">
            <div class="label">出荷先名 <span class="badge-required">必須</span></div>
            <div>
                <input class="input" style="width:100%;" name="name" value="{{ old('name') }}">
            </div>

            <div class="label">出荷先コード <span class="badge-optional">任意</span></div>
            <div>
                <input class="input" style="width:100%;" name="code" value="{{ old('code') }}">
            </div>

            <div class="label">郵便番号 <span class="badge-optional">任意</span></div>
            <div>
                <input class="input" style="width:100%;" name="postal_code" value="{{ old('postal_code') }}">
            </div>

            <div class="label">住所1 <span class="badge-optional">任意</span></div>
            <div>
                <input class="input" style="width:100%;" name="address1" value="{{ old('address1') }}">
            </div>

            <div class="label">住所2 <span class="badge-optional">任意</span></div>
            <div>
                <input class="input" style="width:100%;" name="address2" value="{{ old('address2') }}">
            </div>

            <div class="label">電話番号 <span class="badge-optional">任意</span></div>
            <div>
                <input class="input" style="width:100%;" name="phone" value="{{ old('phone') }}">
            </div>

            <div class="label">メールアドレス <span class="badge-optional">任意</span></div>
            <div>
                <input class="input" style="width:100%;" name="email" value="{{ old('email') }}">
            </div>

            <div class="label">担当者名 <span class="badge-optional">任意</span></div>
            <div>
                <input class="input" style="width:100%;" name="contact_name" value="{{ old('contact_name') }}">
            </div>

            <div class="label">配送方法 <span class="badge-optional">任意</span></div>
            <div>
                <input class="input" style="width:100%;" name="shipping_method" value="{{ old('shipping_method') }}" placeholder="例：ヤマト / 佐川 / 日本郵便">
            </div>

            <div class="label">備考 <span class="badge-optional">任意</span></div>
            <div>
                <textarea class="input" style="width:100%; height:110px;" name="note">{{ old('note') }}</textarea>
            </div>
        </div>
    </form>
</div>
@endsection
