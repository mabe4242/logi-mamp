@extends('wms.layouts.app')

@section('title', '保管場所詳細')
@section('header_title', '在庫')

@section('content')
<div class="page-head">
    <div class="page-title">保管場所詳細</div>
    <div class="actions">
        <a class="btn" href="{{ route('locations.index') }}">一覧へ</a>
        <a class="btn btn--outline" href="{{ route('locations.edit', $location) }}">編集</a>
    </div>
</div>

<div class="card">
    <h2 class="card__title">ロケーション情報</h2>

    <div class="form-grid">
        <div class="label">ロケーションコード</div>
        <div>{{ $location->code }}</div>

        <div class="label">名称</div>
        <div>{{ $location->name ?? '—' }}</div>

        <div class="label">備考</div>
        <div>{{ $location->note ?? '—' }}</div>
    </div>
</div>
@endsection
