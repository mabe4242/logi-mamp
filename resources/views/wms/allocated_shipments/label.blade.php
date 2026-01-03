<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>送り状 #{{ $shipment_plan->id }}</title>
    <style>
        body { font-family: sans-serif; padding: 24px; }
        .box { border:2px solid #000; padding:18px; width:520px; }
        .row { display:flex; justify-content:space-between; margin-bottom:10px; }
        .big { font-size: 22px; font-weight: 800; }
        .muted { color:#666; font-size:12px; }
    </style>
</head>
<body>
    <div class="box">
        <div class="row">
            <div class="big">送り状（仮）</div>
            <div class="muted">伝票番号：{{ $shipment_plan->id }}</div>
        </div>

        <div style="margin:12px 0;">
            <div class="muted">お届け先</div>
            <div class="big">{{ $shipment_plan->customer->name }}</div>
        </div>

        <div class="row">
            <div>
                <div class="muted">運送会社</div>
                <div>{{ $shipment_plan->carrier ?? '未設定' }}</div>
            </div>
            <div>
                <div class="muted">送り状番号</div>
                <div class="big">{{ $shipment_plan->tracking_no ?? '未設定' }}</div>
            </div>
        </div>

        <div style="margin-top:14px;" class="muted">
            ※本実装ではヤマト/佐川のAPI or CSV連携などに置き換え
        </div>
    </div>

    <div style="margin-top:20px;">
        <button onclick="window.print()">印刷</button>
    </div>
</body>
</html>
