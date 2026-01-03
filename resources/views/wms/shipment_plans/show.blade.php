@extends('wms.layouts.app')

@section('title', '出荷予定 詳細')
@section('header_title', '出荷')

@section('content')
<div class="page-head">
    <div class="page-title">出荷予定 詳細 #{{ $shipment_plan->id }}</div>

    <div class="actions">
        <a class="btn" href="{{ route('shipment-plans.index') }}">一覧へ</a>
        <a class="btn btn--outline" href="{{ route('shipment-plans.edit', $shipment_plan) }}">ヘッダ編集</a>

        {{-- 在庫引当 / 引当解除 --}}
        @if($shipment_plan->status === 'PLANNED')
            <form method="POST" action="{{ route('shipment-plans.allocate', $shipment_plan) }}">
                @csrf
                <button class="btn btn--primary" onclick="return confirm('在庫を引当しますか？')">在庫を引当する</button>
            </form>
        @endif

        @if($shipment_plan->status === 'ALLOCATED')
            <form method="POST" action="{{ route('shipment-plans.deallocate', $shipment_plan) }}">
                @csrf
                <button class="btn btn--outline" onclick="return confirm('在庫引当を解除しますか？')">引当を解除する</button>
            </form>
        @endif
    </div>
</div>

{{-- ヘッダ --}}
<div class="card" style="margin-bottom:14px;">
    <h2 class="card__title">ヘッダ</h2>

    <div class="form-grid">
        <div class="label">出荷先</div>
        <div>{{ $shipment_plan->customer->name ?? '—' }}</div>

        <div class="label">出荷予定日</div>
        <div>{{ optional($shipment_plan->planned_ship_date)->format('Y-m-d') ?? '—' }}</div>

        <div class="label">状態</div>
        <div>
            @php
                $statusLabel = match($shipment_plan->status) {
                    'PLANNED' => '出荷予定（未引当）',
                    'ALLOCATED' => '在庫引当済み',
                    'PICKING' => 'ピッキング中',
                    'PACKING' => '出荷作業中',
                    'SHIPPED' => '出荷完了',
                    default => $shipment_plan->status,
                };
            @endphp
            {{ $statusLabel }}
        </div>

        <div class="label">備考</div>
        <div>{{ $shipment_plan->note ?? '—' }}</div>
    </div>
</div>

{{-- 明細追加（PLANNEDのみ許可） --}}
<div class="card" style="margin-bottom:14px;">
    <h2 class="card__title">明細追加</h2>

    @if($shipment_plan->status !== 'PLANNED')
        <div class="muted">
            ※在庫引当後は明細編集できません（引当解除してから編集してください）
        </div>
    @else
        <form method="POST" action="{{ route('shipment-plans.lines.store', $shipment_plan) }}" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            @csrf

            <select class="input" name="product_id" style="width:380px;">
                <option value="">商品を選択</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                        {{ $product->name }}（SKU: {{ $product->sku ?? '—' }} / BAR: {{ $product->barcode ?? '—' }}）
                    </option>
                @endforeach
            </select>

            <input class="input" style="width:160px;" type="number" min="1" name="planned_qty" value="{{ old('planned_qty', 1) }}" placeholder="数量">

            <button class="btn btn--primary" type="submit">追加</button>

            @error('product_id') <div class="error">{{ $message }}</div> @enderror
            @error('planned_qty') <div class="error">{{ $message }}</div> @enderror
        </form>
    @endif
</div>

{{-- 明細一覧 --}}
<div class="card">
    <h2 class="card__title">明細</h2>

    <table class="table">
        <thead>
        <tr>
            <th>商品</th>
            <th style="width:110px; text-align:right;">予定</th>
            <th style="width:110px; text-align:right;">ピック</th>
            <th style="width:110px; text-align:right;">出荷</th>
            <th style="width:110px; text-align:right;">残</th>
            <th style="width:160px;">SKU</th>
            <th style="width:220px;">バーコード</th>
            <th style="width:220px;">操作</th>
        </tr>
        </thead>
        <tbody>
        @forelse($shipment_plan->lines as $line)
            @php
                $remainToPick = max(0, (int)$line->planned_qty - (int)$line->picked_qty);
                $remainToShip = max(0, (int)$line->picked_qty - (int)$line->shipped_qty);
                // 表示上の「残」は、未ピックを優先して見せる（現場の感覚に近い）
                $remain = $shipment_plan->status === 'PICKING' || $shipment_plan->status === 'PACKING'
                    ? $remainToShip
                    : $remainToPick;
            @endphp
            <tr>
                <td>{{ $line->product->name ?? '—' }}</td>
                <td style="text-align:right; font-weight:800;">{{ $line->planned_qty }}</td>
                <td style="text-align:right;">{{ $line->picked_qty }}</td>
                <td style="text-align:right;">{{ $line->shipped_qty }}</td>
                <td style="text-align:right;">{{ $remain }}</td>
                <td>{{ $line->product->sku ?? '—' }}</td>
                <td>{{ $line->product->barcode ?? '—' }}</td>
                <td style="display:flex; gap:8px; flex-wrap:wrap;">
                    @if($shipment_plan->status === 'PLANNED')
                        <form method="POST" action="{{ route('shipment-plans.lines.update', [$shipment_plan, $line]) }}" style="display:flex; gap:8px; align-items:center;">
                            @csrf
                            @method('PATCH')
                            <input class="input" style="width:120px;" type="number" min="1" name="planned_qty" value="{{ $line->planned_qty }}">
                            <button class="btn btn--outline" type="submit">更新</button>
                        </form>

                        <form method="POST" action="{{ route('shipment-plans.lines.destroy', [$shipment_plan, $line]) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn--outline" onclick="return confirm('削除しますか？')">削除</button>
                        </form>
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="muted">明細がありません</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="muted" style="margin-top:10px;">
        ※在庫引当は「予定数」を基準に reserved_qty を増やします。引当後は明細編集できません（解除してから編集）。
    </div>
</div>
@endsection
