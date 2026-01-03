@extends('wms.layouts.app')

@section('title', '出荷予定')
@section('header_title', '出荷')

@section('content')
<div class="page-head">
    <div class="page-title">出荷予定</div>

    <div class="actions">
        <a class="btn btn--primary" href="{{ route('shipment-plans.create') }}">新規作成</a>
    </div>
</div>

<div class="card">
    <div class="toolbar">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <input class="input" type="text" name="keyword" value="{{ request('keyword') }}" placeholder="検索（出荷先名）">
            <input class="input" style="width:200px;" type="date" name="planned_ship_date" value="{{ request('planned_ship_date') }}">
            <button class="btn btn--outline" type="submit">絞り込み</button>
            <a class="btn" href="{{ route('shipment-plans.index') }}">リセット</a>
        </form>

        <div style="margin-left:auto" class="muted">
            {{ $plans->total() }}件
        </div>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th style="width:90px;">ID</th>
            <th>出荷先</th>
            <th style="width:160px;">出荷予定日</th>
            <th style="width:140px;">状態</th>
            <th style="width:220px;">操作</th>
        </tr>
        </thead>
        <tbody>
        @forelse($plans as $plan)
            <tr>
                <td>{{ $plan->id }}</td>
                <td>
                    <a href="{{ route('shipment-plans.show', $plan) }}" style="color: var(--blue); font-weight:800;">
                        {{ $plan->customer->name ?? '—' }}
                    </a>
                </td>
                <td>{{ optional($plan->planned_ship_date)->format('Y-m-d') ?? '—' }}</td>
                <td>
                    @php
                        $statusLabel = match($plan->status) {
                            'PLANNED' => '出荷予定（未引当）',
                            'ALLOCATED' => '在庫引当済み',
                            'PICKING' => 'ピッキング中',
                            'PACKING' => '出荷作業中',
                            'SHIPPED' => '出荷完了',
                            default => $plan->status,
                        };
                    @endphp
                    {{ $statusLabel }}
                </td>
                <td style="display:flex; gap:8px; flex-wrap:wrap;">
                    <a class="btn" href="{{ route('shipment-plans.show', $plan) }}">詳細</a>
                    <a class="btn btn--outline" href="{{ route('shipment-plans.edit', $plan) }}">編集</a>

                    <form method="POST" action="{{ route('shipment-plans.destroy', $plan) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn--outline" onclick="return confirm('削除しますか？')">削除</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">出荷予定がありません</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">
        {{ $plans->links() }}
    </div>
</div>
@endsection
