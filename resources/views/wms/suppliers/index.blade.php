@extends('wms.layouts.app')

@section('title', '仕入先一覧')
@section('header_title', '取引先')

@section('content')
<div class="page-head">
    <div class="page-title">仕入先</div>
    <div class="actions">
        <a class="btn btn--outline" href="#">エクスポート</a>
        <a class="btn btn--outline" href="#">インポート</a>
        <a class="btn btn--primary" href="{{ route('suppliers.create') }}">仕入先を作成する</a>
    </div>
</div>

<div class="card">
    <div class="toolbar">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <input class="input" type="text" name="keyword" value="{{ request('keyword') }}" placeholder="検索（仕入先名 / コード / メール）">
            <button class="btn btn--outline" type="submit">絞り込み</button>
            <a class="btn" href="{{ route('suppliers.index') }}">リセット</a>
        </form>

        <div style="margin-left:auto" class="muted">
            {{ $suppliers->total() }}件
        </div>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th>仕入先名</th>
            <th style="width:160px;">仕入先コード</th>
            <th>住所</th>
            <th style="width:140px;">電話番号</th>
            <th style="width:220px;">メールアドレス</th>
            <th style="width:220px;">詳細</th>
        </tr>
        </thead>
        <tbody>
        @forelse($suppliers as $supplier)
            <tr>
                <td>
                    <a href="{{ route('suppliers.show', $supplier) }}" style="color: var(--blue); font-weight: 800;">
                        {{ $supplier->name }}
                    </a>
                </td>
                <td>{{ $supplier->code ?? '—' }}</td>
                <td>
                    <span class="muted">〒</span> {{ $supplier->postal_code ?? '—' }}
                    <div class="muted" style="margin-top:4px;">
                        {{ trim(($supplier->address1 ?? '') . ' ' . ($supplier->address2 ?? '')) ?: '—' }}
                    </div>
                </td>
                <td>{{ $supplier->phone ?? '—' }}</td>
                <td>{{ $supplier->email ?? '—' }}</td>
                <td>
                    <div style="display:flex; gap:8px; align-items:center; justify-content:space-between;">
                        <span class="muted">
                            {{ \Illuminate\Support\Str::limit($supplier->note ?? '', 18, '…') ?: '—' }}
                        </span>
                        <a class="btn btn--outline" href="{{ route('suppliers.edit', $supplier) }}">編集</a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="muted">データがありません</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top: 12px;">
        {{ $suppliers->links() }}
    </div>
</div>
@endsection
