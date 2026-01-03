@extends('wms.layouts.app')

@section('title', '出荷履歴詳細')
@section('header_title', '出荷')

@section('content')
<div class="page-head">
    <div class="page-title">出荷履歴 #{{ $shipment_plan->id }}</div>

    <div class="actions">
        <a class="btn" href="{{ route('shipped-histories.index') }}">一覧へ</a>

        <a class="btn btn--outline" href="{{ route('shipped-histories.invoice', $shipment_plan) }}" target="_blank">納品書</a>
        <a class="btn btn--outline" href="{{ route('shipped-histories.label', $shipment_plan) }}" target="_blank">送り状</a>
    </div>
</div>

<div class="card" style="margin-bottom:14px;">
    <h2 class="card__title">ヘッダ</h2>
    <div class="form-grid">
        <div class="label">出荷先</div>
        <div>{{ $shipment_plan->customer->name }}</div>

        <div class="label">出荷予定日</div>
        <div>{{ optional($shipment_plan->planned_ship_date)->format('Y-m-d') ?? '—' }}</div>

        <div class="label">運送会社</div>
        <div>{{ $shipment_plan->carrier ?? '—' }}</div>

        <div class="label">送り状番号</div>
        <div>{{ $shipment_plan->tracking_no ?? '—' }}</div>

        <div class="label">状態</div>
        <div>出荷完了</div>
    </div>
</div>

<div class="card" style="margin-bottom:14px;">
    <h2 class="card__title">明細（出荷済）</h2>

    <table class="table">
        <thead>
        <tr>
            <th>商品</th>
            <th style="width:120px; text-align:right;">予定</th>
            <th style="width:120px; text-align:right;">ピック済</th>
            <th style="width:120px; text-align:right;">出荷済</th>
        </tr>
        </thead>
        <tbody>
        @foreach($shipment_plan->lines as $line)
            <tr>
                <td>{{ $line->product->name ?? '—' }}</td>
                <td style="text-align:right;">{{ $line->planned_qty }}</td>
                <td style="text-align:right;">{{ $line->picked_qty }}</td>
                <td style="text-align:right; font-weight:800;">{{ $line->shipped_qty }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="card">
    <h2 class="card__title">出荷ログ</h2>

    <table class="table">
        <thead>
        <tr>
            <th style="width:200px;">日時</th>
            <th style="width:160px;">運送会社</th>
            <th>送り状番号</th>
        </tr>
        </thead>
        <tbody>
        @forelse($shippingLogs as $log)
            <tr>
                <td>{{ optional($log->shipped_at)->format('Y-m-d H:i:s') ?? $log->created_at->format('Y-m-d H:i:s') }}</td>
                <td>{{ $log->carrier ?? '—' }}</td>
                <td>{{ $log->tracking_no ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">ログがありません</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
