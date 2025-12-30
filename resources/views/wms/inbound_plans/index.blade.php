@extends('wms.layouts.app')

@section('title', '入荷予定一覧')
@section('header_title', '入荷')

@section('content')
<div class="page-head">
    <div class="page-title">入荷予定</div>
    <div class="actions">
        <a class="btn btn--primary" href="{{ route('inbound-plans.create') }}">入荷予定を作成する</a>
    </div>
</div>

<div class="card">
    <div class="toolbar">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <input class="input" type="text" name="keyword" value="{{ request('keyword') }}" placeholder="検索（仕入先名）">

            <input class="input" style="width:180px;" type="date" name="planned_date" value="{{ request('planned_date') }}">

            <select class="input" style="width:220px;" name="status">
                <option value="">ステータス（全て）</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <button class="btn btn--outline" type="submit">絞り込み</button>
            <a class="btn" href="{{ route('inbound-plans.index') }}">リセット</a>
        </form>

        <div style="margin-left:auto" class="muted">
            {{ $inboundPlans->total() }}件
        </div>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th style="width:90px;">ID</th>
            <th>仕入先</th>
            <th style="width:160px;">入荷予定日</th>
            <th style="width:180px;">ステータス</th>
            <th style="width:180px;">操作</th>
        </tr>
        </thead>
        <tbody>
        @forelse($inboundPlans as $plan)
            <tr>
                <td>{{ $plan->id }}</td>
                <td>
                    <a href="{{ route('inbound-plans.show', $plan) }}" style="color: var(--blue); font-weight:800;">
                        {{ $plan->supplier->name }}
                    </a>
                </td>
                <td>{{ optional($plan->planned_date)->format('Y-m-d') ?? '—' }}</td>
                <td>{{ $statuses[$plan->status] ?? $plan->status }}</td>
                <td>
                    <a class="btn btn--outline" href="{{ route('inbound-plans.edit', $plan) }}">編集</a>
                    <a class="btn" href="{{ route('inbound-plans.show', $plan) }}">詳細</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">データがありません</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">
        {{ $inboundPlans->links() }}
    </div>
</div>
@endsection
