@extends('wms.layouts.app')

@section('title', '在庫引当済み詳細')
@section('header_title', '出荷')

@section('content')
<div class="page-head">
    <div class="page-title">在庫引当済み #{{ $shipment_plan->id }}</div>

    <div class="actions">
        <a class="btn" href="{{ route('allocated-shipments.index') }}">一覧へ</a>

        <a class="btn btn--outline" href="{{ route('allocated-shipments.invoice', $shipment_plan) }}" target="_blank">納品書を表示</a>
        <a class="btn btn--outline" href="{{ route('allocated-shipments.label', $shipment_plan) }}" target="_blank">送り状を表示</a>

        <form method="POST" action="{{ route('allocated-shipments.start-picking', $shipment_plan) }}">
            @csrf
            <button class="btn btn--primary" onclick="return confirm('ピッキングを開始しますか？')">ピッキング開始</button>
        </form>
    </div>
</div>

<div class="card" style="margin-bottom:14px;">
    <h2 class="card__title">ヘッダ</h2>
    <div class="form-grid">
        <div class="label">出荷先</div>
        <div>{{ $shipment_plan->customer->name }}</div>

        <div class="label">出荷予定日</div>
        <div>{{ optional($shipment_plan->planned_ship_date)->format('Y-m-d') ?? '—' }}</div>

        <div class="label">状態</div>
        <div>在庫引当済み</div>

        <div class="label">備考</div>
        <div>{{ $shipment_plan->note ?? '—' }}</div>
    </div>
</div>

<div class="card">
    <h2 class="card__title">明細</h2>

    <table class="table">
        <thead>
        <tr>
            <th>商品</th>
            <th style="width:120px; text-align:right;">予定数</th>
            <th style="width:160px;">SKU</th>
            <th style="width:220px;">バーコード</th>
        </tr>
        </thead>
        <tbody>
        @foreach($shipment_plan->lines as $line)
            <tr>
                <td>{{ $line->product->name ?? '—' }}</td>
                <td style="text-align:right; font-weight:800;">{{ $line->planned_qty }}</td>
                <td>{{ $line->product->sku ?? '—' }}</td>
                <td>{{ $line->product->barcode ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
