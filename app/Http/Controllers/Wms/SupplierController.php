<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wms\StoreSupplierRequest;
use App\Http\Requests\Wms\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        // 検索（仕入先名 / コード / メール）
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('code', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        $suppliers = $query
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('wms.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('wms.suppliers.create');
    }

    public function store(StoreSupplierRequest $request)
    {
        Supplier::create($request->validated());

        return redirect()
            ->route('suppliers.index')
            ->with('success', '仕入先を登録しました');
    }

    public function show(Supplier $supplier)
    {
        return view('wms.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('wms.suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('success', '仕入先を更新しました');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', '仕入先を削除しました');
    }
}
