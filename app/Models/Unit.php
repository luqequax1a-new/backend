<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name', 'text', 'step', 'is_active'];
    
    protected $casts = [
        'step' => 'decimal:3',
        'is_active' => 'boolean'
    ];
    
    public function products()
    {
        return $this->hasMany(\App\Models\Product::class);
    }
}
