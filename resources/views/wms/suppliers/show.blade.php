@extends('wms.layouts.app')

@section('title', '仕入先詳細')
@section('header_title', '取引先')

@section('content')
<div class="page-head">
    <div class="page-title">仕入先詳細</div>
    <div class="actions">
        <a class="btn" href="{{ route('suppliers.index') }}">一覧へ</a>
        <a class="btn btn--outline" href="{{ route('suppliers.edit', $supplier) }}">編集</a>
    </div>
</div>

<div class="card">
    <h2 class="card__title">仕入先情報</h2>

    <div class="form-grid">
        <div class="label">仕入先名</div>
        <div>{{ $supplier->name }}</div>

        <div class="label">仕入先コード</div>
        <div>{{ $supplier->code ?? '—' }}</div>

        <div class="label">郵便番号</div>
        <div>{{ $supplier->postal_code ?? '—' }}</div>

        <div class="label">住所</div>
        <div>{{ trim(($supplier->address1 ?? '') . ' ' . ($supplier->address2 ?? '')) ?: '—' }}</div>

        <div class="label">電話番号</div>
        <div>{{ $supplier->phone ?? '—' }}</div>

        <div class="label">メールアドレス</div>
        <div>{{ $supplier->email ?? '—' }}</div>

        <div class="label">担当者名</div>
        <div>{{ $supplier->contact_name ?? '—' }}</div>

        <div class="label">備考</div>
        <div>{{ $supplier->note ?? '—' }}</div>
    </div>
</div>
@endsection
