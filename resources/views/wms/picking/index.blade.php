@extends('wms.layouts.app')

@section('title', 'ピッキング開始')
@section('header_title', '出荷')

@section('content')
<div class="page-head">
    <div class="page-title">ピッキング開始</div>
</div>

<div class="card">
    <div class="toolbar">
        <form method="GET" style="display:flex; gap:10px; align-items:center;">
            <input class="input" type="text" name="keyword" value="{{ request('keyword') }}" placeholder="検索（出荷先名）">
            <button class="btn btn--outline" type="submit">絞り込み</button>
            <a class="btn" href="{{ route('picking.index') }}">リセット</a>
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
            <th style="width:180px;">操作</th>
        </tr>
        </thead>
        <tbody>
        @forelse($plans as $plan)
            <tr>
                <td>{{ $plan->id }}</td>
                <td>{{ $plan->customer->name }}</td>
                <td>{{ optional($plan->planned_ship_date)->format('Y-m-d') ?? '—' }}</td>
                <td>ピッキング中</td>
                <td>
                    <a class="btn btn--primary" href="{{ route('picking.show', $plan) }}">ピッキング</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">ピッキング対象がありません</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">
        {{ $plans->links() }}
    </div>
</div>
@endsection
