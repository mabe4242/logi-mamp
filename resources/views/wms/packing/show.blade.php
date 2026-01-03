@extends('wms.layouts.app')

@section('title', '出荷作業')
@section('header_title', '出荷')

@section('content')
<div class="page-head">
    <div class="page-title">出荷作業 #{{ $shipment_plan->id }}</div>

    <div class="actions">
        <a class="btn" href="{{ route('packing.index') }}">一覧へ</a>

        <form method="POST" action="{{ route('packing.ship', $shipment_plan) }}">
            @csrf
            <button class="btn btn--primary" onclick="return confirm('出荷完了しますか？在庫が減算されます。')">
                出荷完了（在庫減算）
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
        <div>出荷作業中</div>

        <div class="label">運送会社</div>
        <div>{{ $shipment_plan->carrier ?? '未設定' }}</div>

        <div class="label">送り状番号</div>
        <div>{{ $shipment_plan->tracking_no ?? '未設定' }}</div>
    </div>
</div>

<div class="card" style="margin-bottom:14px;">
    <h2 class="card__title">送り状スキャン（入力で代替）</h2>
    <form method="POST" action="{{ route('packing.scan-label', $shipment_plan) }}" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        @csrf
        <input class="input" style="width:420px;" name="tracking_no" value="{{ old('tracking_no', $shipment_plan->tracking_no) }}"
               placeholder="送り状番号を入力して Enter（USBスキャナOK）">
        <button class="btn btn--outline" type="submit">登録</button>
    </form>
</div>

<div class="card" style="margin-bottom:14px;">
    <h2 class="card__title">運送会社</h2>
    <form method="POST" action="{{ route('packing.set-carrier', $shipment_plan) }}" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        @csrf
        <select class="input" name="carrier" style="width:260px;">
            @foreach($carriers as $c)
                <option value="{{ $c }}" @selected($shipment_plan->carrier === $c)>{{ $c }}</option>
            @endforeach
        </select>
        <button class="btn btn--outline" type="submit">設定</button>
    </form>
</div>

<div class="card" style="margin-bottom:14px;">
    <h2 class="card__title">明細（ピック済 / 出荷済 / 残）</h2>

    <table class="table">
        <thead>
        <tr>
            <th>商品</th>
            <th style="width:110px;">予定</th>
            <th style="width:110px;">ピック済</th>
            <th style="width:110px;">出荷済</th>
            <th style="width:110px;">残</th>
        </tr>
        </thead>
        <tbody>
        @foreach($shipment_plan->lines as $line)
            @php
                $remain = max(0, $line->picked_qty - $line->shipped_qty);
            @endphp
            <tr>
                <td>{{ $line->product->name ?? '—' }}</td>
                <td>{{ $line->planned_qty }}</td>
                <td>{{ $line->picked_qty }}</td>
                <td>{{ $line->shipped_qty }}</td>
                <td>{{ $remain }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="muted" style="margin-top:10px;">
        ※出荷完了すると「ピック済 − 出荷済（残）」分が出荷され、在庫が減算されます。
    </div>
</div>

<div class="card">
    <h2 class="card__title">直近の出荷ログ（最新10件）</h2>

    <table class="table">
        <thead>
        <tr>
            <th style="width:200px;">日時</th>
            <th style="width:160px;">運送会社</th>
            <th>送り状番号</th>
        </tr>
        </thead>
        <tbody>
        @forelse($recentLogs as $log)
            <tr>
                <td>{{ optional($log->shipped_at)->format('Y-m-d H:i:s') ?? $log->created_at->format('Y-m-d H:i:s') }}</td>
                <td>{{ $log->carrier ?? '—' }}</td>
                <td>{{ $log->tracking_no ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">まだ出荷ログがありません</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
