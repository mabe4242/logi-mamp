@extends('wms.layouts.app')

@section('title', '出荷予定 編集')
@section('header_title', '出荷')

@section('content')
<div class="page-head">
    <div class="page-title">出荷予定 編集 #{{ $shipment_plan->id }}</div>

    <div class="actions">
        <a class="btn" href="{{ route('shipment-plans.show', $shipment_plan) }}">詳細へ</a>
        <a class="btn" href="{{ route('shipment-plans.index') }}">一覧へ</a>
    </div>
</div>

<div class="card">
    <h2 class="card__title">ヘッダ</h2>

    @if($shipment_plan->status !== 'PLANNED')
        <div class="muted" style="margin-bottom:10px;">
            ※この出荷予定は「{{ $shipment_plan->status }}」のため、ヘッダ編集のみを許可しています（明細は show 画面で管理）。
        </div>
    @endif

    <form method="POST" action="{{ route('shipment-plans.update', $shipment_plan) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="label">出荷先</div>
            <div>
                <select class="input" name="customer_id" style="width:360px;">
                    <option value="">選択してください</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id', $shipment_plan->customer_id) == $customer->id)>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="label">出荷予定日</div>
            <div>
                <input class="input" style="width:220px;" type="date" name="planned_ship_date"
                       value="{{ old('planned_ship_date', optional($shipment_plan->planned_ship_date)->format('Y-m-d')) }}">
                @error('planned_ship_date') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="label">備考</div>
            <div>
                <textarea class="input" name="note" rows="3" style="width:520px;">{{ old('note', $shipment_plan->note) }}</textarea>
                @error('note') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="margin-top:14px; display:flex; gap:10px;">
            <button class="btn btn--primary" type="submit">更新</button>
            <a class="btn btn--outline" href="{{ route('shipment-plans.show', $shipment_plan) }}">戻る</a>
        </div>
    </form>
</div>
@endsection
