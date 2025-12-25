<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * 商品一覧
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // 検索（SKU / 商品名）
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', "%{$keyword}%")
                  ->orWhere('name', 'like', "%{$keyword}%");
            });
        }

        $products = $query
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('wms.products.index', compact('products'));
    }

    /**
     * 商品新規作成画面
     */
    public function create()
    {
        return view('wms.products.create');
    }

    /**
     * 商品登録処理
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku',
            'barcode' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'note' => 'nullable|string',
        ]);

        Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with('success', '商品を登録しました');
    }

    /**
     * 商品詳細
     */
    public function show(Product $product)
    {
        return view('wms.products.show', compact('product'));
    }

    /**
     * 商品編集画面
     */
    public function edit(Product $product)
    {
        return view('wms.products.edit', compact('product'));
    }

    /**
     * 商品更新処理
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'note' => 'nullable|string',
        ]);

        $product->update($validated);

        return redirect()
            ->route('products.show', $product)
            ->with('success', '商品を更新しました');
    }

    /**
     * 商品削除（論理削除）
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', '商品を削除しました');
    }
}
