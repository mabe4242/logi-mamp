<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Stock::query()
            ->with(['product', 'location']);

        // 検索（SKU / 商品名 / バーコード）
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('product', function ($q) use ($keyword) {
                $q->where('sku', 'like', "%{$keyword}%")
                  ->orWhere('name', 'like', "%{$keyword}%")
                  ->orWhere('barcode', 'like', "%{$keyword}%");
            });
        }

        // ロケーション検索（コード）
        if ($request->filled('location')) {
            $loc = $request->location;
            $query->whereHas('location', function ($q) use ($loc) {
                $q->where('code', 'like', "%{$loc}%")
                  ->orWhere('name', 'like', "%{$loc}%");
            });
        }

        // 並び（商品名 → ロケーションコード）
        $stocks = $query
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->join('locations', 'locations.id', '=', 'stocks.location_id')
            ->orderBy('products.name')
            ->orderBy('locations.code')
            ->select('stocks.*')
            ->paginate(30)
            ->withQueryString();

        return view('wms.stocks.index', compact('stocks'));
    }
}
