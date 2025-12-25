@extends('wms.layouts.app')

@section('title', '出荷先詳細')
@section('header_title', '取引先')

@section('content')
<div class="page-head">
    <div class="page-title">出荷先詳細</div>
    <div class="actions">
        <a class="btn" href="{{ route('customers.index') }}">一覧へ</a>
        <a class="btn btn--outline" href="{{ route('customers.edit', $customer) }}">編集</a>
    </div>
</div>

<div class="card">
    <h2 class="card__title">出荷先情報</h2>

    <div class="form-grid">
        <div class="label">出荷先名</div>
        <div>{{ $customer->name }}</div>

        <div class="label">出荷先コード</div>
        <div>{{ $customer->code ?? '—' }}</div>

        <div class="label">郵便番号</div>
        <div>{{ $customer->postal_code ?? '—' }}</div>

        <div class="label">住所</div>
        <div>{{ trim(($customer->address1 ?? '') . ' ' . ($customer->address2 ?? '')) ?: '—' }}</div>

        <div class="label">電話番号</div>
        <div>{{ $customer->phone ?? '—' }}</div>

        <div class="label">メールアドレス</div>
        <div>{{ $customer->email ?? '—' }}</div>

        <div class="label">担当者名</div>
        <div>{{ $customer->contact_name ?? '—' }}</div>

        <div class="label">配送方法</div>
        <div>{{ $customer->shipping_method ?? '—' }}</div>

        <div class="label">備考</div>
        <div>{{ $customer->note ?? '—' }}</div>
    </div>
</div>
@endsection
