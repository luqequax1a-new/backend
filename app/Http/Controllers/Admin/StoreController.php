<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return response()->json($stores);
    }
    
    public function show()
    {
        return response()->json([
            'name' => 'Kayalar Manifatura',
            'logo' => null,
            'currency' => 'TRY',
            'timezone' => 'Europe/Istanbul',
        ]);
    }
}
