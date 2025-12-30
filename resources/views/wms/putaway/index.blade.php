@extends('wms.layouts.app')

@section('title', '入荷作業中（入庫）')
@section('header_title', '入荷')

@section('content')
<div class="page-head">
    <div class="page-title">入荷作業中（入庫）</div>
</div>

<div class="card">
    <div class="toolbar">
        <form method="GET" style="display:flex; gap:10px; align-items:center;">
            <input class="input" type="text" name="keyword" value="{{ request('keyword') }}" placeholder="検索（仕入先名）">
            <button class="btn btn--outline" type="submit">絞り込み</button>
            <a class="btn" href="{{ route('putaway.index') }}">リセット</a>
        </form>

        <div style="margin-left:auto" class="muted">
            {{ $plans->total() }}件
        </div>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th style="width:90px;">ID</th>
            <th>仕入先</th>
            <th style="width:160px;">入荷予定日</th>
            <th style="width:180px;">状態</th>
            <th style="width:180px;">操作</th>
        </tr>
        </thead>
        <tbody>
        @forelse($plans as $plan)
            <tr>
                <td>{{ $plan->id }}</td>
                <td>{{ $plan->supplier->name }}</td>
                <td>{{ optional($plan->planned_date)->format('Y-m-d') ?? '—' }}</td>
                <td>入庫待ち</td>
                <td>
                    <a class="btn btn--primary" href="{{ route('putaway.show', $plan) }}">入庫する</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">入庫待ちの入荷予定がありません</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">
        {{ $plans->links() }}
    </div>
</div>
@endsection
