@extends('wms.layouts.app')

@section('title', '在庫引当済み')
@section('header_title', '出荷')

@section('content')
<div class="page-head">
    <div class="page-title">在庫引当済み</div>
</div>

<div class="card">
    <div class="toolbar">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <input class="input" type="text" name="keyword" value="{{ request('keyword') }}" placeholder="検索（出荷先名）">
            <input class="input" style="width:200px;" type="date" name="planned_ship_date" value="{{ request('planned_ship_date') }}">
            <button class="btn btn--outline" type="submit">絞り込み</button>
            <a class="btn" href="{{ route('allocated-shipments.index') }}">リセット</a>
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
            <th style="width:150px;">状態</th>
            <th style="width:220px;">操作</th>
        </tr>
        </thead>
        <tbody>
        @forelse($plans as $plan)
            <tr>
                <td>{{ $plan->id }}</td>
                <td>
                    <a href="{{ route('allocated-shipments.show', $plan) }}" style="color: var(--blue); font-weight:800;">
                        {{ $plan->customer->name }}
                    </a>
                </td>
                <td>{{ optional($plan->planned_ship_date)->format('Y-m-d') ?? '—' }}</td>
                <td>在庫引当済み</td>
                <td>
                    <a class="btn btn--outline" href="{{ route('allocated-shipments.invoice', $plan) }}" target="_blank">納品書</a>
                    <a class="btn btn--outline" href="{{ route('allocated-shipments.label', $plan) }}" target="_blank">送り状</a>
                    <a class="btn" href="{{ route('allocated-shipments.show', $plan) }}">詳細</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">在庫引当済みの出荷予定がありません</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">
        {{ $plans->links() }}
    </div>
</div>
@endsection
