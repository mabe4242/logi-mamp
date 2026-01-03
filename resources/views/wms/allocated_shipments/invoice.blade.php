<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>納品書 #{{ $shipment_plan->id }}</title>
    <style>
        body { font-family: sans-serif; padding: 24px; }
        .row { display:flex; justify-content:space-between; margin-bottom:14px; }
        table { width:100%; border-collapse:collapse; margin-top:16px; }
        th, td { border:1px solid #ddd; padding:10px; }
        th { background:#f7f7f7; text-align:left; }
        .right { text-align:right; }
    </style>
</head>
<body>
    <div class="row">
        <h1>納品書</h1>
        <div>
            <div>伝票番号：{{ $shipment_plan->id }}</div>
            <div>出荷予定日：{{ optional($shipment_plan->planned_ship_date)->format('Y-m-d') ?? '—' }}</div>
        </div>
    </div>

    <div style="margin-bottom:10px;">
        <strong>納品先：</strong> {{ $shipment_plan->customer->name }}
    </div>

    <table>
        <thead>
        <tr>
            <th>商品名</th>
            <th class="right">数量</th>
        </tr>
        </thead>
        <tbody>
        @foreach($shipment_plan->lines as $line)
            <tr>
                <td>{{ $line->product->name ?? '—' }}</td>
                <td class="right">{{ $line->planned_qty }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div style="margin-top:20px;">
        <button onclick="window.print()">印刷</button>
    </div>
</body>
</html>
