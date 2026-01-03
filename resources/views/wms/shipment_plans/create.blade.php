@extends('wms.layouts.app')

@section('title', '出荷予定 新規作成')
@section('header_title', '出荷')

@section('content')
<div class="page-head">
    <div class="page-title">出荷予定 新規作成</div>

    <div class="actions">
        <a class="btn" href="{{ route('shipment-plans.index') }}">一覧へ</a>
    </div>
</div>

<div class="card">
    <h2 class="card__title">ヘッダ</h2>

    <form method="POST" action="{{ route('shipment-plans.store') }}">
        @csrf

        <div class="form-grid">
            <div class="label">出荷先</div>
            <div>
                <select class="input" name="customer_id" style="width:360px;">
                    <option value="">選択してください</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="label">出荷予定日</div>
            <div>
                <input class="input" style="width:220px;" type="date" name="planned_ship_date" value="{{ old('planned_ship_date') }}">
                @error('planned_ship_date') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="label">備考</div>
            <div>
                <textarea class="input" name="note" rows="3" style="width:520px;">{{ old('note') }}</textarea>
                @error('note') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="margin-top:14px; display:flex; gap:10px;">
            <button class="btn btn--primary" type="submit">作成</button>
            <a class="btn btn--outline" href="{{ route('shipment-plans.index') }}">キャンセル</a>
        </div>
    </form>
</div>
@endsection
