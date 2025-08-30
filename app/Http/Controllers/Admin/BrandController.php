<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return response()->json($brands);
    }
}
