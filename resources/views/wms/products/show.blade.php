@extends('wms.layouts.app')

@section('title', '商品詳細')
@section('header_title', '商品')

@section('content')
<div class="page-head">
    <div class="page-title">商品詳細</div>
    <div class="actions">
        <a class="btn" href="{{ route('products.index') }}">一覧へ</a>
        <a class="btn btn--outline" href="{{ route('products.edit', $product) }}">編集</a>
    </div>
</div>

<div class="card">
    <h2 class="card__title">商品情報</h2>

    <div class="form-grid">
        <div class="label">商品名</div>
        <div>{{ $product->name }}</div>

        <div class="label">SKU</div>
        <div>{{ $product->sku }}</div>

        <div class="label">バーコード</div>
        <div>{{ $product->barcode ?? '—' }}</div>

        <div class="label">単位</div>
        <div>{{ $product->unit }}</div>

        <div class="label">備考</div>
        <div>{{ $product->note ?? '—' }}</div>
    </div>
</div>
@endsection
