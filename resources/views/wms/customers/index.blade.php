@extends('wms.layouts.app')

@section('title', '出荷先一覧')
@section('header_title', '取引先')

@section('content')
<div class="page-head">
    <div class="page-title">出荷先</div>
    <div class="actions">
        <a class="btn btn--outline" href="#">エクスポート</a>
        <a class="btn btn--outline" href="#">インポート</a>
        <a class="btn btn--primary" href="{{ route('customers.create') }}">出荷先を作成する</a>
    </div>
</div>

<div class="card">
    <div class="toolbar">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <input class="input" type="text" name="keyword" value="{{ request('keyword') }}" placeholder="検索（出荷先名 / コード / メール）">
            <button class="btn btn--outline" type="submit">絞り込み</button>
            <a class="btn" href="{{ route('customers.index') }}">リセット</a>
        </form>

        <div style="margin-left:auto" class="muted">
            {{ $customers->total() }}件
        </div>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th>出荷先名</th>
            <th style="width:160px;">出荷先コード</th>
            <th>住所</th>
            <th style="width:140px;">電話番号</th>
            <th style="width:220px;">メールアドレス</th>
            <th style="width:160px;">配送方法</th>
            <th style="width:220px;">詳細</th>
        </tr>
        </thead>
        <tbody>
        @forelse($customers as $customer)
            <tr>
                <td>
                    <a href="{{ route('customers.show', $customer) }}" style="color: var(--blue); font-weight: 800;">
                        {{ $customer->name }}
                    </a>
                </td>
                <td>{{ $customer->code ?? '—' }}</td>
                <td>
                    <span class="muted">〒</span> {{ $customer->postal_code ?? '—' }}
                    <div class="muted" style="margin-top:4px;">
                        {{ trim(($customer->address1 ?? '') . ' ' . ($customer->address2 ?? '')) ?: '—' }}
                    </div>
                </td>
                <td>{{ $customer->phone ?? '—' }}</td>
                <td>{{ $customer->email ?? '—' }}</td>
                <td>{{ $customer->shipping_method ?? '—' }}</td>
                <td>
                    <div style="display:flex; gap:8px; align-items:center; justify-content:space-between;">
                        <span class="muted">
                            {{ \Illuminate\Support\Str::limit($customer->note ?? '', 18, '…') ?: '—' }}
                        </span>
                        <a class="btn btn--outline" href="{{ route('customers.edit', $customer) }}">編集</a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="muted">データがありません</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top: 12px;">
        {{ $customers->links() }}
    </div>
</div>
@endsection
