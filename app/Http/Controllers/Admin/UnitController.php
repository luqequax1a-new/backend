<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Unit::query();
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        $units = $query->withCount('products')->orderBy('name')->get(['id', 'name', 'text', 'step', 'is_active']);
        
        return response()->json(UnitResource::collection($units));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UnitRequest $request)
    {
        $unit = Unit::create($request->validated());
        
        return response()->json(new UnitResource($unit), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $unit = Unit::findOrFail($id);
        
        return response()->json(new UnitResource($unit));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UnitRequest $request, string $id)
    {
        $unit = Unit::findOrFail($id);
        $data = $request->only(['name', 'text', 'step', 'is_active']);
        $unit->fill($data)->save();
        
        return response()->json(new UnitResource($unit));
    }

    /**
     * Update the status of the specified resource.
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);
        
        $unit = Unit::findOrFail($id);
        $unit->is_active = $request->is_active;
        $unit->save();
        
        return response()->json(new UnitResource($unit));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unit = Unit::findOrFail($id);
        $inUse = $unit->products()->count();
        
        if ($inUse > 0) {
            return response()->json([
                'code' => 'UNIT_IN_USE',
                'message' => 'Bu birim ürünlerde kullanılıyor. Silmeden önce ürünlerden kaldırın.'
            ], 409);
        }
        
        $unit->delete();
        return response()->json(['message' => 'Birim silindi.']);
    }
    
    /**
     * Replace unit in all products and delete the old unit.
     */
    public function replace(Request $request, string $id)
    {
        $unit = Unit::findOrFail($id);
        
        $data = $request->validate([
            'new_unit_id' => ['required', 'different:' . $id, 'exists:units,id'],
        ]);
        
        \DB::transaction(function () use ($unit, $data) {
            \App\Models\Product::where('unit_id', $unit->id)
                ->update(['unit_id' => $data['new_unit_id']]);
            
            $unit->delete();
        });
        
        return response()->json(['message' => 'Ürünler yeni birime taşındı ve eski birim silindi.']);
    }
}
