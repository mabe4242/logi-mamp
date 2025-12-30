@extends('wms.layouts.app')

@section('title', '入庫（Putaway）')
@section('header_title', '入荷')

@section('content')
<div class="page-head">
    <div class="page-title">入庫（Putaway） #{{ $inbound_plan->id }}</div>

    <div class="actions">
        <a class="btn" href="{{ route('putaway.index') }}">一覧へ</a>

        <form method="POST" action="{{ route('putaway.complete', $inbound_plan) }}">
            @csrf
            <button class="btn btn--outline" onclick="return confirm('入庫を完了にしますか？（残数があるとエラーになります）')">
                入庫を完了する
            </button>
        </form>
    </div>
</div>

<div class="card" style="margin-bottom:14px;">
    <h2 class="card__title">ヘッダ</h2>
    <div class="form-grid">
        <div class="label">仕入先</div>
        <div>{{ $inbound_plan->supplier->name }}</div>

        <div class="label">入荷予定日</div>
        <div>{{ optional($inbound_plan->planned_date)->format('Y-m-d') ?? '—' }}</div>

        <div class="label">状態</div>
        <div>入庫待ち</div>
    </div>
</div>

<div class="card" style="margin-bottom:14px;">
    <h2 class="card__title">入庫する</h2>

    <form method="POST" action="{{ route('putaway.store', $inbound_plan) }}" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
        @csrf

        <div style="min-width:280px;">
            <div class="muted" style="margin-bottom:6px;">入庫対象（明細）</div>
            <select class="input" style="width:100%;" name="line_id">
                @foreach($inbound_plan->lines as $line)
                    @php
                        $remain = max(0, $line->received_qty - $line->putaway_qty);
                    @endphp
                    <option value="{{ $line->id }}">
                        {{ $line->product->name ?? '—' }} / 残{{ $remain }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="min-width:260px;">
            <div class="muted" style="margin-bottom:6px;">ロケーション</div>
            <select class="input" style="width:100%;" name="location_id">
                @foreach($locations as $loc)
                    <option value="{{ $loc->id }}">
                        {{ $loc->code }}{{ $loc->name ? '（'.$loc->name.'）' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="min-width:160px;">
            <div class="muted" style="margin-bottom:6px;">数量</div>
            <input class="input" style="width:100%;" type="number" min="1" name="qty" value="1">
        </div>

        <button class="btn btn--primary" type="submit">入庫する（stocks増加）</button>
    </form>

    <div class="muted" style="margin-top:10px;">
        ※後で「ロケーションQRスキャン」「商品バーコード確認」を追加できます（今はセレクトで簡易対応）
    </div>
</div>

<div class="card" style="margin-bottom:14px;">
    <h2 class="card__title">明細（検品 / 入庫 / 残数）</h2>

    <table class="table">
        <thead>
        <tr>
            <th>商品</th>
            <th style="width:120px;">予定</th>
            <th style="width:120px;">検品済</th>
            <th style="width:120px;">入庫済</th>
            <th style="width:120px;">入庫残</th>
        </tr>
        </thead>
        <tbody>
        @foreach($inbound_plan->lines as $line)
            @php
                $remain = max(0, $line->received_qty - $line->putaway_qty);
            @endphp
            <tr>
                <td>{{ $line->product->name ?? '—' }}</td>
                <td>{{ $line->planned_qty }}</td>
                <td>{{ $line->received_qty }}</td>
                <td>{{ $line->putaway_qty }}</td>
                <td>{{ $remain }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="card">
    <h2 class="card__title">直近の入庫ログ（最新10件）</h2>

    <table class="table">
        <thead>
        <tr>
            <th style="width:200px;">日時</th>
            <th>ロケーション</th>
            <th style="width:120px;">数量</th>
        </tr>
        </thead>
        <tbody>
        @forelse($recentPutaways as $p)
            <tr>
                <td>{{ $p->created_at->format('Y-m-d H:i:s') }}</td>
                <td>{{ $p->location->code ?? '—' }}</td>
                <td>{{ $p->qty }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">まだ入庫ログがありません</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
