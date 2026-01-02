@extends('wms.layouts.app')

@section('title', '商品を作成する')
@section('header_title', '商品')

@section('content')
<div class="page-head">
    <div class="page-title">商品を作成する</div>
    <div class="actions">
        <a class="btn" href="{{ route('products.index') }}">キャンセル</a>
        <button class="btn btn--primary" form="product-form" type="submit">保存する</button>
    </div>
</div>

<div class="card">
    <h2 class="card__title">商品情報</h2>

    {{-- <form id="product-form" method="POST" action="{{ route('products.store') }}"> --}}
    <form id="product-form" method="POST" action="{{ route('products.store') . '?XDEBUG_TRIGGER=1' }}">
        @csrf

        <div class="form-grid">
            <div class="label">商品名 <span class="badge-required">必須</span></div>
            <div>
                <input class="input" style="width:100%;" name="name" value="{{ old('name') }}">
            </div>

            <div class="label">SKU <span class="badge-required">必須</span></div>
            <div>
                <input class="input" style="width:100%;" name="sku" value="{{ old('sku') }}">
            </div>

            <div class="label">バーコード <span class="badge-optional">任意</span></div>
            <div>
                <input class="input" style="width:100%;" name="barcode" value="{{ old('barcode') }}">
            </div>

            <div class="label">単位 <span class="badge-required">必須</span></div>
            <div>
                <input class="input" style="width:100%;" name="unit" value="{{ old('unit', 'piece') }}">
                <div class="muted" style="margin-top:6px;">例：piece / pair / box など</div>
            </div>

            <div class="label">備考 <span class="badge-optional">任意</span></div>
            <div>
                <textarea class="input" style="width:100%; height:110px;" name="note">{{ old('note') }}</textarea>
            </div>
        </div>
    </form>
</div>
@endsection
