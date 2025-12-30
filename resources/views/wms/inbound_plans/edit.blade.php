@extends('wms.layouts.app')

@section('title', '入荷予定を編集する')
@section('header_title', '入荷')

@section('content')
<div class="page-head">
    <div class="page-title">入荷予定を編集する</div>
    <div class="actions">
        <a class="btn" href="{{ route('inbound-plans.show', $inbound_plan) }}">キャンセル</a>
        <button class="btn btn--primary" form="plan-form" type="submit">保存する</button>
    </div>
</div>

<div class="card">
    <h2 class="card__title">入荷予定（ヘッダ）</h2>

    <form id="plan-form" method="POST" action="{{ route('inbound-plans.update', $inbound_plan) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="label">仕入先 <span class="badge-required">必須</span></div>
            <div>
                <select class="input" style="width:100%;" name="supplier_id">
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}"
                                @selected(old('supplier_id', $inbound_plan->supplier_id) == $supplier->id)>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="label">入荷予定日 <span class="badge-optional">任意</span></div>
            <div>
                <input class="input" style="width:100%;" type="date"
                       name="planned_date"
                       value="{{ old('planned_date', optional($inbound_plan->planned_date)->format('Y-m-d')) }}">
            </div>

            <div class="label">ステータス <span class="badge-required">必須</span></div>
            <div>
                <select class="input" style="width:100%;" name="status">
                    @php
                        $opts = ['DRAFT'=>'下書き','RECEIVING'=>'入荷作業中（検品）','WAITING_PUTAWAY'=>'入庫待ち','COMPLETED'=>'完了','CANCELED'=>'キャンセル'];
                    @endphp
                    @foreach($opts as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $inbound_plan->status) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="label">備考 <span class="badge-optional">任意</span></div>
            <div>
                <textarea class="input" style="width:100%; height:110px;" name="note">{{ old('note', $inbound_plan->note) }}</textarea>
            </div>
        </div>
    </form>

    <div style="margin-top:16px; display:flex; justify-content:flex-end;">
        <form method="POST" action="{{ route('inbound-plans.destroy', $inbound_plan) }}">
            @csrf
            @method('DELETE')
            <button class="btn" onclick="return confirm('削除しますか？')">削除</button>
        </form>
    </div>
</div>
@endsection
