<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wms\StoreLocationRequest;
use App\Http\Requests\Wms\UpdateLocationRequest;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * ロケーション一覧
     */
    public function index(Request $request)
    {
        $query = Location::query();

        // 検索（コード / 名称）
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                  ->orWhere('name', 'like', "%{$keyword}%");
            });
        }

        $locations = $query
            ->orderBy('code')
            ->paginate(20)
            ->withQueryString();

        return view('wms.locations.index', compact('locations'));
    }

    /**
     * 新規作成画面
     */
    public function create()
    {
        return view('wms.locations.create');
    }

    /**
     * 登録処理
     */
    public function store(StoreLocationRequest $request)
    {
        Location::create($request->validated());

        return redirect()
            ->route('locations.index')
            ->with('success', 'ロケーションを登録しました');
    }

    /**
     * 詳細
     */
    public function show(Location $location)
    {
        return view('wms.locations.show', compact('location'));
    }

    /**
     * 編集画面
     */
    public function edit(Location $location)
    {
        return view('wms.locations.edit', compact('location'));
    }

    /**
     * 更新処理
     */
    public function update(UpdateLocationRequest $request, Location $location)
    {
        $location->update($request->validated());

        return redirect()
            ->route('locations.show', $location)
            ->with('success', 'ロケーションを更新しました');
    }

    /**
     * 削除（論理削除）
     */
    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()
            ->route('locations.index')
            ->with('success', 'ロケーションを削除しました');
    }
}
