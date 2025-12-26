@extends('wms.layouts.app')

@section('title', '保管場所を作成する')
@section('header_title', '在庫')

@section('content')
<div class="page-head">
    <div class="page-title">保管場所を作成する</div>
    <div class="actions">
        <a class="btn" href="{{ route('locations.index') }}">キャンセル</a>
        <button class="btn btn--primary" form="location-form">保存する</button>
    </div>
</div>

<div class="card">
    <h2 class="card__title">ロケーション情報</h2>

    <form id="location-form" method="POST" action="{{ route('locations.store') }}">
        @csrf

        <div class="form-grid">
            <div class="label">ロケーションコード <span class="badge-required">必須</span></div>
            <div>
                <input class="input" style="width:100%;" name="code" value="{{ old('code') }}" placeholder="例：A-01">
            </div>

            <div class="label">名称 <span class="badge-optional">任意</span></div>
            <div>
                <input class="input" style="width:100%;" name="name" value="{{ old('name') }}">
            </div>

            <div class="label">備考 <span class="badge-optional">任意</span></div>
            <div>
                <textarea class="input" style="width:100%; height:100px;" name="note">{{ old('note') }}</textarea>
            </div>
        </div>
    </form>
</div>
@endsection
