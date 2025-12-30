@extends('wms.layouts.app')

@section('title', '在庫一覧')
@section('header_title', '在庫')

@section('content')
<div class="page-head">
    <div class="page-title">在庫</div>
    <div class="actions">
        <a class="btn btn--outline" href="#">エクスポート</a>
    </div>
</div>

<div class="card">
    <div class="toolbar">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <input class="input" type="text" name="keyword" value="{{ request('keyword') }}"
                   placeholder="検索（SKU / 商品名 / バーコード）">

            <input class="input" type="text" name="location" value="{{ request('location') }}"
                   placeholder="ロケーション（例：A-01）" style="width:220px;">

            <button class="btn btn--outline" type="submit">絞り込み</button>
            <a class="btn" href="{{ route('stocks.index') }}">リセット</a>
        </form>

        <div style="margin-left:auto" class="muted">
            {{ $stocks->total() }}件
        </div>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th>商品</th>
            <th style="width:160px;">SKU</th>
            <th style="width:220px;">バーコード</th>
            <th style="width:160px;">ロケーション</th>
            <th style="width:130px; text-align:right;">現在庫</th>
            <th style="width:130px; text-align:right;">取置</th>
            <th style="width:130px; text-align:right;">引当可能</th>
        </tr>
        </thead>
        <tbody>
        @forelse($stocks as $stock)
            @php
                $onHand = (int)$stock->on_hand_qty;
                $reserved = (int)$stock->reserved_qty;
                $available = max(0, $onHand - $reserved);
            @endphp
            <tr>
                <td>{{ $stock->product->name ?? '—' }}</td>
                <td>{{ $stock->product->sku ?? '—' }}</td>
                <td>{{ $stock->product->barcode ?? '—' }}</td>
                <td>
                    {{ $stock->location->code ?? '—' }}
                    @if(!empty($stock->location->name))
                        <div class="muted" style="margin-top:4px;">{{ $stock->location->name }}</div>
                    @endif
                </td>
                <td style="text-align:right; font-weight:800;">{{ $onHand }}</td>
                <td style="text-align:right;">{{ $reserved }}</td>
                <td style="text-align:right;">{{ $available }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="muted">在庫データがありません（入庫を実行するとここに出ます）</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">
        {{ $stocks->links() }}
    </div>
</div>
@endsection
