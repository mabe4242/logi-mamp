@extends('wms.layouts.app')

@section('title', 'ピッキング')
@section('header_title', '出荷')

@section('content')
<div class="page-head">
    <div class="page-title">ピッキング #{{ $shipment_plan->id }}</div>

    <div class="actions">
        <a class="btn" href="{{ route('picking.index') }}">一覧へ</a>

        <form method="POST" action="{{ route('picking.finish', $shipment_plan) }}">
            @csrf
            <button class="btn btn--outline" onclick="return confirm('ピッキングを完了しますか？')">
                ピッキング完了
            </button>
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
        <div>ピッキング中</div>
    </div>
</div>

<div class="card" style="margin-bottom:14px;">
    <h2 class="card__title">スキャン入力（バーコード / SKU）</h2>

    <form method="POST" action="{{ route('picking.scan', $shipment_plan) }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        @csrf
        <input class="input" style="width:420px;" name="code" value="{{ old('code') }}"
               placeholder="バーコード or SKU を入力して Enter（USBスキャナOK）" autofocus>
        <button class="btn btn--primary" type="submit">ピック（+1）</button>
        <div class="muted">※1回入力=1個として加算します</div>
    </form>
</div>

<div class="card" style="margin-bottom:14px;">
    <h2 class="card__title">明細（予定 / ピック / 残数）</h2>

    <table class="table">
        <thead>
        <tr>
            <th>商品</th>
            <th style="width:120px;">予定</th>
            <th style="width:120px;">ピック済</th>
            <th style="width:120px;">残数</th>
            <th style="width:160px;">SKU</th>
            <th style="width:200px;">バーコード</th>
        </tr>
        </thead>
        <tbody>
        @foreach($shipment_plan->lines as $line)
            @php
                $remain = max(0, $line->planned_qty - $line->picked_qty);
            @endphp
            <tr>
                <td>{{ $line->product->name ?? '—' }}</td>
                <td>{{ $line->planned_qty }}</td>
                <td>{{ $line->picked_qty }}</td>
                <td>{{ $remain }}</td>
                <td>{{ $line->product->sku ?? '—' }}</td>
                <td>{{ $line->product->barcode ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="card">
    <h2 class="card__title">直近のピッキングログ（最新10件）</h2>

    <table class="table">
        <thead>
        <tr>
            <th style="width:200px;">日時</th>
            <th>コード</th>
            <th style="width:120px;">数量</th>
        </tr>
        </thead>
        <tbody>
        @forelse($recentLogs as $log)
            <tr>
                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                <td>{{ $log->scanned_code ?? '—' }}</td>
                <td>{{ $log->qty }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">まだログがありません</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
