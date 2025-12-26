@extends('wms.layouts.app')

@section('title', '保管場所一覧')
@section('header_title', '在庫')

@section('content')
<div class="page-head">
    <div class="page-title">保管場所</div>
    <div class="actions">
        <a class="btn btn--primary" href="{{ route('locations.create') }}">保管場所を作成する</a>
    </div>
</div>

<div class="card">
    <div class="toolbar">
        <form method="GET" style="display:flex; gap:10px; align-items:center;">
            <input class="input" type="text" name="keyword" value="{{ request('keyword') }}" placeholder="検索（コード / 名称）">
            <button class="btn btn--outline">絞り込み</button>
            <a class="btn" href="{{ route('locations.index') }}">リセット</a>
        </form>

        <div style="margin-left:auto" class="muted">
            {{ $locations->total() }}件
        </div>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th style="width:160px;">ロケーションコード</th>
            <th>名称</th>
            <th>備考</th>
            <th style="width:160px;">操作</th>
        </tr>
        </thead>
        <tbody>
        @forelse($locations as $location)
            <tr>
                <td>
                    <a href="{{ route('locations.show', $location) }}"
                       style="color: var(--blue); font-weight: 800;">
                        {{ $location->code }}
                    </a>
                </td>
                <td>{{ $location->name ?? '—' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($location->note ?? '', 30) ?: '—' }}</td>
                <td>
                    <a class="btn btn--outline" href="{{ route('locations.edit', $location) }}">編集</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="muted">データがありません</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">
        {{ $locations->links() }}
    </div>
</div>
@endsection
