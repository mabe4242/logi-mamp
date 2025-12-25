@extends('wms.layouts.app')

@section('title', '商品一覧')
@section('header_title', '商品')

@section('content')
<div class="page-head">
    <div class="page-title">商品</div>
    <div class="actions">
        <a class="btn btn--outline" href="#">エクスポート</a>
        <a class="btn btn--outline" href="#">インポート</a>
        <a class="btn btn--primary" href="{{ route('products.create') }}">商品を作成する</a>
    </div>
</div>

<div class="card">
    <div class="toolbar">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <input class="input" type="text" name="keyword" value="{{ request('keyword') }}" placeholder="検索（SKU / 商品名）">
            <button class="btn btn--outline" type="submit">絞り込み</button>
            <a class="btn" href="{{ route('products.index') }}">リセット</a>
        </form>

        <div style="margin-left:auto" class="muted">
            {{ $products->total() }}件
        </div>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th style="width:80px;">ID</th>
            <th>SKU</th>
            <th>商品名</th>
            <th style="width:140px;">単位</th>
            <th style="width:180px;">操作</th>
        </tr>
        </thead>
        <tbody>
        @forelse($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->sku }}</td>
                <td>
                    <a href="{{ route('products.show', $product) }}" style="color: var(--blue); font-weight: 800;">
                        {{ $product->name }}
                    </a>
                </td>
                <td>{{ $product->unit }}</td>
                <td>
                    <a class="btn btn--outline" href="{{ route('products.edit', $product) }}">編集</a>
                    <a class="btn" href="{{ route('products.show', $product) }}">詳細</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="muted">データがありません</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top: 12px;">
        {{ $products->links() }}
    </div>
</div>
@endsection
