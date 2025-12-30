@extends('wms.layouts.app')

@section('title', '入荷予定詳細')
@section('header_title', '入荷')

@section('content')
<div class="page-head">
    <div class="page-title">入荷予定 #{{ $inbound_plan->id }}</div>
    <div class="actions">
        <a class="btn" href="{{ route('inbound-plans.index') }}">一覧へ</a>
        <a class="btn btn--outline" href="{{ route('inbound-plans.edit', $inbound_plan) }}">編集</a>

        @if($inbound_plan->status === 'DRAFT')
            <form method="POST" action="{{ route('inbound-plans.confirm', $inbound_plan) }}">
                @csrf
                <button class="btn btn--primary" type="submit">入荷予定を確定する</button>
            </form>
        @endif
    </div>
</div>

<div class="card" style="margin-bottom:14px;">
    <h2 class="card__title">ヘッダ</h2>

    <div class="form-grid">
        <div class="label">仕入先</div>
        <div>{{ $inbound_plan->supplier->name }}</div>

        <div class="label">入荷予定日</div>
        <div>{{ optional($inbound_plan->planned_date)->format('Y-m-d') ?? '—' }}</div>

        <div class="label">ステータス</div>
        <div>{{ $inbound_plan->status }}</div>

        <div class="label">備考</div>
        <div>{{ $inbound_plan->note ?? '—' }}</div>
    </div>
</div>

{{-- 明細追加（DRAFTのみ） --}}
@if($inbound_plan->status === 'DRAFT')
<div class="card" style="margin-bottom:14px;">
    <h2 class="card__title">明細を追加</h2>

    <form method="POST" action="{{ route('inbound-plans.lines.store', $inbound_plan) }}">
        @csrf

        <div class="form-grid">
            <div class="label">商品 <span class="badge-required">必須</span></div>
            <div>
                {{-- productは増えるので最初はid手入力でもOKだが、ここではシンプルに商品ID入力 --}}
                <input class="input" style="width:100%;" name="product_id" placeholder="商品ID（後でセレクトに改善可）" value="{{ old('product_id') }}">
                <div class="muted" style="margin-top:6px;">※次のステップで「商品選択（セレクト/検索）」に改良できます</div>
            </div>

            <div class="label">予定数量 <span class="badge-required">必須</span></div>
            <div>
                <input class="input" style="width:100%;" type="number" min="1" name="planned_qty" value="{{ old('planned_qty', 1) }}">
            </div>

            <div class="label">備考 <span class="badge-optional">任意</span></div>
            <div>
                <input class="input" style="width:100%;" name="note" value="{{ old('note') }}">
            </div>
        </div>

        <div style="margin-top:12px; display:flex; justify-content:flex-end;">
            <button class="btn btn--primary" type="submit">明細を追加する</button>
        </div>
    </form>
</div>
@endif

<div class="card">
    <h2 class="card__title">明細</h2>

    <table class="table">
        <thead>
        <tr>
            <th style="width:90px;">商品ID</th>
            <th>商品名</th>
            <th style="width:140px;">予定数</th>
            <th style="width:140px;">入荷済</th>
            <th style="width:140px;">入庫済</th>
            <th>備考</th>
            <th style="width:220px;">操作</th>
        </tr>
        </thead>
        <tbody>
        @forelse($inbound_plan->lines as $line)
            <tr>
                <td>{{ $line->product_id }}</td>
                <td>{{ $line->product->name ?? '—' }}</td>
                <td>{{ $line->planned_qty }}</td>
                <td>{{ $line->received_qty }}</td>
                <td>{{ $line->putaway_qty }}</td>
                <td>{{ $line->note ?? '—' }}</td>
                <td>
                    @if($inbound_plan->status === 'DRAFT')
                        {{-- 予定数だけその場編集 --}}
                        <form method="POST" action="{{ route('inbound-plans.lines.update', [$inbound_plan, $line]) }}" style="display:inline-flex; gap:6px; align-items:center;">
                            @csrf
                            @method('PATCH')
                            <input class="input" style="width:110px;" type="number" min="1" name="planned_qty" value="{{ $line->planned_qty }}">
                            <input class="input" style="width:160px;" name="note" value="{{ $line->note }}">
                            <button class="btn btn--outline" type="submit">更新</button>
                        </form>

                        <form method="POST" action="{{ route('inbound-plans.lines.destroy', [$inbound_plan, $line]) }}" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn" onclick="return confirm('この明細を削除しますか？')">削除</button>
                        </form>
                    @else
                        <span class="muted">確定後は編集不可</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="muted">明細がありません</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
