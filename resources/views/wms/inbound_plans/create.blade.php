@extends('wms.layouts.app')

@section('title', '入荷予定を作成する')
@section('header_title', '入荷')

@section('content')
<div class="page-head">
    <div class="page-title">入荷予定を作成する</div>
    <div class="actions">
        <a class="btn" href="{{ route('inbound-plans.index') }}">キャンセル</a>
        <button class="btn btn--primary" form="plan-form" type="submit">保存する</button>
    </div>
</div>

<div class="card">
    <h2 class="card__title">入荷予定（ヘッダ）</h2>

    <form id="plan-form" method="POST" action="{{ route('inbound-plans.store') }}">
        @csrf

        <div class="form-grid">
            <div class="label">仕入先 <span class="badge-required">必須</span></div>
            <div>
                <select class="input" style="width:100%;" name="supplier_id">
                    <option value="">選択してください</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="label">入荷予定日 <span class="badge-optional">任意</span></div>
            <div>
                <input class="input" style="width:100%;" type="date" name="planned_date" value="{{ old('planned_date') }}">
            </div>

            <div class="label">備考 <span class="badge-optional">任意</span></div>
            <div>
                <textarea class="input" style="width:100%; height:110px;" name="note">{{ old('note') }}</textarea>
            </div>
        </div>
    </form>
</div>
@endsection
